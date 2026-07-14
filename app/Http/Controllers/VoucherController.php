<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherFactura;
use App\Notifications\VoucherAplicado;
use App\Notifications\VoucherEnviadoParaAplicar;
use App\Notifications\ReporteSedeRechazado;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    private const MAX_SIZE_KB  = 20480;
    private const MIMES        = 'jpg,jpeg,png,gif,webp,xlsx,xls,csv,pdf';

    public function index()
    {
        $user         = auth()->user();
        $puedeAplicar = $user->isFinanzas();

        if (!$user->puedeVerVouchers()) {
            abort(403, 'No tienes permiso para acceder a Vouchers.');
        }

        $esRevisor = $user->puedeRevisarReportesSedes();
        $puedeCrear = $user->isSede() || $user->isSuperAdmin() || $user->isAdmin();

        // Panel de pendientes de aplicar (solo finanzas)
        $pendientes = collect();
        if ($puedeAplicar) {
            $pendientes = Voucher::with(['creator', 'facturas'])
                ->where('status', 'pendiente')
                ->latest()
                ->paginate(20, ['*'], 'pendientes');
        }

        // Límites reales de subida del servidor (para validar en el front antes de
        // enviar y no perder el envío). Se calculan desde el php.ini efectivo.
        $maxUploadFiles = (int) ini_get('max_file_uploads');
        $postMax        = $this->iniAObytes(ini_get('post_max_size'));
        // Margen de 2 MB para los campos del formulario (código, facturas, csrf…).
        $maxUploadTotal = max(1024 * 1024, $postMax - 2 * 1024 * 1024);

        return view('vouchers.index', [
            'puedeAplicar'   => $puedeAplicar,
            'esRevisor'      => $esRevisor,
            'puedeCrear'     => $puedeCrear,
            'pendientes'     => $pendientes,
            'sedUsuario'     => $user->sede,
            'maxUploadFiles' => $maxUploadFiles ?: 20,
            'maxUploadTotal' => $maxUploadTotal,
        ]);
    }

    /** Convierte un valor de php.ini tipo "40M"/"2G"/"512K" a bytes. */
    private function iniAObytes(?string $valor): int
    {
        $valor = trim((string) $valor);
        if ($valor === '') return 0;
        $num  = (int) $valor;
        $unidad = strtolower(substr($valor, -1));
        return match ($unidad) {
            'g'     => $num * 1024 * 1024 * 1024,
            'm'     => $num * 1024 * 1024,
            'k'     => $num * 1024,
            default => (int) $valor,
        };
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'codigo'             => 'required|string|max:50',
            'facturas'           => 'required|array|min:1',
            'facturas.*.factura' => 'required|string|max:100',
            'facturas.*.ruc'     => 'required|digits:11',
            'facturas.*.monto'   => 'required|numeric|min:0.01',
            'archivos'           => 'nullable|array',
            'archivos.*'         => 'file|max:' . self::MAX_SIZE_KB . '|mimes:' . self::MIMES,
        ], [
            'codigo.required'          => 'El número de voucher es obligatorio.',
            'facturas.required'        => 'Debes agregar al menos una factura.',
            'facturas.*.ruc.required'  => 'El RUC es obligatorio en cada factura.',
            'facturas.*.ruc.digits'    => 'El RUC debe tener exactamente 11 dígitos.',
            'archivos.*.mimes'         => 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP) y archivos Excel/CSV/PDF.',
            'archivos.*.max'           => 'Cada archivo no puede superar 20 MB.',
        ]);

        $archivosGuardados = [];
        if ($request->hasFile('archivos')) {
            $archivosGuardados = $this->guardarArchivos($request->file('archivos'), $user->sede ?? 'GENERAL');
        }

        $total = collect($request->facturas)->sum('monto');

        $voucher = Voucher::create([
            'codigo'        => strtoupper(trim($request->codigo)),
            'sede'          => $user->sede ?? 'SIN SEDE',
            'status'        => 'pendiente',
            'archivos'      => $archivosGuardados ?: null,
            'total'         => $total,
            'solicitado_at' => now()->toDateString(),
            'created_by'    => $user->id,
        ]);

        foreach ($request->facturas as $f) {
            $voucher->facturas()->create([
                'factura' => trim($f['factura']),
                'ruc'     => trim($f['ruc']),
                'monto'   => $f['monto'],
            ]);
        }

        $voucher->load(['creator', 'aplicador', 'facturas']);

        // TEMPORAL: notificación desactivada mientras el personal se adapta al sistema
        // $silvia = User::find(self::SILVIA_ID);
        // if ($silvia) {
        //     $silvia->notify(new VoucherEnviadoParaAplicar($voucher));
        // }

        return response()->json([
            'success' => true,
            'message' => 'Voucher creado y enviado a Silvia para aplicar.',
            'voucher' => $this->formatVoucher($voucher),
        ]);
    }

    public function addFactura(Request $request, $id)
    {
        $request->validate([
            'factura' => 'required|string|max:100',
            'ruc'     => 'required|digits:11',
            'monto'   => 'required|numeric|min:0.01',
        ], [
            'ruc.required' => 'El RUC es obligatorio.',
            'ruc.digits'   => 'El RUC debe tener exactamente 11 dígitos.',
        ]);

        $voucher = Voucher::findOrFail($id);

        if ($voucher->status === 'aplicado') {
            return response()->json(['success' => false, 'message' => 'No se puede modificar un voucher ya aplicado.'], 422);
        }

        $factura = $voucher->facturas()->create([
            'factura' => trim($request->factura),
            'ruc'     => trim($request->ruc),
            'monto'   => $request->monto,
        ]);

        $nuevoTotal = $voucher->facturas()->sum('monto');
        $voucher->update(['total' => $nuevoTotal]);
        $this->resetRevisionSiRechazado($voucher);

        return response()->json([
            'success'     => true,
            'factura'     => ['id' => $factura->id, 'factura' => $factura->factura, 'ruc' => $factura->ruc, 'monto' => $factura->monto],
            'nuevo_total' => $nuevoTotal,
        ]);
    }

    public function removeFactura($id)
    {
        $factura    = VoucherFactura::findOrFail($id);
        $voucherId  = $factura->voucher_id;
        $factura->delete();

        $voucher    = Voucher::findOrFail($voucherId);
        $nuevoTotal = $voucher->facturas()->sum('monto');
        $voucher->update(['total' => $nuevoTotal]);
        $this->resetRevisionSiRechazado($voucher);

        return response()->json([
            'success'     => true,
            'nuevo_total' => $nuevoTotal,
        ]);
    }

    /**
     * Si un voucher rechazado se reedita (cambian facturas), la revisión
     * vuelve a "sin revisar" para que finanzas re-valide la corrección.
     */
    private function resetRevisionSiRechazado(Voucher $voucher): void
    {
        if ($voucher->revision_estado === 'rechazado') {
            $voucher->update([
                'revision_estado'        => null,
                'revision_motivo'        => null,
                'revision_kpi_penalidad' => null,
                'revision_archivos'      => null,
                'revision_user_id'       => null,
                'revision_at'            => null,
            ]);
        }
    }

    public function enviarAplicar($id)
    {
        $voucher = Voucher::with(['facturas', 'creator'])->findOrFail($id);

        if ($voucher->created_by !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        // TEMPORAL: notificación desactivada mientras el personal se adapta al sistema
        // $silvia = User::find(self::SILVIA_ID);
        // if ($silvia) {
        //     $silvia->notify(new VoucherEnviadoParaAplicar($voucher));
        // }

        return response()->json([
            'success' => true,
            'message' => 'Notificación reenviada a Silvia.',
        ]);
    }

    public function aplicar($id)
    {
        if (!auth()->user()->isFinanzas()) {
            return response()->json(['success' => false, 'message' => 'Solo el equipo de Finanzas puede aplicar vouchers.'], 403);
        }

        $voucher = Voucher::with(['creator', 'facturas'])->findOrFail($id);

        $voucher->update([
            'status'      => 'aplicado',
            'applied_by'  => auth()->id(),
            'aplicado_at' => now()->toDateString(),
        ]);

        if ($voucher->creator) {
            $voucher->creator->notify(new VoucherAplicado($voucher->fresh()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Voucher aplicado. Se notificó al solicitante.',
            'voucher' => $this->formatVoucher($voucher->fresh()->load(['creator', 'aplicador', 'facturas'])),
        ]);
    }

    public function destroy($id)
    {
        $user    = auth()->user();
        $voucher = Voucher::findOrFail($id);

        if ($voucher->created_by !== $user->id && !$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        foreach ($voucher->archivos ?? [] as $archivo) {
            if (!empty($archivo['path'])) {
                Storage::disk('public')->delete($archivo['path']);
            }
        }

        $voucher->delete();

        return response()->json(['success' => true, 'message' => 'Voucher eliminado.']);
    }

    public function getFacturas($id)
    {
        $voucher = Voucher::with(['facturas', 'revisor'])->findOrFail($id);
        $user    = auth()->user();

        return response()->json([
            'id'       => $voucher->id,
            'codigo'   => $voucher->codigo,
            'status'   => $voucher->status,
            'facturas' => $voucher->facturas->map(fn($f) => [
                'id'      => $f->id,
                'factura' => $f->factura,
                'ruc'     => $f->ruc,
                'monto'   => $f->monto,
            ]),
            'archivos' => collect($voucher->archivos ?? [])->map(fn($a, $idx) => [
                'name' => $a['name'] ?? 'archivo',
                'url'  => isset($a['path']) ? route('vouchers.archivo', ['id' => $id, 'index' => $idx]) : null,
                'mime' => $a['mime'] ?? '',
                'size' => isset($a['size']) ? round($a['size'] / 1024, 1) . ' KB' : '',
            ]),
            'conformidad_label'      => $voucher->conformidadLabel(),
            'conformidad_color'      => $voucher->conformidadColor(),
            'revision_estado'        => $voucher->revision_estado,
            'revision_motivo'        => $voucher->revision_motivo,
            'revision_kpi_penalidad' => $voucher->revision_kpi_penalidad !== null ? (float) $voucher->revision_kpi_penalidad : null,
            'revision_revisor'       => $voucher->revisor?->name,
            'revision_at'            => $voucher->revision_at?->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'revision_archivos'      => $this->mapRevisionArchivos($voucher),
            'puede_revisar'          => $user->puedeRevisarReportesSedes(),
        ]);
    }

    /** Mapea los adjuntos de la revisión con URLs de preview/descarga. */
    private function mapRevisionArchivos(Voucher $voucher): array
    {
        return collect($voucher->revision_archivos ?? [])->map(function ($a, $i) use ($voucher) {
            return [
                'name'        => $a['name'] ?? 'archivo',
                'es_imagen'   => str_starts_with($a['mime'] ?? '', 'image/'),
                'preview_url' => route('vouchers.revisionFile', ['id' => $voucher->id, 'index' => $i]),
            ];
        })->values()->all();
    }

    public function servirArchivo($id, $index)
    {
        $voucher  = Voucher::findOrFail($id);
        $archivos = $voucher->archivos ?? [];

        if (!isset($archivos[$index])) {
            abort(404, 'Archivo no encontrado.');
        }

        $archivo = $archivos[$index];

        if (Storage::disk('public')->exists($archivo['path'])) {
            return response()->file(
                Storage::disk('public')->path($archivo['path']),
                [
                    'Content-Type'        => $archivo['mime'] ?? 'application/octet-stream',
                    'Content-Disposition' => 'inline; filename="' . $archivo['name'] . '"',
                ]
            );
        }

        abort(404, 'El archivo ya no está disponible.');
    }

    // ── Revisión de finanzas (conforme / observado / rechazado) ──

    public function revisar(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user->puedeRevisarReportesSedes()) {
            return response()->json(['success' => false, 'message' => 'Sin permiso para revisar vouchers.'], 403);
        }

        $voucher = Voucher::with('creator')->findOrFail($id);

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

        $voucher->revision_estado        = $data['estado'];
        $voucher->revision_motivo        = $data['motivo'] ?? null;
        $voucher->revision_kpi_penalidad = $data['estado'] === 'conforme_observado' ? (float) $data['penalidad'] : null;
        $voucher->revision_user_id       = $user->id;
        $voucher->revision_at            = Carbon::now('America/Lima');

        if ($request->hasFile('archivos')) {
            $voucher->revision_archivos = $this->guardarArchivos($request->file('archivos'), $voucher->sede ?? 'GENERAL');
        }

        $voucher->save();

        if ($data['estado'] === 'rechazado' || $data['estado'] === 'conforme_observado') {
            $this->notificarRevision($voucher, $data['estado']);
        }

        ActivityLogService::log(
            $user->id, 'revisar_voucher', 'Voucher', $voucher->id,
            "Marcó voucher {$voucher->codigo} como {$data['estado']} (sede: {$voucher->sede})"
        );

        $mensajes = [
            'conforme'           => 'Voucher marcado como conforme.',
            'conforme_observado' => 'Voucher conforme observado. KPI penalizado y notificado a la sede.',
            'rechazado'          => 'Voucher rechazado. Se notificó a la sede.',
        ];

        return response()->json([
            'success'         => true,
            'message'         => $mensajes[$data['estado']],
            'revision_estado' => $voucher->revision_estado,
        ]);
    }

    private function notificarRevision(Voucher $voucher, string $estado): void
    {
        $owner = $voucher->creator;
        if (!$owner || empty($owner->email)) {
            return;
        }
        try {
            Notification::route('mail', $owner->email)->notify(new ReporteSedeRechazado(
                'Vouchers',
                $voucher->sede,
                $voucher->codigo,
                $voucher->revision_motivo ?? '',
                auth()->user()->name,
                route('vouchers.index'),
                $estado,
                $voucher->revision_kpi_penalidad !== null ? (float) $voucher->revision_kpi_penalidad : null
            ));
        } catch (\Exception $e) {
            Log::error("Notificación de revisión (voucher) no enviada: " . $e->getMessage());
        }
    }

    /** Sirve un adjunto de la revisión (inline por defecto, ?download=1 para bajar). */
    public function revisionFile($id, int $index, Request $request)
    {
        if (!auth()->user()->puedeVerVouchers()) {
            abort(403);
        }
        $voucher = Voucher::findOrFail($id);
        $archivo = ($voucher->revision_archivos ?? [])[$index] ?? null;
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

    // ── Historial (AJAX, paginado + filtros) ──────────────────────

    public function historial(Request $request)
    {
        $user = auth()->user();

        if (!$user->puedeVerVouchers()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $query = Voucher::with(['creator', 'aplicador']);

        // Sede solo ve lo suyo; admin/finanzas ven todo
        $verTodo = $user->isSuperAdmin() || $user->isAdmin() || $user->isFinanzas();
        if (!$verTodo) {
            $query->where('sede', $user->sede);
        }

        if ($request->filled('sede'))   $query->where('sede', $request->sede);
        if ($request->filled('estado')) $query->where('status', $request->estado);
        if ($request->filled('fecha'))  $query->whereDate('solicitado_at', $request->fecha);

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
            'data'         => $items->map(fn($v) => $this->filaHistorial($v))->all(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / max(1, $perPage)),
        ]);
    }

    /** Formatea un voucher como fila del historial. */
    private function filaHistorial(Voucher $v): array
    {
        $user = auth()->user();

        $demora = null;
        if ($v->solicitado_at) {
            $fin    = $v->aplicado_at ?? now()->toDateString();
            $demora = $v->solicitado_at->diffInDays($fin);
        }

        return [
            'id'                => $v->id,
            'codigo'            => $v->codigo,
            'sede'              => $v->sede,
            'status'            => $v->status,
            'total'             => number_format((float) $v->total, 2),
            'solicitado_at'     => $v->solicitado_at?->format('d/m/Y'),
            'aplicado_at'       => $v->aplicado_at?->format('d/m/Y'),
            'creator_name'      => $v->creator?->name,
            'demora'            => $demora,
            'conformidad_label' => $v->conformidadLabel(),
            'conformidad_color' => $v->conformidadColor(),
            'revision_estado'   => $v->revision_estado,
            'puede_reenviar'    => $v->status === 'pendiente' && $v->created_by === $user->id,
            'puede_eliminar'    => $v->status === 'pendiente' && ($v->created_by === $user->id || $user->isAdmin() || $user->isSuperAdmin()),
            'puede_revisar'     => $user->puedeRevisarReportesSedes(),
        ];
    }

    public function sedesDisponibles()
    {
        $user = auth()->user();
        if (!$user->puedeVerVouchers()) {
            return response()->json([], 403);
        }
        $query = Voucher::selectRaw('DISTINCT sede')->orderBy('sede');
        $verTodo = $user->isSuperAdmin() || $user->isAdmin() || $user->isFinanzas();
        if (!$verTodo) {
            $query->where('sede', $user->sede);
        }
        return response()->json($query->pluck('sede'));
    }

    // ── KPI semanal de conformidad ────────────────────────────────

    /**
     * Promedio semanal de conformidad por sede (últimas 8 semanas ISO).
     * Solo cuentan los vouchers ya revisados; los pendientes se excluyen.
     */
    public function kpiSemanal(Request $request)
    {
        $user = auth()->user();
        if (!$user->puedeVerVouchers()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $verTodo    = $user->isSuperAdmin() || $user->isAdmin() || $user->isFinanzas();
        $sedeFiltro = $verTodo ? $request->get('sede') : $user->sede;

        $hoy    = Carbon::now('America/Lima');
        $semanas = [];
        for ($i = 7; $i >= 0; $i--) {
            $ref     = $hoy->copy()->subWeeks($i);
            $inicio  = $ref->copy()->startOfWeek(Carbon::MONDAY);
            $fin     = $ref->copy()->endOfWeek(Carbon::SUNDAY);
            $semanas[] = [
                'inicio' => $inicio->toDateString(),
                'fin'    => $fin->toDateString(),
                'label'  => $inicio->format('d/m') . '–' . $fin->format('d/m'),
            ];
        }

        $rangoInicio = $semanas[0]['inicio'];
        $rangoFin    = $semanas[count($semanas) - 1]['fin'];

        $query = Voucher::whereNotNull('revision_estado')
            ->whereNotNull('solicitado_at')
            ->whereBetween('solicitado_at', [$rangoInicio, $rangoFin]);
        if ($sedeFiltro) {
            $query->where('sede', $sedeFiltro);
        }
        $vouchers = $query->get();

        $labels = [];
        $data   = [];
        foreach ($semanas as $s) {
            $delRango = $vouchers->filter(function ($v) use ($s) {
                $d = $v->solicitado_at->toDateString();
                return $d >= $s['inicio'] && $d <= $s['fin'];
            });
            $labels[] = $s['label'];
            if ($delRango->isEmpty()) {
                $data[] = null;
            } else {
                $prom = $delRango->avg(fn($v) => $v->conformidadKpi() ?? 0);
                $data[] = round($prom, 1);
            }
        }

        // Resumen de la semana actual
        $actual        = $semanas[count($semanas) - 1];
        $delActual     = $vouchers->filter(function ($v) use ($actual) {
            $d = $v->solicitado_at->toDateString();
            return $d >= $actual['inicio'] && $d <= $actual['fin'];
        });
        $promedioActual = $delActual->isEmpty() ? null : round($delActual->avg(fn($v) => $v->conformidadKpi() ?? 0), 1);

        return response()->json([
            'labels'          => $labels,
            'data'            => $data,
            'semana_actual'   => $actual['label'],
            'promedio_actual' => $promedioActual,
            'revisados_actual'=> $delActual->count(),
        ]);
    }

    /* ── privados ──────────────────────────────────────────── */

    private function guardarArchivos(array $archivos, string $sede): array
    {
        $guardados = [];
        $dir = 'vouchers/' . date('Y/m') . '/' . Str::slug($sede);

        foreach ($archivos as $file) {
            $ext    = $file->getClientOriginalExtension();
            $nombre = $file->getClientOriginalName();
            $path   = $file->storeAs($dir, Str::uuid() . '.' . $ext, 'public');

            $guardados[] = [
                'name' => $nombre,
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $guardados;
    }

    private function formatVoucher(Voucher $v): array
    {
        $demora = null;
        if ($v->solicitado_at) {
            $fin    = $v->aplicado_at ?? now()->toDateString();
            $demora = $v->solicitado_at->diffInDays($fin);
        }

        return [
            'id'                => $v->id,
            'codigo'            => $v->codigo,
            'sede'              => $v->sede,
            'status'            => $v->status,
            'total'             => number_format((float) $v->total, 2),
            'solicitado_at'     => $v->solicitado_at?->format('d/m/Y'),
            'aplicado_at'       => $v->aplicado_at?->format('d/m/Y'),
            'creator_name'      => $v->creator?->name,
            'aplicador_name'    => $v->aplicador?->name,
            'demora'            => $demora,
            'conformidad_label' => $v->conformidadLabel(),
            'conformidad_color' => $v->conformidadColor(),
            'revision_estado'   => $v->revision_estado,
            'archivos_count'    => count($v->archivos ?? []),
            'facturas'          => $v->facturas->map(fn($f) => [
                'id'      => $f->id,
                'factura' => $f->factura,
                'ruc'     => $f->ruc,
                'monto'   => number_format((float) $f->monto, 2),
            ])->toArray(),
        ];
    }
}
