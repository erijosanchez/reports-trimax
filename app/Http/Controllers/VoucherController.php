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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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

        // Piloto de A1 (ARQUITECTURA.md): mismo chequeo que antes
        // ($user->puedeVerVouchers()), ahora a través del Gate registrado en
        // AuthServiceProvider — el helper de User sigue siendo el detalle de
        // implementación.
        if (Gate::denies('ver-vouchers')) {
            abort(403, 'No tienes permiso para acceder a Vouchers.');
        }

        $esRevisor = $user->puedeRevisarReportesSedes();
        $puedeVerTodosLosRevisores = $user->isSuperAdmin() || $user->isAdmin();
        $puedeCrear = $user->isSede() || $user->isSuperAdmin() || $user->isAdmin();

        // El KPI de conformidad es sobre el desempeño de la SEDE, no de
        // finanzas: a finanzas puro no le corresponde verlo (tiene su propio
        // KPI de tiempo de revisión). Admin/superadmin sí ven ambos.
        $puedeVerKpiSede = !$user->isFinanzas() || $user->isSuperAdmin() || $user->isAdmin();

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
            'puedeAplicar'              => $puedeAplicar,
            'esRevisor'                 => $esRevisor,
            'puedeVerTodosLosRevisores' => $puedeVerTodosLosRevisores,
            'puedeVerKpiSede'           => $puedeVerKpiSede,
            'puedeCrear'                => $puedeCrear,
            'pendientes'                => $pendientes,
            'sedUsuario'                => $user->sede,
            'maxUploadFiles'            => $maxUploadFiles ?: 20,
            'maxUploadTotal'            => $maxUploadTotal,
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

        $voucher = DB::transaction(function () use ($request, $user, $archivosGuardados, $total) {
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

            return $voucher;
        });

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

        [$factura, $nuevoTotal] = DB::transaction(function () use ($voucher, $request) {
            $factura = $voucher->facturas()->create([
                'factura' => trim($request->factura),
                'ruc'     => trim($request->ruc),
                'monto'   => $request->monto,
            ]);

            $nuevoTotal = $voucher->facturas()->sum('monto');
            $voucher->update(['total' => $nuevoTotal]);
            $this->resetRevisionSiRechazado($voucher);

            return [$factura, $nuevoTotal];
        });

        return response()->json([
            'success'     => true,
            'factura'     => ['id' => $factura->id, 'factura' => $factura->factura, 'ruc' => $factura->ruc, 'monto' => $factura->monto],
            'nuevo_total' => $nuevoTotal,
        ]);
    }

    public function removeFactura($id)
    {
        $factura = VoucherFactura::findOrFail($id);
        $voucher = Voucher::findOrFail($factura->voucher_id);

        if ($voucher->status === 'aplicado') {
            return response()->json(['success' => false, 'message' => 'No se puede modificar un voucher ya aplicado.'], 422);
        }

        $nuevoTotal = DB::transaction(function () use ($factura, $voucher) {
            $factura->delete();

            $nuevoTotal = $voucher->facturas()->sum('monto');
            $voucher->update(['total' => $nuevoTotal]);
            $this->resetRevisionSiRechazado($voucher);

            return $nuevoTotal;
        });

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
            'aplicado_at' => now(),
        ]);

        // El correo va síncrono por SMTP (Gmail gratis); si falla no debe tumbar
        // la aplicación del voucher, que ya quedó guardada arriba.
        if ($voucher->creator) {
            try {
                $voucher->creator->notify(new VoucherAplicado($voucher->fresh()));
            } catch (\Exception $e) {
                Log::error("Notificación de voucher aplicado no enviada ({$voucher->codigo}): " . $e->getMessage());
            }
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
                Storage::disk('local')->delete($archivo['path']);
            }
        }

        $voucher->delete();

        return response()->json(['success' => true, 'message' => 'Voucher eliminado.']);
    }

    public function getFacturas($id)
    {
        $user = auth()->user();

        if (!$user->puedeVerVouchers()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $voucher = Voucher::with(['facturas', 'revisor', 'creator'])->findOrFail($id);

        return response()->json([
            'id'           => $voucher->id,
            'codigo'       => $voucher->codigo,
            'status'       => $voucher->status,
            'creator_name' => $voucher->creator?->name,
            'hora_envio'   => $voucher->created_at?->setTimezone('America/Lima')->format('d/m/Y H:i'),
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
        if (!auth()->user()->puedeVerVouchers()) {
            abort(403);
        }

        $voucher  = Voucher::findOrFail($id);
        $archivos = $voucher->archivos ?? [];

        if (!isset($archivos[$index])) {
            abort(404, 'Archivo no encontrado.');
        }

        $archivo = $archivos[$index];

        if (Storage::disk('local')->exists($archivo['path'])) {
            return response()->file(
                Storage::disk('local')->path($archivo['path']),
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

        // save() + registro de actividad en la misma transacción (A3); la
        // notificación por correo queda fuera para no sostener la conexión
        // de BD abierta durante el I/O de red.
        DB::transaction(function () use ($voucher, $user, $data) {
            $voucher->save();

            ActivityLogService::log(
                $user->id, 'revisar_voucher', 'Voucher', $voucher->id,
                "Marcó voucher {$voucher->codigo} como {$data['estado']} (sede: {$voucher->sede})"
            );
        });

        if ($data['estado'] === 'rechazado' || $data['estado'] === 'conforme_observado') {
            $this->notificarRevision($voucher, $data['estado']);
        }

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
        if (!$archivo || !Storage::disk('local')->exists($archivo['path'])) {
            abort(404, 'Archivo no encontrado.');
        }
        if ($request->boolean('download')) {
            return Storage::disk('local')->download($archivo['path'], $archivo['name']);
        }
        return response()->file(Storage::disk('local')->path($archivo['path']), [
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

        // Sede solo ve lo suyo; admin/finanzas/permiso especial ven todo
        $verTodo = $user->isSuperAdmin() || $user->isAdmin() || $user->isFinanzas() || $user->puede_ver_vouchers;
        if (!$verTodo) {
            $query->where('sede', $user->sede);
        }

        if ($request->filled('sede'))   $query->where('sede', $request->sede);
        if ($request->filled('estado')) $query->where('status', $request->estado);

        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->whereBetween('solicitado_at', [$request->desde, $request->hasta]);
        } elseif ($request->filled('desde')) {
            $query->whereDate('solicitado_at', '>=', $request->desde);
        } elseif ($request->filled('hasta')) {
            $query->whereDate('solicitado_at', '<=', $request->hasta);
        }

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

    /**
     * false si aplicado_at es de antes de migrar la columna a datetime: esos
     * registros se guardaron con now()->toDateString() y siempre quedaron en
     * 00:00:00 — no tienen hora real, así que no se les puede sacar
     * precisión sin inventar un dato que nunca se capturó. Cualquier voucher
     * aplicado después del fix va a tener una hora real (la probabilidad de
     * que caiga justo en la medianoche exacta es despreciable).
     */
    private function aplicadoTieneHoraReal(Voucher $v): bool
    {
        return $v->aplicado_at && $v->aplicado_at->format('H:i:s') !== '00:00:00';
    }

    /**
     * Minutos transcurridos entre el envío y la aplicación del voucher, para
     * precisión de horas/minutos en la columna "Demora" del historial.
     *
     * solicitado_at solo guarda fecha (cast 'date'), así que se usa
     * created_at como proxy de la hora real de envío (igual que hora_envio,
     * fijado una sola vez en store() y nunca vuelto a tocar). aplicado_at sí
     * es un datetime real (columna migrada de 'date' a 'datetime' junto con
     * este cambio) que aplicar() guarda con now() completo — es la fuente
     * directa, sin depender de updated_at (que cualquier UPDATE futuro sobre
     * el voucher podría mover).
     *
     * Para vouchers aplicados antes del fix (ver aplicadoTieneHoraReal), se
     * cae de vuelta al cálculo original en días completos (solicitado_at →
     * aplicado_at, ambos sin hora) convertido a minutos, para no mostrar
     * horas/minutos falsos sobre datos que nunca los tuvieron.
     */
    private function demoraEnMinutos(Voucher $v): ?int
    {
        if (!$v->solicitado_at) {
            return null;
        }

        if (!$v->aplicado_at) {
            $inicio = $v->created_at ?? $v->solicitado_at;
            return max(0, (int) $inicio->diffInMinutes(now()));
        }

        if (!$this->aplicadoTieneHoraReal($v)) {
            return (int) $v->solicitado_at->diffInDays($v->aplicado_at) * 1440;
        }

        $inicio = $v->created_at ?? $v->solicitado_at;
        return max(0, (int) $inicio->diffInMinutes($v->aplicado_at));
    }

    /** Formatea un voucher como fila del historial. */
    private function filaHistorial(Voucher $v): array
    {
        $user = auth()->user();

        return [
            'id'                => $v->id,
            'codigo'            => $v->codigo,
            'sede'              => $v->sede,
            'status'            => $v->status,
            'total'             => number_format((float) $v->total, 2),
            'solicitado_at'     => $v->solicitado_at?->format('d/m/Y'),
            'hora_envio'        => $v->created_at?->setTimezone('America/Lima')->format('H:i'),
            'aplicado_at'       => $v->aplicado_at?->setTimezone('America/Lima')->format('d/m/Y'),
            'hora_aplicado'     => $this->aplicadoTieneHoraReal($v) ? $v->aplicado_at->setTimezone('America/Lima')->format('H:i') : null,
            'creator_name'      => $v->creator?->name,
            'demora'            => $this->demoraEnMinutos($v),
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
        $verTodo = $user->isSuperAdmin() || $user->isAdmin() || $user->isFinanzas() || $user->puede_ver_vouchers;
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
        // Es el KPI de desempeño de la SEDE, no de finanzas — finanzas puro
        // no debe verlo (tiene su propio KPI de tiempo de revisión).
        if ($user->isFinanzas() && !$user->isSuperAdmin() && !$user->isAdmin()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $verTodo    = $user->isSuperAdmin() || $user->isAdmin() || $user->puede_ver_vouchers;
        $sedeFiltro = $verTodo ? $request->get('sede') : $user->sede;

        $semanas     = $this->ultimasSemanas();
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

    /** Últimas 8 semanas ISO (lunes a domingo), hoy incluido, más antigua primero. */
    private function ultimasSemanas(): array
    {
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
        return $semanas;
    }

    // ── KPI semanal de finanzas (tiempo de aplicación) ────────────────

    /**
     * Usuarios disponibles para el selector del KPI de finanzas: cualquiera
     * que ya haya aplicado al menos un voucher. Solo para quien puede ver a
     * todos — cada finanzas individual no elige, ve lo suyo.
     */
    public function revisoresDisponibles()
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            return response()->json([], 403);
        }

        $revisores = User::query()
            ->whereIn('id', Voucher::whereNotNull('applied_by')->distinct()->pluck('applied_by'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($revisores);
    }

    /**
     * Promedio semanal de horas hasta aplicar (solicitado_at → aplicado_at)
     * por quien aplicó, últimas 8 semanas ISO. Se agrupa por semana de
     * SOLICITUD (igual que kpiSemanal()), no por semana de aplicación: a
     * pedido de negocio, un voucher pedido una semana debe contar en esa
     * semana aunque finanzas tarde en aplicarlo y lo destrabe varias semanas
     * después — así el atraso queda visible en la semana que lo originó, en
     * vez de "explotar" en la semana en que por fin se resolvió.
     *
     * Nota: como esto solo cuenta vouchers YA aplicados (aplicado_at no
     * nulo), el promedio de una semana puede seguir moviéndose después de
     * que esa semana termine, si quedan vouchers de esa semana todavía sin
     * aplicar — es la contrapartida aceptada de este criterio.
     *
     * Reusa demoraEnMinutos() — es la misma métrica que la columna "Demora"
     * del historial, incluido su fallback para vouchers aplicados antes de
     * que aplicado_at guardara hora real (ver aplicadoTieneHoraReal).
     *
     * Visibilidad: finanzas (sin ser admin/superadmin) solo ve su propio
     * promedio, sin poder elegir otro usuario. Admin/superadmin puede elegir
     * puntualmente quién aplicó o dejarlo vacío para ver el combinado.
     */
    public function kpiFinanzasSemanal(Request $request)
    {
        $user = auth()->user();
        if (!$user->puedeRevisarReportesSedes()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $puedeVerTodos    = $user->isSuperAdmin() || $user->isAdmin();
        $aplicadorFiltro  = $puedeVerTodos ? $request->get('aplicador') : $user->id;

        $semanas     = $this->ultimasSemanas();
        $rangoInicio = $semanas[0]['inicio'];
        $rangoFin    = $semanas[count($semanas) - 1]['fin'];

        $query = Voucher::whereNotNull('aplicado_at')
            ->whereNotNull('solicitado_at')
            ->whereBetween('solicitado_at', [$rangoInicio, $rangoFin]);
        if ($aplicadorFiltro) {
            $query->where('applied_by', $aplicadorFiltro);
        }
        $vouchers = $query->get();

        $horasAplicacion = fn($v) => $this->demoraEnMinutos($v) / 60;

        $labels = [];
        $data   = [];
        foreach ($semanas as $s) {
            $delRango = $vouchers->filter(function ($v) use ($s) {
                $d = $v->solicitado_at->toDateString();
                return $d >= $s['inicio'] && $d <= $s['fin'];
            });
            $labels[] = $s['label'];
            $data[]   = $delRango->isEmpty() ? null : round($delRango->avg($horasAplicacion), 1);
        }

        $actual    = $semanas[count($semanas) - 1];
        $delActual = $vouchers->filter(function ($v) use ($actual) {
            $d = $v->solicitado_at->toDateString();
            return $d >= $actual['inicio'] && $d <= $actual['fin'];
        });
        $promedioActual = $delActual->isEmpty() ? null : round($delActual->avg($horasAplicacion), 1);

        return response()->json([
            'labels'           => $labels,
            'data'             => $data,
            'semana_actual'    => $actual['label'],
            'promedio_actual'  => $promedioActual,
            'aplicados_actual' => $delActual->count(),
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
            $path   = $file->storeAs($dir, Str::uuid() . '.' . $ext, 'local');

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
        return [
            'id'                => $v->id,
            'codigo'            => $v->codigo,
            'sede'              => $v->sede,
            'status'            => $v->status,
            'total'             => number_format((float) $v->total, 2),
            'solicitado_at'     => $v->solicitado_at?->format('d/m/Y'),
            'aplicado_at'       => $v->aplicado_at?->setTimezone('America/Lima')->format('d/m/Y'),
            'creator_name'      => $v->creator?->name,
            'aplicador_name'    => $v->aplicador?->name,
            'demora'            => $this->demoraEnMinutos($v),
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
