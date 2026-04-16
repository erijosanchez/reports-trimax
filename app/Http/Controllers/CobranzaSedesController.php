<?php

namespace App\Http\Controllers;

use App\Models\ReporteCobranza;
use App\Models\User;
use App\Notifications\CobranzaSubmitida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CobranzaSedesController extends Controller
{
    // Destinatarios fijos al enviar/editar reporte
    const EMAILS_DESTINO = [
        'arosadio@trimaxperu.com',
        'supervisor.comercial.1@trimaxperu.com',
        'planeamiento.comercial@trimaxperu.com',
        'analista.cobranzas@trimaxperu.com',
        'emisionnc@trimaxperu.com',
        'cobranzas@trimaxperu.com'
    ];

    // Tipos de archivo permitidos
    const MIMES_PERMITIDOS = 'jpg,jpeg,png,gif,webp,xlsx,xls,csv,pdf';
    const MAX_SIZE_KB       = 20480; // 20 MB por archivo

    // ── Vistas ────────────────────────────────────────────────────

    public function index()
    {
        $user = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            abort(403, 'No tienes permiso para acceder al módulo de Cobranza.');
        }

        $hoy = Carbon::now('America/Lima');
        [, , , , $limiteCarbon] = ReporteCobranza::datosSemanActual();
        $fechaLimiteTs = $limiteCarbon->timestamp * 1000;
        $fechaDiaLabel = $hoy->format('d/m/Y');

        // Reporte del día actual (solo para sede)
        $reporteSemanaActual = null;
        if ($user->isSede() && $user->sede) {
            $reporteSemanaActual = ReporteCobranza::obtenerOCrearSemanaActual($user->id, $user->sede);
        }

        // Resumen del día por sede (para admin/superadmin y usuarios con permiso productividad sedes)
        $resumenSedes = null;
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->puede_ver_productividad_sedes) {
            $usuariosSede = User::role('sede')->where('is_active', true)->whereNotNull('sede')->get();

            // Reportes ya registrados hoy
            $reportesSemana = ReporteCobranza::where('semana_inicio', $hoy->toDateString())
                ->get()
                ->keyBy('sede');

            $resumenSedes = $usuariosSede->map(function ($u) use ($reportesSemana) {
                $reporte = $reportesSemana->get($u->sede);
                return [
                    'sede'         => $u->sede,
                    'usuario'      => $u->name,
                    'enviado'      => $reporte && $reporte->fecha_envio_original,
                    'fecha_envio'  => $reporte?->fecha_envio_original?->setTimezone('America/Lima')->format('H:i'),
                    'kpi'          => $reporte?->kpi_porcentaje,
                    'kpi_label'    => $reporte?->kpiLabel() ?? '—',
                    'kpi_color'    => $reporte?->kpiColor() ?? 'secondary',
                    'editado_tarde'=> $reporte?->editado_tarde ?? false,
                    'reporte_id'   => $reporte?->id,
                ];
            })->unique('sede')->sortBy('sede')->values();
        }

        // Historial (últimas 60 entradas)
        $historialQuery = ReporteCobranza::with('user')
            ->orderByDesc('semana_inicio')
            ->orderByDesc('created_at');

        if ($user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            $historialQuery->where('sede', $user->sede);
        }

        $historial = $historialQuery->limit(60)->get();

        // Datos KPI para el gráfico (últimos 2 meses; sede filtra su propia sede)
        $sedeFiltro = ($user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) ? $user->sede : null;
        $kpiData = $this->getKpiChartData(2, $sedeFiltro);

        return view('productividad.cobranza-sedes.cobranza', compact(
            'reporteSemanaActual',
            'resumenSedes',
            'historial',
            'kpiData',
            'fechaDiaLabel',
            'fechaLimiteTs'
        ));
    }

    // ── Store ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            return response()->json(['error' => 'Sin permiso para enviar reportes.'], 403);
        }

        $request->validate([
            'archivos'   => 'required|array|min:1',
            'archivos.*' => 'file|max:' . self::MAX_SIZE_KB . '|mimes:' . self::MIMES_PERMITIDOS,
            'notas'      => 'nullable|string|max:2000',
        ], [
            'archivos.required' => 'Debes adjuntar al menos un archivo.',
            'archivos.*.mimes'  => 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP) y archivos Excel/CSV/PDF.',
            'archivos.*.max'    => 'Cada archivo no puede superar 20 MB.',
        ]);

        $sede = $user->sede ?? 'Sin asignar';
        $reporte = ReporteCobranza::obtenerOCrearSemanaActual($user->id, $sede);

        // Solo se permite un envío inicial por día (edición va por update)
        if ($reporte->fecha_envio_original) {
            return response()->json(['error' => 'Ya enviaste tu reporte hoy. Usa la opción de editar.'], 422);
        }

        $archivosGuardados = $this->guardarArchivos($request->file('archivos'), $reporte);

        $ahora = Carbon::now('America/Lima');
        $reporte->fill([
            'user_id'              => $user->id,
            'fecha_envio_original' => $ahora,
            'fecha_ultimo_envio'   => $ahora,
            'archivos'             => $archivosGuardados,
            'notas'                => $request->notas,
        ]);
        $reporte->save();
        $reporte->recalcularKpi();

        $this->enviarNotificaciones($reporte, false);

        return response()->json([
            'success' => true,
            'message' => 'Reporte enviado correctamente.',
            'kpi'     => $reporte->kpi_porcentaje,
            'estado'  => $reporte->estado,
            'reload'  => true,
        ]);
    }

    // ── Update (edición) ──────────────────────────────────────────

    public function update(Request $request, ReporteCobranza $reporte)
    {
        $user = auth()->user();

        // Solo el usuario que creó el reporte, o admin/superadmin
        if (!$user->isSuperAdmin() && !$user->isAdmin() && $reporte->user_id !== $user->id) {
            return response()->json(['error' => 'Sin permiso para editar este reporte.'], 403);
        }

        $request->validate([
            'archivos'         => 'nullable|array',
            'archivos.*'       => 'file|max:' . self::MAX_SIZE_KB . '|mimes:' . self::MIMES_PERMITIDOS,
            'notas'            => 'nullable|string|max:2000',
            'eliminar_indices' => 'nullable|array',
            'eliminar_indices.*'=> 'integer|min:0',
        ]);

        $ahora     = Carbon::now('America/Lima');
        $tardio    = $ahora->greaterThan($reporte->fecha_limite);

        // Manejar eliminación de archivos existentes
        $archivosActuales = $reporte->archivos ?? [];
        if ($request->filled('eliminar_indices')) {
            foreach ($request->eliminar_indices as $idx) {
                if (isset($archivosActuales[$idx])) {
                    Storage::disk('local')->delete($archivosActuales[$idx]['path']);
                    unset($archivosActuales[$idx]);
                }
            }
            $archivosActuales = array_values($archivosActuales);
        }

        // Agregar nuevos archivos
        if ($request->hasFile('archivos')) {
            $nuevos = $this->guardarArchivos($request->file('archivos'), $reporte);
            $archivosActuales = array_merge($archivosActuales, $nuevos);
        }

        $reporte->fill([
            'fecha_ultimo_envio' => $ahora,
            'archivos'           => $archivosActuales,
            'notas'              => $request->notas ?? $reporte->notas,
            'editado_tarde'      => $reporte->editado_tarde || $tardio,
        ]);
        $reporte->save();
        $reporte->recalcularKpi();

        $this->enviarNotificaciones($reporte, true);

        return response()->json([
            'success' => true,
            'message' => $tardio
                ? 'Reporte editado con atraso. El KPI fue recalculado.'
                : 'Reporte actualizado correctamente.',
            'kpi'     => $reporte->kpi_porcentaje,
            'estado'  => $reporte->estado,
            'tardio'  => $tardio,
            'reload'  => true,
        ]);
    }

    // ── Download ──────────────────────────────────────────────────

    public function download(ReporteCobranza $reporte, int $index)
    {
        $user = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            abort(403);
        }

        $archivos = $reporte->archivos ?? [];

        if (!isset($archivos[$index])) {
            abort(404, 'Archivo no encontrado.');
        }

        $archivo = $archivos[$index];

        if (!Storage::disk('local')->exists($archivo['path'])) {
            abort(404, 'El archivo ya no está disponible.');
        }

        return Storage::disk('local')->download($archivo['path'], $archivo['name']);
    }

    // ── Preview inline ────────────────────────────────────────────

    public function preview(ReporteCobranza $reporte, int $index)
    {
        $user = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            abort(403);
        }

        $archivos = $reporte->archivos ?? [];

        if (!isset($archivos[$index])) {
            abort(404, 'Archivo no encontrado.');
        }

        $archivo = $archivos[$index];

        if (!Storage::disk('local')->exists($archivo['path'])) {
            abort(404, 'El archivo ya no está disponible.');
        }

        return response()->file(
            Storage::disk('local')->path($archivo['path']),
            [
                'Content-Type'        => $archivo['mime'] ?? 'application/octet-stream',
                'Content-Disposition' => 'inline; filename="' . $archivo['name'] . '"',
            ]
        );
    }

    // ── KPI API ───────────────────────────────────────────────────

    public function kpiData(Request $request)
    {
        $user = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $sedeFiltro = ($user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) ? $user->sede : null;
        $tipo       = $request->get('tipo', 'diario');

        if ($tipo === 'mensual') {
            $meses = max(1, min((int) $request->get('meses', 2), 12));
            return response()->json($this->getKpiChartData($meses, $sedeFiltro));
        }

        // tipo = diario: datos día a día del mes solicitado
        $hoy  = Carbon::now('America/Lima');
        $mes  = max(1, min((int) $request->get('mes',  $hoy->month), 12));
        $anio = max(2020, min((int) $request->get('anio', $hoy->year), $hoy->year));
        return response()->json($this->getKpiDiarioData($mes, $anio, $sedeFiltro));
    }

    // ── Historial AJAX ────────────────────────────────────────────

    public function historial(Request $request)
    {
        $user  = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $query = ReporteCobranza::with('user')
            ->orderByDesc('semana_inicio')
            ->orderByDesc('created_at');

        if ($user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            $query->where('sede', $user->sede);
        }

        if ($request->filled('sede')) {
            $query->where('sede', $request->sede);
        }

        $registros = $query->limit(100)->get()->map(function ($r) {
            return [
                'id'              => $r->id,
                'sede'            => $r->sede,
                'semana'          => $r->semana_inicio?->format('d/m/Y'),
                'semana_inicio'   => $r->semana_inicio?->format('d/m/Y'),
                'semana_fin'      => null,
                'fecha_limite'    => $r->fecha_limite?->setTimezone('America/Lima')->format('d/m/Y H:i'),
                'fecha_envio'     => $r->fecha_envio_original?->setTimezone('America/Lima')->format('d/m/Y H:i'),
                'fecha_edicion'   => $r->editado_tarde ? $r->fecha_ultimo_envio?->setTimezone('America/Lima')->format('d/m/Y H:i') : null,
                'kpi'             => $r->kpi_porcentaje,
                'kpi_label'       => $r->kpiLabel(),
                'kpi_color'       => $r->kpiColor(),
                'estado'          => $r->estado,
                'editado_tarde'   => $r->editado_tarde,
                'num_archivos'    => count($r->archivos ?? []),
                'notas'           => $r->notas,
                'usuario'         => $r->user?->name,
            ];
        });

        return response()->json(['data' => $registros]);
    }

    // ── Detalle de un reporte ─────────────────────────────────────

    public function show(ReporteCobranza $reporte)
    {
        $user = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            abort(403);
        }

        $archivos = collect($reporte->archivos ?? [])->map(function ($a, $i) use ($reporte) {
            $esImagen = str_starts_with($a['mime'] ?? '', 'image/');
            return array_merge($a, [
                'index'        => $i,
                'es_imagen'    => $esImagen,
                'download_url' => route('productividad.cobranza-sedes.cobranza.download', [$reporte->id, $i]),
                'preview_url'  => route('productividad.cobranza-sedes.cobranza.preview',  [$reporte->id, $i]),
            ]);
        });

        return response()->json([
            'id'            => $reporte->id,
            'sede'          => $reporte->sede,
            'semana'        => $reporte->semana_inicio?->format('d/m/Y'),
            'fecha_limite'  => $reporte->fecha_limite?->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'fecha_envio'   => $reporte->fecha_envio_original?->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'fecha_edicion' => $reporte->editado_tarde ? $reporte->fecha_ultimo_envio?->setTimezone('America/Lima')->format('d/m/Y H:i') : null,
            'kpi'           => $reporte->kpi_porcentaje,
            'kpi_label'     => $reporte->kpiLabel(),
            'kpi_color'     => $reporte->kpiColor(),
            'estado'        => $reporte->estado,
            'editado_tarde' => $reporte->editado_tarde,
            'notas'         => $reporte->notas,
            'archivos'      => $archivos,
            'puede_editar'  => $this->puedeEditar($reporte),
        ]);
    }

    // ── Privados ──────────────────────────────────────────────────

    private function guardarArchivos(array $archivos, ReporteCobranza $reporte): array
    {
        $guardados = [];
        $dir = "cobranza/{$reporte->anio}/dia-{$reporte->semana_inicio->format('Y-m-d')}/{$reporte->sede}";

        foreach ($archivos as $file) {
            $extension = $file->getClientOriginalExtension();
            $nombreOriginal = $file->getClientOriginalName();
            $nombreGuardado = Str::uuid() . '.' . $extension;
            $path = $file->storeAs($dir, $nombreGuardado, 'local');

            $guardados[] = [
                'name' => $nombreOriginal,
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $guardados;
    }

    private function enviarNotificaciones(ReporteCobranza $reporte, bool $esEdicion): void
    {
        // Destinatarios fijos
        $destinatariosEmail = collect(self::EMAILS_DESTINO)->map(function ($email) {
            return (object) ['email' => $email, 'routeNotificationForMail' => fn() => $email];
        });

        // Notificar usando Notification::route para emails externos
        foreach (self::EMAILS_DESTINO as $email) {
            Notification::route('mail', $email)
                ->notify(new \App\Notifications\CobranzaSubmitida($reporte, $esEdicion));
        }
    }

    private function puedeEditar(ReporteCobranza $reporte): bool
    {
        $user = auth()->user();
        return $user->isSuperAdmin() || $user->isAdmin() || $reporte->user_id === $user->id;
    }

    private function getKpiDiarioData(int $mes, int $anio, ?string $sede = null): array
    {
        $inicio = Carbon::create($anio, $mes, 1, 0, 0, 0, 'America/Lima');
        $fin    = $inicio->copy()->endOfMonth();
        $hoy    = Carbon::now('America/Lima');

        if ($fin->greaterThan($hoy)) {
            $fin = $hoy->copy();
        }

        // Generar días del mes hasta hoy
        $diasData = [];
        $cursor   = $inicio->copy();
        while ($cursor->lte($fin)) {
            $diasData[] = [
                'fecha' => $cursor->toDateString(),
                'label' => $cursor->format('d'),
            ];
            $cursor->addDay();
        }

        if (empty($diasData)) {
            return ['labels' => [], 'datasets' => []];
        }

        $fechas = array_column($diasData, 'fecha');
        $query  = ReporteCobranza::whereIn('semana_inicio', $fechas);
        if ($sede) {
            $query->where('sede', $sede);
        }
        $reportes = $query->get()->groupBy('sede');

        $colores = [
            '#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1',
        ];

        $datasets = [];
        foreach ($reportes->keys()->sort()->values() as $idx => $sedeNombre) {
            $kpis = [];
            foreach ($diasData as $d) {
                $rep    = $reportes[$sedeNombre]->first(
                    fn($r) => optional($r->semana_inicio)->toDateString() === $d['fecha']
                );
                $kpis[] = ($rep && !is_null($rep->kpi_porcentaje))
                    ? (float) $rep->kpi_porcentaje
                    : null;
            }
            $color      = $colores[$idx % count($colores)];
            $datasets[] = [
                'label'           => $sedeNombre,
                'data'            => $kpis,
                'backgroundColor' => $color . '33',
                'borderColor'     => $color,
                'borderWidth'     => 2,
                'spanGaps'        => true,
                'tension'         => 0.3,
                'pointRadius'     => 4,
            ];
        }

        return [
            'labels'   => array_column($diasData, 'label'),
            'datasets' => $datasets,
        ];
    }

    private function getKpiChartData(int $meses, ?string $sede = null): array
    {
        $now     = Carbon::now('America/Lima');
        $nombMes = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        // Generar últimos N meses
        $mesesData = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $fecha = $now->copy()->subMonths($i);
            $mesesData[] = [
                'anio'  => (int) $fecha->year,
                'mes'   => (int) $fecha->month,
                'label' => $nombMes[$fecha->month] . ' ' . $fecha->year,
            ];
        }

        // Obtener reportes de esos meses
        $query = ReporteCobranza::whereNotNull('semana_inicio')
            ->where(function ($q) use ($mesesData) {
                foreach ($mesesData as $m) {
                    $q->orWhere(function ($q2) use ($m) {
                        $q2->whereYear('semana_inicio', $m['anio'])
                           ->whereMonth('semana_inicio', $m['mes']);
                    });
                }
            });

        if ($sede) {
            $query->where('sede', $sede);
        }

        $reportes = $query->get()->groupBy('sede');

        $colores = [
            '#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1',
        ];

        $sedes    = $reportes->keys()->sort()->values();
        $datasets = [];

        foreach ($sedes as $idx => $nombreSede) {
            $kpis = [];
            foreach ($mesesData as $m) {
                $reportesMes = $reportes[$nombreSede]->filter(
                    fn($r) => optional($r->semana_inicio)->year  === $m['anio']
                           && optional($r->semana_inicio)->month === $m['mes']
                           && !is_null($r->kpi_porcentaje)
                );
                $kpis[] = $reportesMes->count()
                    ? round((float) $reportesMes->avg('kpi_porcentaje'), 1)
                    : null;
            }

            $color = $colores[$idx % count($colores)];
            $datasets[] = [
                'label'           => $nombreSede,
                'data'            => $kpis,
                'backgroundColor' => $color . '33',
                'borderColor'     => $color,
                'borderWidth'     => 2,
                'spanGaps'        => true,
                'tension'         => 0.3,
                'pointRadius'     => 4,
            ];
        }

        return [
            'labels'   => array_column($mesesData, 'label'),
            'datasets' => $datasets,
        ];
    }
}
