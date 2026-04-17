<?php

namespace App\Http\Controllers;

use App\Models\ReporteCajaChica;
use App\Models\User;
use App\Notifications\CajaChicaSubmitida;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdmin() && $reporte->user_id !== $user->id) {
            return response()->json(['error' => 'Sin permiso.'], 403);
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
        $reporte->save();
        $reporte->recalcularKpi();

        $this->enviarNotificaciones($reporte, true);

        return response()->json([
            'success' => true,
            'message' => $tardio ? 'Editado con atraso — KPI recalculado.' : 'Reporte actualizado.',
            'kpi'     => $reporte->kpi_porcentaje,
            'tardio'  => $tardio,
            'reload'  => true,
        ]);
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

        $query = ReporteCajaChica::with('user')->orderByDesc('anio')->orderByDesc('semana_numero');

        if ($user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            $query->where('sede', $user->sede);
        }

        return response()->json([
            'data' => $query->limit(100)->get()->map(fn($r) => [
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
            ]),
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
            Notification::route('mail', $email)
                ->notify(new CajaChicaSubmitida($reporte, $esEdicion));
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
        $reportes = $query->get()->groupBy('sede');

        $colores  = ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#84cc16','#f97316','#6366f1'];
        $datasets = [];

        foreach ($reportes->keys()->sort()->values() as $idx => $s) {
            $kpis = array_map(fn($sw) => ($r = $reportes[$s]->first(fn($r) => $r->semana_numero === $sw['semana'] && $r->anio === $sw['anio'])) ? (float)$r->kpi_porcentaje : null, $semanasData);
            $c = $colores[$idx % count($colores)];
            $datasets[] = ['label' => $s, 'data' => $kpis, 'backgroundColor' => $c.'33', 'borderColor' => $c, 'borderWidth' => 2, 'spanGaps' => true, 'tension' => 0.3, 'pointRadius' => 4];
        }

        return ['labels' => array_column($semanasData, 'label'), 'datasets' => $datasets];
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
        $reportes = $query->get()->groupBy('sede');

        $colores  = ['#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#84cc16','#f97316','#6366f1'];
        $datasets = [];

        foreach ($reportes->keys()->sort()->values() as $idx => $s) {
            $kpis = [];
            foreach ($mesesData as $m) {
                $rm = $reportes[$s]->filter(fn($r) => optional($r->semana_inicio)->year === $m['anio'] && optional($r->semana_inicio)->month === $m['mes'] && !is_null($r->kpi_porcentaje));
                $kpis[] = $rm->count() ? round((float)$rm->avg('kpi_porcentaje'), 1) : null;
            }
            $c = $colores[$idx % count($colores)];
            $datasets[] = ['label' => $s, 'data' => $kpis, 'backgroundColor' => $c.'33', 'borderColor' => $c, 'borderWidth' => 2, 'spanGaps' => true, 'tension' => 0.3, 'pointRadius' => 4];
        }

        return ['labels' => array_column($mesesData, 'label'), 'datasets' => $datasets];
    }
}
