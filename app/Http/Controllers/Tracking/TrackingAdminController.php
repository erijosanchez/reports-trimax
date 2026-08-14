<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Motorizado;
use App\Models\GpsRuta;
use App\Models\Entrega;
use App\Models\EntregaOrden;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class TrackingAdminController extends Controller
{
    private function checkPermiso()
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);
    }

    // ── Mapa en vivo ──────────────────────────────────────
    public function mapaVivo()
    {
        $this->checkPermiso();
        $sedes = Motorizado::SEDES;
        return view('tracking.mapa-vivo', compact('sedes'));
    }

    // ── Resumen diario ────────────────────────────────────
    public function resumenDiario(Request $request)
    {
        $this->checkPermiso();

        $fecha = $request->get('fecha', today()->toDateString());

        $rutas = GpsRuta::with(['motorizado', 'entregas'])
            ->whereDate('fecha', $fecha)
            ->get();

        $kpi = [
            'total_rutas'   => $rutas->count(),
            'total_km'      => round($rutas->sum('distance_km'), 2),
            'completadas'   => $rutas->flatMap->entregas->where('estado', 'completado')->count(),
            'fallidas'      => $rutas->flatMap->entregas->where('estado', 'fallido')->count(),
            'en_ruta'       => $rutas->where('status', 'activa')->count(),
        ];

        $porSede = $rutas->groupBy('motorizado.sede')->map(fn($g) => [
            'rutas'    => $g->count(),
            'km'       => round($g->sum('distance_km'), 2),
            'entregas' => $g->flatMap->entregas->count(),
        ]);

        return view('tracking.resumen-diario', compact('rutas', 'kpi', 'porSede', 'fecha'));
    }

    // Agrega este método
    public function rutasActivasPorMotorizado(int $motorizadoId)
    {
        $this->checkPermiso();

        $rutas = GpsRuta::where('motorizado_id', $motorizadoId)
            ->whereIn('status', ['pendiente', 'activa'])
            ->whereDate('fecha', today())
            ->get(['id', 'fecha', 'status', 'started_at']);

        return response()->json($rutas->map(fn($r) => [
            'id'         => $r->id,
            'label'      => 'Ruta #' . $r->id . ' — ' .
                ucfirst($r->status) . ' — ' .
                $r->fecha->format('d/m/Y'),
            'status'     => $r->status,
        ]));
    }

    // ── Historial km ──────────────────────────────────────
    public function historialKm(Request $request)
    {
        $this->checkPermiso();

        $motorizados = Motorizado::where('estado', 'activo')
            ->orderBy('sede')->orderBy('nombre')->get();

        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $motorizadoId = $request->get('motorizado_id');

        $query = GpsRuta::with(['motorizado', 'entregas'])
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('status', 'completada')
            ->orderByDesc('fecha');

        if ($motorizadoId) $query->where('motorizado_id', $motorizadoId);

        $rutas = $query->paginate(30)->withQueryString();

        return view('tracking.historial-km', compact(
            'rutas',
            'motorizados',
            'desde',
            'hasta',
            'motorizadoId'
        ));
    }

    // ── Gestión motorizados ───────────────────────────────
    public function motorizados()
    {
        $this->checkPermiso();
        $motorizados = Motorizado::withTrashed()->orderBy('sede')->get();
        $sedes = Motorizado::SEDES;
        return view('tracking.motorizados', compact('motorizados', 'sedes'));
    }

    public function storeMotorizado(Request $request)
    {
        $this->checkPermiso();

        $data = $request->validate([
            'nombre'   => 'required|string|max:255',
            'sede'     => 'required|string',
            'tipo'     => 'required|in:motorizado,delivery',
            'telefono' => 'nullable|string|max:50',
            'email'    => 'required|email|unique:motorizados,email',
            'password' => ['required', Password::min(10)->letters()->numbers()->uncompromised()],
            'estado'   => 'required|in:activo,inactivo',
        ]);

        $motorizado = Motorizado::create($data);

        ActivityLogService::log(
            Auth::id(), 'create_motorizado', 'Motorizado', $motorizado->id,
            "Creó motorizado {$motorizado->nombre} (sede: {$motorizado->sede})"
        );

        return response()->json(['success' => true, 'motorizado' => $motorizado]);
    }

    public function updateMotorizado(Request $request, int $id)
    {
        $this->checkPermiso();

        $motorizado = Motorizado::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'nombre'   => 'required|string|max:255',
            'sede'     => 'required|string',
            'tipo'     => 'required|in:motorizado,delivery',
            'telefono' => 'nullable|string|max:50',
            'email'    => "required|email|unique:motorizados,email,{$id}",
            'password' => ['nullable', Password::min(10)->letters()->numbers()->uncompromised()],
            'estado'   => 'required|in:activo,inactivo',
        ]);

        $cambioPassword = !empty($data['password']);
        if (empty($data['password'])) unset($data['password']);

        $motorizado->update($data);

        ActivityLogService::log(
            Auth::id(), 'update_motorizado', 'Motorizado', $motorizado->id,
            "Editó motorizado {$motorizado->nombre} (sede: {$motorizado->sede})" . ($cambioPassword ? ' — cambió contraseña' : '')
        );

        return response()->json(['success' => true]);
    }

    public function destroyMotorizado(int $id)
    {
        $this->checkPermiso();
        $motorizado = Motorizado::findOrFail($id);
        $nombre = $motorizado->nombre;
        $sede   = $motorizado->sede;
        $motorizado->delete();

        ActivityLogService::log(
            Auth::id(), 'delete_motorizado', 'Motorizado', $id,
            "Eliminó motorizado {$nombre} (sede: {$sede})"
        );

        return response()->json(['success' => true]);
    }

    // ── Gestión entregas ──────────────────────────────────
    public function entregas(Request $request)
    {
        $this->checkPermiso();

        $motorizados = Motorizado::where('estado', 'activo')
            ->orderBy('nombre')->get();

        $sedes = Motorizado::SEDES;

        $query = Entrega::with(['motorizado', 'ruta'])
            ->withCount('ordenes')
            ->orderByDesc('id');

        if ($request->filled('sede'))         $query->where('sede', $request->sede);
        if ($request->filled('estado'))       $query->where('estado', $request->estado);
        if ($request->filled('motorizado_id')) $query->where('motorizado_id', $request->motorizado_id);

        $entregas = $query->paginate(25)->withQueryString();

        return view('tracking.entregas', compact('entregas', 'motorizados', 'sedes'));
    }

    public function historialRecorrido()
    {
        $this->checkPermiso();
        $motorizados = Motorizado::where('estado', 'activo')
            ->orderBy('sede')->orderBy('nombre')->get();
        return view('tracking.historial-recorrido', compact('motorizados'));
    }

    public function storeEntrega(Request $request)
    {
        $this->checkPermiso();

        $data = $request->validate([
            'motorizado_id'          => 'required|exists:motorizados,id',
            'ruta_id'                => 'required|exists:gps_rutas,id',
            'cliente_nombre'         => 'required|string|max:255',
            'cliente_telefono'       => 'nullable|string|max:50',
            'ordenes'                => 'required|array|min:1',
            'ordenes.*.numero_orden' => 'required|string|max:100',
            'ordenes.*.cliente'      => 'nullable|string|max:255',
            'ordenes.*.ruc'          => 'nullable|string|max:20',
            'ordenes.*.fecha_orden'  => 'nullable|date',
            'direccion'              => 'required|string',
            'latitud'                => 'nullable|numeric',
            'longitud'               => 'nullable|numeric',
            'sede'                   => 'required|string',
            'notas'                  => 'nullable|string',
        ]);

        $ordenes = collect($data['ordenes'])->unique('numero_orden')->values();

        // Revalida "ocupadas" al momento de guardar (no solo al buscar), por si
        // dos usuarios eligieron la misma orden casi al mismo tiempo.
        $ocupadas = EntregaOrden::whereHas('entrega', fn ($q) => $q->whereIn('estado', ['pendiente', 'completado']))
            ->whereIn('numero_orden', $ordenes->pluck('numero_orden'))
            ->pluck('numero_orden');

        if ($ocupadas->isNotEmpty()) {
            return response()->json([
                'message' => 'Alguna orden ya fue asignada a otra entrega: ' . $ocupadas->implode(', '),
            ], 422);
        }

        // Auto-asignar secuencia
        $ultimaSecuencia = Entrega::where('ruta_id', $data['ruta_id'])
            ->max('orden_secuencia') ?? 0;

        $entrega = DB::transaction(function () use ($data, $ordenes, $ultimaSecuencia) {
            $entrega = Entrega::create([
                'motorizado_id'    => $data['motorizado_id'],
                'ruta_id'          => $data['ruta_id'],
                'cliente_nombre'   => $data['cliente_nombre'],
                'cliente_telefono' => $data['cliente_telefono'] ?? null,
                'referencia'       => \Illuminate\Support\Str::limit($ordenes->pluck('numero_orden')->implode(', '), 250, ''),
                'direccion'        => $data['direccion'],
                'latitud'          => $data['latitud'] ?? null,
                'longitud'         => $data['longitud'] ?? null,
                'orden_secuencia'  => $ultimaSecuencia + 1,
                'sede'             => $data['sede'],
                'notas'            => $data['notas'] ?? null,
            ]);

            $entrega->ordenes()->createMany($ordenes->map(fn ($o) => [
                'numero_orden' => $o['numero_orden'],
                'cliente'      => $o['cliente'] ?? null,
                'ruc'          => $o['ruc'] ?? null,
                'fecha_orden'  => $o['fecha_orden'] ?? null,
            ])->all());

            return $entrega;
        });

        ActivityLogService::log(
            Auth::id(), 'create_entrega', 'Entrega', $entrega->id,
            "Creó entrega para {$entrega->cliente_nombre} (sede: {$entrega->sede}) con {$ordenes->count()} orden(es)"
        );

        return response()->json(['success' => true, 'entrega' => $entrega->load('ordenes')]);
    }

    public function showEntrega(int $id)
    {
        $this->checkPermiso();

        $entrega = Entrega::with(['motorizado', 'ruta', 'ordenes'])->findOrFail($id);

        $data = $entrega->toArray();
        $data['ordenes'] = $entrega->ordenes->map(fn ($o) => [
            'numero_orden' => $o->numero_orden,
            'cliente'      => $o->cliente,
            'ruc'          => $o->ruc,
            'fecha_orden'  => $o->fecha_orden?->toDateString(),
        ]);

        return response()->json(['entrega' => $data]);
    }

    /**
     * Busca órdenes reales de ordenes_historico para asignarlas a una entrega,
     * limitando a las que físicamente están en sede (ubicacion_orden = "En
     * sede") y excluyendo anuladas y las que ya tienen una entrega
     * pendiente/completada asignada, para que la asignación sea trazable.
     */
    public function buscarOrdenes(Request $request)
    {
        $this->checkPermiso();

        $data = $request->validate([
            'sede' => 'required|string',
            'q'    => 'required|string|min:2',
        ]);

        $term = $data['q'];

        $ocupadas = EntregaOrden::whereHas('entrega', fn ($q) => $q->whereIn('estado', ['pendiente', 'completado']))
            ->pluck('numero_orden');

        $ordenes = DB::table('ordenes_historico')
            ->where('descripcion_sede', $data['sede'])
            ->where(function ($q) use ($term) {
                $q->where('numero_orden', 'like', "%{$term}%")
                  ->orWhere('cliente', 'like', "%{$term}%");
            })
            ->where(function ($q) {
                $q->whereNull('estado_orden')->orWhere('estado_orden', '!=', 'Anulado');
            })
            ->whereRaw('UPPER(TRIM(ubicacion_orden)) = ?', ['EN SEDE'])
            ->whereNotIn('numero_orden', $ocupadas)
            ->orderByRaw("CASE WHEN numero_orden LIKE ? THEN 0 ELSE 1 END", ["{$term}%"])
            ->limit(15)
            ->get(['numero_orden', 'cliente', 'ruc', 'fecha_orden']);

        return response()->json($ordenes->map(fn ($o) => [
            'numero_orden' => $o->numero_orden,
            'cliente'      => $o->cliente,
            'ruc'          => $o->ruc,
            'fecha_orden'  => $o->fecha_orden,
        ]));
    }
}
