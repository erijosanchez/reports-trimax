<?php

namespace App\Http\Controllers;

use App\Models\SolicitudDesbloqueo;
use App\Models\User;
use App\Notifications\DesbloqueoCreado;
use App\Notifications\DesbloqueoRevisado;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DesbloqueoController extends Controller
{
    private const MAX_SIZE_KB = 20480;
    private const MIMES       = 'jpg,jpeg,png,webp,pdf,xlsx,xls,csv';

    // ── Vista ─────────────────────────────────────────────────────

    public function index()
    {
        $user = auth()->user();

        if (!$user->puedeVerDesbloqueo()) {
            abort(403, 'No tienes permiso para acceder a Desbloqueo.');
        }

        return view('desbloqueo.index', [
            'esRevisor'      => $user->puedeRevisarReportesSedes(),
            'puedeCrear'     => $user->isSede() || $user->isSuperAdmin() || $user->isAdmin(),
            'verTodo'        => $this->verTodo($user),
            'verKpiSedes'    => !$user->isFinanzas(),  // finanzas no ve el cuadro KPI Sedes
            'verKpiFinanzas' => !$user->isSede(),      // sede no ve KPI Finanzas (cuadro + columna)
            'sedUsuario'     => $user->sede,
        ]);
    }

    // ── Crear solicitud (sede) ────────────────────────────────────

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->isSede() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Sin permiso para crear solicitudes.'], 403);
        }

        $data = $request->validate([
            'ruc'          => 'required|digits:11',
            'razon_social' => 'required|string|max:255',
            'comentarios'  => 'nullable|string|max:2000',
        ], [
            'ruc.required'          => 'El RUC es obligatorio.',
            'ruc.digits'            => 'El RUC debe tener exactamente 11 dígitos.',
            'razon_social.required' => 'La razón social es obligatoria.',
        ]);

        $solicitud = SolicitudDesbloqueo::create([
            'user_id'      => $user->id,
            'sede'         => $user->sede ?? 'SIN SEDE',
            'ruc'          => trim($data['ruc']),
            'razon_social' => trim($data['razon_social']),
            'comentarios'  => $data['comentarios'] ?? null,
        ]);

        $this->notificarCreacion($solicitud->load('user'));

        ActivityLogService::log($user->id, 'crear_desbloqueo', 'SolicitudDesbloqueo', $solicitud->id, "Solicitó desbloqueo RUC {$solicitud->ruc} (sede: {$solicitud->sede})");

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de desbloqueo enviada a finanzas.',
        ]);
    }

    // ── Revisión de finanzas ──────────────────────────────────────

    public function revisar(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->puedeRevisarReportesSedes()) {
            return response()->json(['success' => false, 'message' => 'Sin permiso para revisar solicitudes.'], 403);
        }

        $solicitud = SolicitudDesbloqueo::with('user')->findOrFail($id);

        $data = $request->validate([
            'estado'     => 'required|in:conforme,conforme_observado,rechazado',
            'motivo'     => 'required_unless:estado,conforme|nullable|string|max:2000',
            'penalidad'  => 'required_if:estado,conforme_observado|nullable|in:20,50',
            'archivos'   => 'nullable|array',
            'archivos.*' => 'file|max:' . self::MAX_SIZE_KB . '|mimes:' . self::MIMES,
        ], [
            'motivo.required_unless' => 'Debes indicar el motivo/observación.',
            'penalidad.required_if'  => 'Debes elegir el descuento de KPI (20% o 50%).',
            'penalidad.in'           => 'El descuento debe ser 20% o 50%.',
            'archivos.*.mimes'       => 'Solo imágenes (JPG, PNG, WEBP), PDF o Excel/CSV.',
            'archivos.*.max'         => 'Cada archivo no puede superar 20 MB.',
        ]);

        $solicitud->revision_estado        = $data['estado'];
        $solicitud->revision_motivo        = $data['motivo'] ?? null;
        $solicitud->revision_kpi_penalidad = $data['estado'] === 'conforme_observado' ? (float) $data['penalidad'] : null;
        $solicitud->revision_user_id       = $user->id;
        $solicitud->revision_at            = Carbon::now('America/Lima');

        if ($request->hasFile('archivos')) {
            $solicitud->revision_archivos = $this->guardarArchivos($request->file('archivos'), $solicitud->sede ?? 'GENERAL');
        }

        $solicitud->save();

        $this->notificarRevision($solicitud);

        ActivityLogService::log($user->id, 'revisar_desbloqueo', 'SolicitudDesbloqueo', $solicitud->id, "Revisó desbloqueo RUC {$solicitud->ruc} como {$data['estado']}");

        $mensajes = [
            'conforme'           => 'Solicitud marcada como conforme (aprobada).',
            'conforme_observado' => 'Solicitud conforme observada. KPI de la sede penalizado y notificado.',
            'rechazado'          => 'Solicitud rechazada. Se notificó al solicitante.',
        ];

        return response()->json([
            'success'         => true,
            'message'         => $mensajes[$data['estado']],
            'revision_estado' => $solicitud->revision_estado,
        ]);
    }

    // ── Eliminar (solo pendientes) ────────────────────────────────

    public function destroy($id)
    {
        $user      = auth()->user();
        $solicitud = SolicitudDesbloqueo::findOrFail($id);

        if (!is_null($solicitud->revision_estado)) {
            return response()->json(['success' => false, 'message' => 'No se puede eliminar una solicitud ya revisada.'], 422);
        }

        if ($solicitud->user_id !== $user->id && !$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $solicitud->delete();

        return response()->json(['success' => true, 'message' => 'Solicitud eliminada.']);
    }

    // ── Detalle ───────────────────────────────────────────────────

    public function show($id)
    {
        $user      = auth()->user();
        $solicitud = SolicitudDesbloqueo::with(['user', 'revisor'])->findOrFail($id);

        if (!$this->puedeVer($solicitud, $user)) {
            abort(403);
        }

        return response()->json([
            'id'                     => $solicitud->id,
            'sede'                   => $solicitud->sede,
            'ruc'                    => $solicitud->ruc,
            'razon_social'           => $solicitud->razon_social,
            'comentarios'            => $solicitud->comentarios,
            'solicitante'            => $solicitud->user?->name,
            'creado'                 => $solicitud->created_at?->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'kpi_sede_label'         => $solicitud->kpiSedeLabel(),
            'kpi_sede_color'         => $solicitud->kpiSedeColor(),
            'kpi_finanzas_label'     => $solicitud->kpiFinanzasLabel(),
            'kpi_finanzas_color'     => $solicitud->kpiFinanzasColor(),
            'revision_estado'        => $solicitud->revision_estado,
            'revision_motivo'        => $solicitud->revision_motivo,
            'revision_kpi_penalidad' => $solicitud->revision_kpi_penalidad !== null ? (float) $solicitud->revision_kpi_penalidad : null,
            'revision_revisor'       => $solicitud->revisor?->name,
            'revision_at'            => $solicitud->revision_at?->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'revision_archivos'      => $this->mapRevisionArchivos($solicitud),
            'puede_revisar'          => $user->puedeRevisarReportesSedes() && is_null($solicitud->revision_estado),
        ]);
    }

    private function mapRevisionArchivos(SolicitudDesbloqueo $s): array
    {
        return collect($s->revision_archivos ?? [])->map(function ($a, $i) use ($s) {
            return [
                'name'        => $a['name'] ?? 'archivo',
                'es_imagen'   => str_starts_with($a['mime'] ?? '', 'image/'),
                'preview_url' => route('desbloqueo.revisionFile', ['id' => $s->id, 'index' => $i]),
            ];
        })->values()->all();
    }

    public function revisionFile($id, int $index, Request $request)
    {
        $user      = auth()->user();
        $solicitud = SolicitudDesbloqueo::findOrFail($id);

        if (!$this->puedeVer($solicitud, $user)) {
            abort(403);
        }

        $archivo = ($solicitud->revision_archivos ?? [])[$index] ?? null;
        if (!$archivo || !Storage::disk('public')->exists($archivo['path'])) {
            abort(404, 'Archivo no encontrado.');
        }
        if ($request->boolean('download')) {
            return Storage::disk('public')->download($archivo['path'], $archivo['name']);
        }
        return response()->file(Storage::disk('public')->path($archivo['path']), [
            'Content-Type'        => $archivo['mime'] ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . $archivo['name'] . '"',
        ]);
    }

    // ── Historial (AJAX paginado + filtros) ───────────────────────

    public function historial(Request $request)
    {
        $user = auth()->user();
        if (!$user->puedeVerDesbloqueo()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $query = SolicitudDesbloqueo::with(['user', 'revisor']);

        if (!$this->verTodo($user)) {
            $query->where('sede', $user->sede);
        }

        if ($request->filled('sede'))  $query->where('sede', $request->sede);
        if ($request->filled('fecha')) $query->whereDate('created_at', $request->fecha);
        if ($request->filled('conformidad')) {
            if ($request->conformidad === 'sin_revisar') {
                $query->whereNull('revision_estado');
            } else {
                $query->where('revision_estado', $request->conformidad);
            }
        }

        $query->latest();

        $perPage = 25;
        $page    = max(1, (int) $request->get('page', 1));
        $total   = $query->count();
        $items   = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'data'         => $items->map(fn($s) => $this->fila($s))->all(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / max(1, $perPage)),
        ]);
    }

    private function fila(SolicitudDesbloqueo $s): array
    {
        $user = auth()->user();

        return [
            'id'                 => $s->id,
            'sede'               => $s->sede,
            'ruc'                => $s->ruc,
            'razon_social'       => $s->razon_social,
            'solicitante'        => $s->user?->name,
            'creado'             => $s->created_at?->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'revisado'           => $s->revision_at?->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'revisor'            => $s->revisor?->name,
            'revision_estado'    => $s->revision_estado,
            'kpi_sede_label'     => $s->kpiSedeLabel(),
            'kpi_sede_color'     => $s->kpiSedeColor(),
            'kpi_finanzas_label' => $s->kpiFinanzasLabel(),
            'kpi_finanzas_color' => $s->kpiFinanzasColor(),
            'puede_revisar'      => $user->puedeRevisarReportesSedes() && is_null($s->revision_estado),
            'puede_eliminar'     => is_null($s->revision_estado) && ($s->user_id === $user->id || $user->isAdmin() || $user->isSuperAdmin()),
        ];
    }

    public function sedesDisponibles()
    {
        $user = auth()->user();
        if (!$user->puedeVerDesbloqueo()) {
            return response()->json([], 403);
        }
        $query = SolicitudDesbloqueo::selectRaw('DISTINCT sede')->orderBy('sede');
        if (!$this->verTodo($user)) {
            $query->where('sede', $user->sede);
        }
        return response()->json($query->pluck('sede'));
    }

    // ── KPI semanal (sedes + finanzas) ────────────────────────────

    public function kpiSemanal(Request $request)
    {
        $user = auth()->user();
        if (!$user->puedeVerDesbloqueo()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $sedeFiltro = $this->verTodo($user) ? $request->get('sede') : $user->sede;

        $hoy     = Carbon::now('America/Lima');
        $semanas = [];
        for ($i = 7; $i >= 0; $i--) {
            $ref    = $hoy->copy()->subWeeks($i);
            $inicio = $ref->copy()->startOfWeek(Carbon::MONDAY);
            $fin    = $ref->copy()->endOfWeek(Carbon::SUNDAY);
            $semanas[] = [
                'inicio' => $inicio->toDateString(),
                'fin'    => $fin->toDateString(),
                'label'  => $inicio->format('d/m') . '–' . $fin->format('d/m'),
            ];
        }

        $rangoInicio = $semanas[0]['inicio'] . ' 00:00:00';
        $rangoFin    = $semanas[count($semanas) - 1]['fin'] . ' 23:59:59';

        $query = SolicitudDesbloqueo::whereNotNull('revision_estado')
            ->whereBetween('created_at', [$rangoInicio, $rangoFin]);
        if ($sedeFiltro) {
            $query->where('sede', $sedeFiltro);
        }
        $solicitudes = $query->get();

        $labels        = [];
        $dataSedes     = [];
        $dataFinanzas  = [];
        foreach ($semanas as $s) {
            $delRango = $solicitudes->filter(function ($x) use ($s) {
                $d = $x->created_at->toDateString();
                return $d >= $s['inicio'] && $d <= $s['fin'];
            });
            $labels[] = $s['label'];
            if ($delRango->isEmpty()) {
                $dataSedes[]    = null;
                $dataFinanzas[] = null;
            } else {
                $dataSedes[]    = round($delRango->avg(fn($x) => $x->kpiSede() ?? 0), 1);
                $dataFinanzas[] = round($delRango->avg(fn($x) => $x->kpiFinanzas() ?? 0), 1);
            }
        }

        $actual    = $semanas[count($semanas) - 1];
        $delActual = $solicitudes->filter(function ($x) use ($actual) {
            $d = $x->created_at->toDateString();
            return $d >= $actual['inicio'] && $d <= $actual['fin'];
        });

        return response()->json([
            'labels'               => $labels,
            'sedes'                => $dataSedes,
            'finanzas'             => $dataFinanzas,
            'semana_actual'        => $actual['label'],
            'promedio_sedes'       => $delActual->isEmpty() ? null : round($delActual->avg(fn($x) => $x->kpiSede() ?? 0), 1),
            'promedio_finanzas'    => $delActual->isEmpty() ? null : round($delActual->avg(fn($x) => $x->kpiFinanzas() ?? 0), 1),
            'revisados_actual'     => $delActual->count(),
        ]);
    }

    // ── Privados ──────────────────────────────────────────────────

    private function verTodo(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin() || $user->isFinanzas()
            || (!$user->isSede() && (bool) $user->puede_ver_desbloqueo);
    }

    private function puedeVer(SolicitudDesbloqueo $s, User $user): bool
    {
        return $this->verTodo($user) || ($user->isSede() && $s->sede === $user->sede);
    }

    private function guardarArchivos(array $archivos, string $sede): array
    {
        $guardados = [];
        $dir = 'desbloqueo/' . date('Y/m') . '/' . Str::slug($sede);

        foreach ($archivos as $file) {
            $ext  = $file->getClientOriginalExtension();
            $path = $file->storeAs($dir, Str::uuid() . '.' . $ext, 'public');

            $guardados[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $guardados;
    }

    /** Notifica a finanzas + super admin cuando se crea una solicitud. */
    private function notificarCreacion(SolicitudDesbloqueo $solicitud): void
    {
        $destinatarios = User::role(['finanzas', 'super_admin'])
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();

        foreach ($destinatarios as $u) {
            try {
                $u->notify(new DesbloqueoCreado($solicitud));
            } catch (\Exception $e) {
                Log::error("Notificación desbloqueo (creación) no enviada a {$u->email}: " . $e->getMessage());
            }
        }
    }

    /** Notifica al solicitante cuando finanzas revisa. */
    private function notificarRevision(SolicitudDesbloqueo $solicitud): void
    {
        $owner = $solicitud->user;
        if (!$owner || empty($owner->email)) {
            return;
        }
        try {
            $owner->notify(new DesbloqueoRevisado($solicitud));
        } catch (\Exception $e) {
            Log::error("Notificación desbloqueo (revisión) no enviada: " . $e->getMessage());
        }
    }
}
