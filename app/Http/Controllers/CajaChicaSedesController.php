<?php

namespace App\Http\Controllers;

use App\Models\ReporteCajaChica;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Notifications\CajaChicaSubmitida;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CajaChicaSedesController extends Controller
{
    const EMAILS_DESTINO = [
        'arosadio@trimaxperu.com',
        'supervisor.comercial.1@trimaxperu.com',
        'planeamiento.comercial@trimaxperu.com',
    ];

    const MIMES_PERMITIDOS = 'jpg,jpeg,png,gif,webp,xlsx,xls,csv,pdf';
    const MAX_SIZE_KB       = 20480;

    // ── index ─────────────────────────────────────────────────────

    public function index()
    {
        $user = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            abort(403);
        }

        [$semanaNumero, $anio, , , $limiteCarbon] = ReporteCajaChica::datosSemanActual();
        $fechaLimiteTs = $limiteCarbon->timestamp * 1000;

        $reporteSemanaActual = null;
        if ($user->isSede() && $user->sede) {
            $reporteSemanaActual = ReporteCajaChica::obtenerOCrearSemanaActual($user->id, $user->sede);
        }

        $resumenSedes = null;
        if ($user->isSuperAdmin() || $user->isAdmin() || $user->puede_ver_productividad_sedes) {
            $usuariosSede   = User::role('sede')->where('is_active', true)->whereNotNull('sede')->get();
            $reportesSemana = ReporteCajaChica::where('semana_numero', $semanaNumero)
                ->where('anio', $anio)->get()->keyBy('sede');

            $resumenSedes = $usuariosSede->map(function ($u) use ($reportesSemana) {
                $r = $reportesSemana->get($u->sede);
                return [
                    'sede'          => $u->sede,
                    'usuario'       => $u->name,
                    'enviado'       => $r && $r->fecha_envio_original,
                    'fecha_envio'   => $r?->fecha_envio_original?->setTimezone('America/Lima')->format('H:i'),
                    'kpi'           => $r?->kpi_porcentaje,
                    'kpi_label'     => $r?->kpiLabel() ?? '—',
                    'kpi_color'     => $r?->kpiColor() ?? 'secondary',
                    'editado_tarde' => $r?->editado_tarde ?? false,
                    'reporte_id'    => $r?->id,
                ];
            })->unique('sede')->sortBy('sede')->values();
        }

        $historialQuery = ReporteCajaChica::with('user')
            ->orderByDesc('anio')
            ->orderByDesc('semana_numero');

        if ($user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            $historialQuery->where('sede', $user->sede);
        }

        $historial = $historialQuery->limit(60)->get();
        $kpiData   = $this->getKpiChartData(8);

        return view('productividad.cobranza-sedes.caja-chica', compact(
            'reporteSemanaActual', 'resumenSedes', 'historial', 'kpiData', 'semanaNumero', 'anio', 'fechaLimiteTs'
        ));
    }

    // ── store ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $request->validate([
            'archivos'   => 'required|array|min:1',
            'archivos.*' => 'file|max:' . self::MAX_SIZE_KB . '|mimes:' . self::MIMES_PERMITIDOS,
            'notas'      => 'nullable|string|max:2000',
        ], [
            'archivos.required' => 'Debes adjuntar al menos un archivo.',
            'archivos.*.mimes'  => 'Solo se permiten Excel, CSV, PDF e imágenes.',
            'archivos.*.max'    => 'Cada archivo no puede superar 20 MB.',
        ]);

        $sede   = $user->sede ?? 'Sin asignar';
        $reporte = ReporteCajaChica::obtenerOCrearSemanaActual($user->id, $sede);

        if ($reporte->fecha_envio_original) {
            return response()->json(['error' => 'Ya enviaste tu reporte esta semana. Usa editar.'], 422);
        }

        $ahora = Carbon::now('America/Lima');
        $reporte->fill([
            'user_id'              => $user->id,
            'fecha_envio_original' => $ahora,
            'fecha_ultimo_envio'   => $ahora,
            'archivos'             => $this->guardarArchivos($request->file('archivos'), $reporte),
            'notas'                => $request->notas,
        ]);
        $reporte->save();
        $reporte->recalcularKpi();

        $this->enviarNotificaciones($reporte, false);

        ActivityLogService::log(auth()->id(), 'submit_reporte_caja_chica', 'ReporteCajaChica', $reporte->id, "Envió reporte de caja chica (sede: {$sede})");

        return response()->json([
            'success' => true,
            'message' => 'Reporte de Caja Chica enviado correctamente.',
            'kpi'     => $reporte->kpi_porcentaje,
            'reload'  => true,
        ]);
    }

    // ── update ────────────────────────────────────────────────────

    public function update(Request $request, ReporteCajaChica $reporte)
    {
        $user = auth()->user()->fresh();

        $esPropietario = (int) $reporte->user_id === (int) $user->id;
        $esMismaSede   = $user->isSede() && $reporte->sede === $user->sede;

        if (!$user->isSuperAdmin() && !$user->isAdmin() && !$esPropietario && !$esMismaSede) {
            return response()->json(['error' => 'Sin permiso para editar este reporte.'], 403);
        }

        $request->validate([
            'archivos'            => 'nullable|array',
            'archivos.*'          => 'file|max:' . self::MAX_SIZE_KB . '|mimes:' . self::MIMES_PERMITIDOS,
            'notas'               => 'nullable|string|max:2000',
            'eliminar_indices'    => 'nullable|array',
            'eliminar_indices.*'  => 'integer|min:0',
        ]);

        $ahora   = Carbon::now('America/Lima');
        $tardio  = $ahora->greaterThan($reporte->fecha_limite);

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

        if ($request->hasFile('archivos')) {
            $archivosActuales = array_merge($archivosActuales, $this->guardarArchivos($request->file('archivos'), $reporte));
        }

        $reporte->fill([
            'fecha_envio_original' => $reporte->fecha_envio_original ?? $ahora,
            'fecha_ultimo_envio'   => $ahora,
            'archivos'             => $archivosActuales,
            'notas'                => $request->notas ?? $reporte->notas,
            'editado_tarde'        => $reporte->editado_tarde || $tardio,
        ]);

        // Si estaba rechazado y se corrige/reenvía, vuelve a pendiente de revisión
        // Rechazado: siempre vuelve a pendiente. Conforme observado: solo si la
        // reedición es dentro del plazo; si es tardía, se conserva la penalización.
        $resetRevision = $reporte->revision_estado === 'rechazado'
            || ($reporte->revision_estado === 'conforme_observado' && !$tardio);

        if ($resetRevision) {
            $reporte->revision_estado        = null;
            $reporte->revision_motivo        = null;
            $reporte->revision_kpi_penalidad = null;
            $reporte->revision_archivos      = null;
            $reporte->revision_user_id       = null;
            $reporte->revision_at            = null;
        }

        $reporte->save();
        $reporte->recalcularKpi();

        $this->enviarNotificaciones($reporte, true);

        ActivityLogService::log(auth()->id(), 'update_reporte_caja_chica', 'ReporteCajaChica', $reporte->id, 'Editó reporte de caja chica' . ($tardio ? ' (edición tardía)' : ''));

        return response()->json([
            'success' => true,
            'message' => $tardio ? 'Editado con atraso — KPI recalculado.' : 'Reporte actualizado.',
            'kpi'     => $reporte->kpi_porcentaje,
            'tardio'  => $tardio,
            'reload'  => true,
        ]);
    }

    // ── Revisión (conforme / rechazado) ───────────────────────────

    public function revisar(Request $request, ReporteCajaChica $reporte)
    {
        $user = auth()->user();

        if (!$user->puedeRevisarReportesSedes()) {
            return response()->json(['error' => 'Sin permiso para revisar reportes.'], 403);
        }

        if (is_null($reporte->fecha_envio_original)) {
            return response()->json(['error' => 'No se puede revisar un reporte que aún no fue enviado.'], 422);
        }

        $data = $request->validate([
            'estado'     => 'required|in:conforme,conforme_observado,rechazado',
            'motivo'     => 'required_unless:estado,conforme|nullable|string|max:2000',
            'penalidad'  => 'required_if:estado,conforme_observado|nullable|in:20,50',
            'archivos'   => 'nullable|array',
            'archivos.*' => 'file|max:' . self::MAX_SIZE_KB . '|mimes:jpg,jpeg,png,webp,pdf,xlsx,xls,csv',
        ], [
            'motivo.required_unless' => 'Debes indicar el motivo/observación.',
            'penalidad.required_if'  => 'Debes elegir el descuento de KPI (20% o 50%).',
            'penalidad.in'           => 'El descuento debe ser 20% o 50%.',
            'archivos.*.mimes'       => 'Solo imágenes (JPG, PNG, WEBP), PDF o Excel/CSV.',
            'archivos.*.max'         => 'Cada archivo no puede superar 20 MB.',
        ]);

        $reporte->revision_estado        = $data['estado'];
        $reporte->revision_motivo        = $data['motivo'] ?? null;
        $reporte->revision_kpi_penalidad = $data['estado'] === 'conforme_observado' ? (float) $data['penalidad'] : null;
        $reporte->revision_user_id       = $user->id;
        $reporte->revision_at            = Carbon::now('America/Lima');

        if ($request->hasFile('archivos')) {
            $reporte->revision_archivos = $this->guardarArchivos($request->file('archivos'), $reporte);
        }

        $reporte->save();
        $reporte->recalcularKpi();

        if ($data['estado'] === 'rechazado' || $data['estado'] === 'conforme_observado') {
            $this->notificarRevision($reporte, $data['estado']);
        }

        ActivityLogService::log(
            $user->id, 'revisar_reporte_caja_chica', 'ReporteCajaChica', $reporte->id,
            "Marcó reporte de caja chica como {$data['estado']} (sede: {$reporte->sede})"
        );

        $mensajes = [
            'conforme'           => 'Reporte marcado como conforme.',
            'conforme_observado' => 'Reporte conforme observado. KPI penalizado y notificado a la sede.',
            'rechazado'          => 'Reporte rechazado. Se notificó a la sede.',
        ];

        return response()->json([
            'success'         => true,
            'message'         => $mensajes[$data['estado']],
            'kpi'             => $reporte->kpi_porcentaje,
            'revision_estado' => $reporte->revision_estado,
            'reload'          => true,
        ]);
    }

    private function notificarRevision(ReporteCajaChica $reporte, string $estado): void
    {
        $owner = $reporte->user;
        if (!$owner || empty($owner->email)) {
            return;
        }
        try {
            Notification::route('mail', $owner->email)->notify(new \App\Notifications\ReporteSedeRechazado(
                'Caja Chica',
                $reporte->sede,
                "S{$reporte->semana_numero}/{$reporte->anio}",
                $reporte->revision_motivo ?? '',
                auth()->user()->name,
                route('productividad.cobranza-sedes.caja-chica.index'),
                $estado,
                $reporte->revision_kpi_penalidad !== null ? (float) $reporte->revision_kpi_penalidad : null
            ));
        } catch (\Exception $e) {
            Log::error("Notificación de revisión (caja chica) no enviada: " . $e->getMessage());
        }
    }

    // ── show ──────────────────────────────────────────────────────

    public function show(ReporteCajaChica $reporte)
    {
        if (!auth()->user()->puedeVerCobranzaSedes()) abort(403);

        $archivos = collect($reporte->archivos ?? [])->map(function ($a, $i) use ($reporte) {
            return array_merge($a, [
                'index'        => $i,
                'es_imagen'    => str_starts_with($a['mime'] ?? '', 'image/'),
                'download_url' => route('productividad.cobranza-sedes.caja-chica.download', [$reporte->id, $i]),
                'preview_url'  => route('productividad.cobranza-sedes.caja-chica.preview',  [$reporte->id, $i]),
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
            'revision_estado'  => $reporte->revision_estado,
            'revision_motivo'  => $reporte->revision_motivo,
            'revision_kpi_penalidad' => $reporte->revision_kpi_penalidad !== null ? (float) $reporte->revision_kpi_penalidad : null,
            'revision_archivos'=> $this->mapRevisionArchivos($reporte),
            'revision_revisor' => $reporte->revisor?->name,
            'revision_at'      => $reporte->revision_at?->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'puede_revisar'    => auth()->user()->puedeRevisarReportesSedes(),
        ]);
    }

    /** Mapea los adjuntos de la revisión con URLs de preview/descarga. */
    private function mapRevisionArchivos(ReporteCajaChica $reporte): array
    {
        return collect($reporte->revision_archivos ?? [])->map(function ($a, $i) use ($reporte) {
            return [
                'name'         => $a['name'] ?? 'archivo',
                'es_imagen'    => str_starts_with($a['mime'] ?? '', 'image/'),
                'preview_url'  => route('productividad.cobranza-sedes.caja-chica.revision-file', [$reporte->id, $i]),
                'download_url' => route('productividad.cobranza-sedes.caja-chica.revision-file', [$reporte->id, $i]) . '?download=1',
            ];
        })->values()->all();
    }

    /** Sirve un adjunto de la revisión (inline por defecto, ?download=1 para bajar). */
    public function revisionFile(ReporteCajaChica $reporte, int $index, Request $request)
    {
        if (!auth()->user()->puedeVerCobranzaSedes()) abort(403);
        $archivo = ($reporte->revision_archivos ?? [])[$index] ?? null;
        if (!$archivo || !Storage::disk('local')->exists($archivo['path'])) abort(404);
        if ($request->boolean('download')) {
            return Storage::disk('local')->download($archivo['path'], $archivo['name']);
        }
        return response()->file(Storage::disk('local')->path($archivo['path']), [
            'Content-Type'        => $archivo['mime'] ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $archivo['name'] . '"',
        ]);
    }

    // ── download ──────────────────────────────────────────────────

    public function download(ReporteCajaChica $reporte, int $index)
    {
        if (!auth()->user()->puedeVerCobranzaSedes()) abort(403);
        $archivo = ($reporte->archivos ?? [])[$index] ?? null;
        if (!$archivo || !Storage::disk('local')->exists($archivo['path'])) abort(404);
        return Storage::disk('local')->download($archivo['path'], $archivo['name']);
    }

    // ── preview ───────────────────────────────────────────────────

    public function preview(ReporteCajaChica $reporte, int $index)
    {
        if (!auth()->user()->puedeVerCobranzaSedes()) abort(403);
        $archivo = ($reporte->archivos ?? [])[$index] ?? null;
        if (!$archivo || !Storage::disk('local')->exists($archivo['path'])) abort(404);
        return response()->file(Storage::disk('local')->path($archivo['path']), [
            'Content-Type'        => $archivo['mime'] ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $archivo['name'] . '"',
        ]);
    }

    // ── historial AJAX ────────────────────────────────────────────

    public function historial(Request $request)
    {
        $user = auth()->user();
        if (!$user->puedeVerCobranzaSedes()) return response()->json(['error' => 'Sin permiso.'], 403);

        $base = ReporteCajaChica::query();
        if ($user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            $base->where('sede', $user->sede);
        }

        // Opciones de filtro sobre el conjunto visible completo
        $sedesDisponibles = (clone $base)->select('sede')->distinct()->orderBy('sede')->pluck('sede');
        $semanasDisponibles = (clone $base)
            ->select('semana_inicio', 'semana_fin', 'semana_numero', 'anio')
            ->orderByDesc('anio')->orderByDesc('semana_numero')->get()
            ->unique(fn($r) => "{$r->semana_numero}/{$r->anio}")
            ->map(fn($r) => [
                'value' => $r->semana_inicio?->toDateString(),
                'label' => "S{$r->semana_numero}/{$r->anio}" . ($r->semana_inicio && $r->semana_fin
                    ? " — {$r->semana_inicio->format('d/m/Y')} al {$r->semana_fin->format('d/m/Y')}" : ''),
            ])->values();

        $query = (clone $base)->with('user');
        if ($request->filled('sede'))   $query->where('sede', $request->sede);
        if ($request->filled('semana')) $query->whereDate('semana_inicio', $request->semana);

        $dir = $request->get('sort', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('semana_inicio', $dir)->orderBy('created_at', $dir);

        $perPage = 25;
        $page    = max(1, (int) $request->get('page', 1));
        $total   = $query->count();
        $items   = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data' => $items->map(fn($r) => [
                'id'            => $r->id,
                'sede'          => $r->sede,
                'semana'        => "S{$r->semana_numero}/{$r->anio}",
                'semana_inicio' => $r->semana_inicio?->format('d/m/Y'),
                'semana_fin'    => $r->semana_fin?->format('d/m/Y'),
                'fecha_limite'  => $r->fecha_limite?->setTimezone('America/Lima')->format('d/m/Y H:i'),
                'fecha_envio'   => $r->fecha_envio_original?->setTimezone('America/Lima')->format('d/m/Y H:i'),
                'fecha_edicion' => $r->editado_tarde ? $r->fecha_ultimo_envio?->setTimezone('America/Lima')->format('d/m/Y H:i') : null,
                'kpi'           => $r->kpi_porcentaje,
                'kpi_label'     => $r->kpiLabel(),
                'kpi_color'     => $r->kpiColor(),
                'estado'        => $r->estado,
                'editado_tarde' => $r->editado_tarde,
                'num_archivos'  => count($r->archivos ?? []),
                'notas'                 => $r->notas,
                'usuario'               => $r->user?->name,
                'puede_enviar_atrasado' => is_null($r->fecha_envio_original) &&
                    ($user->isSuperAdmin() || $user->isAdmin() || $r->sede === $user->sede),
                'semana_inicio_iso'     => $r->semana_inicio?->toDateString(),
                'revision_estado'       => $r->revision_estado,
            ]),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
            'filtros'      => [
                'sedes'   => $sedesDisponibles,
                'semanas' => $semanasDisponibles,
            ],
        ]);
    }

    // ── kpi data ──────────────────────────────────────────────────

    public function kpiData(Request $request)
    {
        $user = auth()->user();
        if (!$user->puedeVerCobranzaSedes()) return response()->json(['error' => 'Sin permiso.'], 403);

        $sedeFiltro = ($user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) ? $user->sede : null;
        $tipo = $request->get('tipo', 'semanas');

        if ($tipo === 'mensual') {
            $meses = max(1, min((int) $request->get('meses', 3), 12));
            return response()->json($this->getKpiMensualData($meses, $sedeFiltro));
        }

        $semanas = max(1, min((int) $request->get('semanas', 8), 26));
        return response()->json($this->getKpiChartData($semanas, $sedeFiltro));
    }

    // ── privados ──────────────────────────────────────────────────

    private function guardarArchivos(array $archivos, ReporteCajaChica $reporte): array
    {
        $dir = "caja_chica/{$reporte->anio}/semana-{$reporte->semana_numero}/{$reporte->sede}";
        return collect($archivos)->map(fn($file) => [
            'name' => $file->getClientOriginalName(),
            'path' => $file->storeAs($dir, Str::uuid() . '.' . $file->getClientOriginalExtension(), 'local'),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ])->toArray();
    }

    private function enviarNotificaciones(ReporteCajaChica $reporte, bool $esEdicion): void
    {
        foreach (self::EMAILS_DESTINO as $email) {
            try {
                Notification::route('mail', $email)
                    ->notify(new CajaChicaSubmitida($reporte, $esEdicion));
            } catch (\Exception $e) {
                Log::error("Notificación caja chica no enviada a {$email}: " . $e->getMessage());
            }
        }
    }

    private function getKpiChartData(int $semanas, ?string $sede = null): array
    {
        $now         = Carbon::now('America/Lima');
        $semanasData = [];
        for ($i = $semanas - 1; $i >= 0; $i--) {
            $f = $now->copy()->subWeeks($i);
            $semanasData[] = ['semana' => (int)$f->isoWeek(), 'anio' => (int)$f->isoWeekYear(), 'label' => 'S'.$f->isoWeek().'/'.$f->isoWeekYear()];
        }

        $query = ReporteCajaChica::whereIn('semana_numero', array_column($semanasData, 'semana'))
            ->whereIn('anio', array_unique(array_column($semanasData, 'anio')));
        if ($sede) $query->where('sede', $sede);
        $reportes = $query->get();

        $enviados   = [];
        $noEnviados = [];
        foreach ($semanasData as $sw) {
            $del_periodo  = $reportes->filter(fn($r) => $r->semana_numero === $sw['semana'] && $r->anio === $sw['anio']);
            $enviados[]   = $del_periodo->whereNotNull('fecha_envio_original')->count();
            $noEnviados[] = $del_periodo->whereNull('fecha_envio_original')->count();
        }

        return [
            'labels'   => array_column($semanasData, 'label'),
            'datasets' => [
                ['label' => 'Enviados',    'data' => $enviados,   'backgroundColor' => 'rgba(16,185,129,0.85)', 'stack' => 'total'],
                ['label' => 'No enviados', 'data' => $noEnviados, 'backgroundColor' => 'rgba(239,68,68,0.85)',  'stack' => 'total'],
            ],
        ];
    }

    private function getKpiMensualData(int $meses, ?string $sede = null): array
    {
        $now     = Carbon::now('America/Lima');
        $nombMes = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

        $mesesData = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $f = $now->copy()->subMonths($i);
            $mesesData[] = ['anio' => (int)$f->year, 'mes' => (int)$f->month, 'label' => $nombMes[$f->month].' '.$f->year];
        }

        $query = ReporteCajaChica::whereNotNull('semana_inicio')
            ->where(function ($q) use ($mesesData) {
                foreach ($mesesData as $m) {
                    $q->orWhere(fn($q2) => $q2->whereYear('semana_inicio', $m['anio'])->whereMonth('semana_inicio', $m['mes']));
                }
            });
        if ($sede) $query->where('sede', $sede);
        $reportes = $query->get();

        $enviados   = [];
        $noEnviados = [];
        foreach ($mesesData as $m) {
            $del_mes      = $reportes->filter(fn($r) => optional($r->semana_inicio)->year === $m['anio'] && optional($r->semana_inicio)->month === $m['mes']);
            $enviados[]   = $del_mes->whereNotNull('fecha_envio_original')->count();
            $noEnviados[] = $del_mes->whereNull('fecha_envio_original')->count();
        }

        return [
            'labels'   => array_column($mesesData, 'label'),
            'datasets' => [
                ['label' => 'Enviados',    'data' => $enviados,   'backgroundColor' => 'rgba(16,185,129,0.85)', 'stack' => 'total'],
                ['label' => 'No enviados', 'data' => $noEnviados, 'backgroundColor' => 'rgba(239,68,68,0.85)',  'stack' => 'total'],
            ],
        ];
    }
}
