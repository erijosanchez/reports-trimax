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
        'analista.cobranzas@trimaxperu.com'
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

        [$semanaNumero, $anio, , , $limiteCarbon] = ReporteCobranza::datosSemanActual();
        $fechaLimiteTs = $limiteCarbon->timestamp * 1000;

        // Reporte de la semana actual (solo para sede)
        $reporteSemanaActual = null;
        if ($user->isSede() && $user->sede) {
            $reporteSemanaActual = ReporteCobranza::obtenerOCrearSemanaActual($user->id, $user->sede);
        }

        // Resumen semana actual por sede (para admin/superadmin)
        $resumenSedes = null;
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Todas las sedes que tienen usuarios activos con rol sede
            $usuariosSede = User::role('sede')->where('is_active', true)->whereNotNull('sede')->get();

            // Reportes ya registrados esta semana
            $reportesSemana = ReporteCobranza::where('semana_numero', $semanaNumero)
                ->where('anio', $anio)
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
            ->orderByDesc('anio')
            ->orderByDesc('semana_numero');

        if ($user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            $historialQuery->where('sede', $user->sede);
        }

        $historial = $historialQuery->limit(60)->get();

        // Datos KPI para el gráfico (últimas 8 semanas, todas las sedes)
        $kpiData = $this->getKpiChartData(8);

        return view('productividad.cobranza-sedes.cobranza', compact(
            'reporteSemanaActual',
            'resumenSedes',
            'historial',
            'kpiData',
            'semanaNumero',
            'anio',
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

        // Solo se permite un envío inicial (edición va por update)
        if ($reporte->fecha_envio_original) {
            return response()->json(['error' => 'Ya enviaste tu reporte esta semana. Usa la opción de editar.'], 422);
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

        $semanas = (int) $request->get('semanas', 8);
        $semanas = max(1, min($semanas, 26));

        return response()->json($this->getKpiChartData($semanas));
    }

    // ── Historial AJAX ────────────────────────────────────────────

    public function historial(Request $request)
    {
        $user  = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $query = ReporteCobranza::with('user')
            ->orderByDesc('anio')
            ->orderByDesc('semana_numero');

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
                'semana'          => "S{$r->semana_numero}/{$r->anio}",
                'semana_inicio'   => $r->semana_inicio?->format('d/m/Y'),
                'semana_fin'      => $r->semana_fin?->format('d/m/Y'),
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
            'semana'        => "S{$reporte->semana_numero}/{$reporte->anio}",
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
        $dir = "cobranza/{$reporte->anio}/semana-{$reporte->semana_numero}/{$reporte->sede}";

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

    private function getKpiChartData(int $semanas): array
    {
        $now = Carbon::now('America/Lima');

        // Generar últimas N semanas
        $semanasData = [];
        for ($i = $semanas - 1; $i >= 0; $i--) {
            $fecha = $now->copy()->subWeeks($i);
            $semanasData[] = [
                'semana' => (int) $fecha->isoWeek(),
                'anio'   => (int) $fecha->isoWeekYear(),
                'label'  => 'S' . $fecha->isoWeek() . '/' . $fecha->isoWeekYear(),
            ];
        }

        $semanaNumeros = array_column($semanasData, 'semana');
        $anios         = array_unique(array_column($semanasData, 'anio'));

        // Obtener reportes de esas semanas
        $reportes = ReporteCobranza::whereIn('semana_numero', $semanaNumeros)
            ->whereIn('anio', $anios)
            ->get()
            ->groupBy('sede');

        // Colores para las sedes
        $colores = [
            '#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#06b6d4', '#ec4899', '#84cc16', '#f97316', '#6366f1',
        ];

        $sedes    = $reportes->keys()->sort()->values();
        $datasets = [];

        foreach ($sedes as $idx => $sede) {
            $kpis = [];
            foreach ($semanasData as $s) {
                $rep = $reportes[$sede]->first(fn($r) => $r->semana_numero === $s['semana'] && $r->anio === $s['anio']);
                $kpis[] = $rep ? (float) $rep->kpi_porcentaje : null;
            }

            $color = $colores[$idx % count($colores)];
            $datasets[] = [
                'label'           => $sede,
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
            'labels'   => array_column($semanasData, 'label'),
            'datasets' => $datasets,
        ];
    }
}
