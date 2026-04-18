<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\Motorizado;
use App\Models\OrdenTracking;
use App\Models\RutaParada;
use App\Models\RutaTracking;
use App\Models\TrackingPosition;
use App\Services\TraccarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MotorizadoController extends Controller
{
    public function __construct(protected TraccarService $traccar) {}

    // ─── VISTAS ───────────────────────────────────────────────

    public function mapa()
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $sedes       = Motorizado::SEDES;
        $motorizados = Motorizado::where('estado', 'activo')->orderBy('sede')->get();

        return view('tracking.mapa', compact('motorizados', 'sedes'));
    }

    public function motorizados()
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $motorizados      = Motorizado::withTrashed()->orderBy('sede')->get();
        $sedes            = Motorizado::SEDES;
        $dispositivosTraccar = $this->traccar->getDispositivos();

        return view('tracking.motorizados', compact('motorizados', 'sedes', 'dispositivosTraccar'));
    }

    // ─── ÓRDENES ──────────────────────────────────────────────

    public function ordenes(Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $query = OrdenTracking::query();

        if ($request->filled('sede'))   $query->where('sede',   $request->sede);
        if ($request->filled('estado')) $query->where('estado', $request->estado);
        if ($request->filled('buscar')) {
            $query->where(fn($q) => $q
                ->where('cliente_nombre', 'like', "%{$request->buscar}%")
                ->orWhere('referencia',   'like', "%{$request->buscar}%")
            );
        }

        $ordenes = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $sedes   = Motorizado::SEDES;

        return view('tracking.ordenes', compact('ordenes', 'sedes'));
    }

    public function storeOrden(Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $data = $request->validate([
            'cliente_nombre'   => 'required|string|max:255',
            'cliente_telefono' => 'nullable|string|max:50',
            'referencia'       => 'nullable|string|max:100',
            'direccion'        => 'required|string',
            'latitud'          => 'nullable|numeric|between:-90,90',
            'longitud'         => 'nullable|numeric|between:-180,180',
            'sede'             => 'required|string',
            'notas'            => 'nullable|string',
        ]);

        $orden = OrdenTracking::create($data);

        return response()->json(['success' => true, 'orden' => $orden]);
    }

    public function updateOrden(Request $request, int $id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $orden = OrdenTracking::findOrFail($id);

        $data = $request->validate([
            'cliente_nombre'   => 'required|string|max:255',
            'cliente_telefono' => 'nullable|string|max:50',
            'referencia'       => 'nullable|string|max:100',
            'direccion'        => 'required|string',
            'latitud'          => 'nullable|numeric|between:-90,90',
            'longitud'         => 'nullable|numeric|between:-180,180',
            'sede'             => 'required|string',
            'estado'           => 'required|in:pendiente,en_ruta,entregado,fallido',
            'notas'            => 'nullable|string',
        ]);

        $orden->update($data);

        return response()->json(['success' => true, 'orden' => $orden->fresh()]);
    }

    public function destroyOrden(int $id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        OrdenTracking::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    // ─── RESUMEN DIARIO ───────────────────────────────────────

    public function resumenDiario(Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $fecha = $request->get('fecha', today()->toDateString());

        $rutas = RutaTracking::with(['motorizado', 'paradas.orden'])
            ->whereDate('fecha', $fecha)
            ->get();

        $todasParadas = $rutas->flatMap(fn($r) => $r->paradas);

        $kpi = [
            'total_rutas'   => $rutas->count(),
            'total_paradas' => $todasParadas->count(),
            'completadas'   => $todasParadas->where('estado', 'completado')->count(),
            'fallidas'      => $todasParadas->where('estado', 'fallido')->count(),
            'pendientes'    => $todasParadas->whereIn('estado', ['pendiente', 'en_camino'])->count(),
        ];

        // Agrupar por sede
        $porSede = [];
        foreach ($rutas as $r) {
            $sede = $r->motorizado->sede;
            if (!isset($porSede[$sede])) {
                $porSede[$sede] = ['total' => 0, 'comp' => 0, 'fall' => 0, 'pend' => 0];
            }
            foreach ($r->paradas as $p) {
                $porSede[$sede]['total']++;
                if ($p->estado === 'completado') $porSede[$sede]['comp']++;
                elseif ($p->estado === 'fallido') $porSede[$sede]['fall']++;
                else $porSede[$sede]['pend']++;
            }
        }
        ksort($porSede);

        $fallidas = $todasParadas->where('estado', 'fallido');

        return view('tracking.resumen-diario', compact('rutas', 'kpi', 'porSede', 'fallidas', 'fecha'));
    }

    public function historialVista()
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $motorizados = Motorizado::where('estado', 'activo')->orderBy('sede')->orderBy('nombre')->get();

        return view('tracking.historial', compact('motorizados'));
    }

    public function rutaDetalle($id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $ruta = RutaTracking::with(['motorizado', 'paradas.orden'])->findOrFail($id);

        $paradasJson = $ruta->paradas->map(function ($p) {
            return [
                'secuencia' => $p->orden_secuencia,
                'estado'    => $p->estado,
                'lat'       => $p->orden->latitud,
                'lng'       => $p->orden->longitud,
                'cliente'   => $p->orden->cliente_nombre,
                'direccion' => $p->orden->direccion,
            ];
        })->values();

        return view('tracking.ruta-detalle', compact('ruta', 'paradasJson'));
    }

    public function rutas()
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $rutas = RutaTracking::with('motorizado')
            ->orderByDesc('fecha')
            ->paginate(30);

        $motorizados = Motorizado::where('estado', 'activo')->orderBy('nombre')->get();

        return view('tracking.rutas', compact('rutas', 'motorizados'));
    }

    // ─── CRUD MOTORIZADOS ─────────────────────────────────────

    public function store(Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $data = $request->validate([
            'nombre'            => 'required|string|max:255',
            'sede'              => 'required|string',
            'telefono'          => 'nullable|string|max:50',
            'traccar_device_id' => 'nullable|integer|unique:motorizados,traccar_device_id',
            'estado'            => 'required|in:activo,inactivo',
        ]);

        $motorizado = Motorizado::create($data);

        return response()->json(['success' => true, 'motorizado' => $motorizado]);
    }

    public function update(Request $request, int $id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $motorizado = Motorizado::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'nombre'            => 'required|string|max:255',
            'sede'              => 'required|string',
            'telefono'          => 'nullable|string|max:50',
            'traccar_device_id' => "nullable|integer|unique:motorizados,traccar_device_id,{$id}",
            'estado'            => 'required|in:activo,inactivo',
        ]);

        $motorizado->update($data);

        return response()->json(['success' => true, 'motorizado' => $motorizado->fresh()]);
    }

    public function destroy(int $id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        Motorizado::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }

    // ─── API JSON ─────────────────────────────────────────────

    /** GET /tracking/api/ordenes-disponibles?sede=ICA — órdenes pendientes sin ruta hoy */
    public function ordenesDisponibles(Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $query = OrdenTracking::whereIn('estado', ['pendiente', 'fallido']);

        if ($request->filled('sede')) {
            $query->where('sede', $request->sede);
        }

        // Excluir órdenes ya asignadas a una ruta de hoy
        $asignadasHoy = RutaParada::whereHas('ruta', fn($q) =>
            $q->whereDate('fecha', today())
              ->whereIn('estado', ['pendiente', 'en_ruta'])
        )->pluck('orden_id');

        $ordenes = $query->whereNotIn('id', $asignadasHoy)
            ->orderBy('id')
            ->get(['id', 'cliente_nombre', 'cliente_telefono', 'referencia', 'direccion', 'sede', 'latitud', 'longitud']);

        return response()->json($ordenes);
    }

    /** GET /tracking/api/ubicaciones — posiciones actuales desde Traccar + DB */
    public function ubicaciones()
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $motorizados = Motorizado::with('ultimaPosicion')
            ->where('estado', 'activo')
            ->get();

        // Enriquecer con datos live de Traccar
        $posicionesTraccar = collect($this->traccar->getPosicionesActuales())
            ->keyBy('deviceId');

        $result = $motorizados->map(function ($m) use ($posicionesTraccar) {
            $live = $m->traccar_device_id
                ? $posicionesTraccar->get($m->traccar_device_id)
                : null;

            $lat = $live['latitude']  ?? $m->ultimaPosicion?->latitud;
            $lng = $live['longitude'] ?? $m->ultimaPosicion?->longitud;
            $vel = $live['speed']     ?? $m->ultimaPosicion?->velocidad ?? 0;
            $upd = $live ? now()->toIso8601String() : $m->ultimaPosicion?->registrado_en;

            return [
                'id'             => $m->id,
                'nombre'         => $m->nombre,
                'sede'           => $m->sede,
                'telefono'       => $m->telefono,
                'latitud'        => $lat,
                'longitud'       => $lng,
                'velocidad'      => round($vel * 1.852, 1), // knots → km/h
                'ultima_actualizacion' => $upd,
            ];
        })->filter(fn($m) => $m['latitud'] && $m['longitud'])->values();

        return response()->json($result);
    }

    /** GET /tracking/api/motorizados/{id}/historial?fecha=YYYY-MM-DD */
    public function historial(int $id, Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $motorizado = Motorizado::findOrFail($id);
        $fecha      = $request->get('fecha', today()->toDateString());

        $posiciones = [];

        if ($motorizado->traccar_device_id) {
            $raw = $this->traccar->getHistorial($motorizado->traccar_device_id, $fecha);
            $posiciones = collect($raw)->map(fn($p) => [
                'lat' => $p['latitude'],
                'lng' => $p['longitude'],
                'vel' => round(($p['speed'] ?? 0) * 1.852, 1),
                'ts'  => $p['fixTime'] ?? null,
            ])->values()->toArray();
        }

        if (empty($posiciones)) {
            $posiciones = TrackingPosition::where('motorizado_id', $id)
                ->whereDate('registrado_en', $fecha)
                ->orderBy('registrado_en')
                ->get()
                ->map(fn($p) => [
                    'lat' => $p->latitud,
                    'lng' => $p->longitud,
                    'vel' => round($p->velocidad * 1.852, 1),
                    'ts'  => $p->registrado_en->toIso8601String(),
                ])->values()->toArray();
        }

        return response()->json([
            'motorizado' => $motorizado->only('id', 'nombre', 'sede'),
            'fecha'      => $fecha,
            'puntos'     => $posiciones,
        ]);
    }

    /** GET /tracking/api/rutas/{id} — detalle de ruta con paradas */
    public function rutaJson(int $id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $ruta = RutaTracking::with(['motorizado', 'paradas.orden'])->findOrFail($id);

        $paradas = $ruta->paradas->map(fn($p) => [
            'secuencia'     => $p->orden_secuencia,
            'estado'        => $p->estado,
            'cliente'       => $p->orden->cliente_nombre,
            'direccion'     => $p->orden->direccion,
            'latitud'       => $p->orden->latitud,
            'longitud'      => $p->orden->longitud,
            'hora_llegada'  => $p->hora_llegada?->format('H:i'),
            'hora_salida'   => $p->hora_salida?->format('H:i'),
            'tiempo_parada' => $p->tiempo_en_parada,
        ]);

        return response()->json([
            'ruta'       => [
                'id'         => $ruta->id,
                'fecha'      => $ruta->fecha->format('d/m/Y'),
                'estado'     => $ruta->estado,
                'motorizado' => $ruta->motorizado->only('id', 'nombre', 'sede'),
            ],
            'paradas'    => $paradas,
        ]);
    }

    /** GET /tracking/api/ordenes/{id}/tracking */
    public function trackingOrden(int $id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $orden = OrdenTracking::findOrFail($id);

        $parada = RutaParada::with(['ruta.motorizado'])
            ->where('orden_id', $id)
            ->whereHas('ruta', fn($q) => $q->where('fecha', today()))
            ->first();

        if (!$parada) {
            return response()->json(['message' => 'Sin ruta activa hoy'], 404);
        }

        $motorizado = $parada->ruta->motorizado;
        $ultimaPos  = $motorizado->ultimaPosicion;

        $totalParadas = $parada->ruta->paradas()->count();
        $completadas  = $parada->ruta->paradas()->where('estado', 'completado')->count();

        return response()->json([
            'orden'       => $orden->only('id', 'cliente_nombre', 'direccion', 'estado'),
            'motorizado'  => [
                'nombre'   => $motorizado->nombre,
                'latitud'  => $ultimaPos?->latitud,
                'longitud' => $ultimaPos?->longitud,
                'velocidad' => $ultimaPos ? round($ultimaPos->velocidad * 1.852, 1) : 0,
            ],
            'posicion_ruta' => "{$completadas} de {$totalParadas} entregas",
            'parada_actual' => $parada->orden_secuencia,
        ]);
    }

    /** POST /tracking/rutas/{id}/token — genera o regenera token de acceso */
    public function generarToken(int $id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $ruta = RutaTracking::with('motorizado')->findOrFail($id);

        $token = hash('sha256', $ruta->id . $ruta->motorizado_id . $ruta->fecha . now()->timestamp . config('app.key'));
        $token = substr($token, 0, 32);

        $ruta->update(['token_acceso' => $token]);

        $url = url("/entrega/{$token}");

        $whatsapp = 'https://wa.me/' . preg_replace('/\D/', '', $ruta->motorizado->telefono ?? '')
            . '?text=' . urlencode(
                "Hola {$ruta->motorizado->nombre} 👋\n"
                . "Aquí está tu ruta de hoy ({$ruta->fecha->format('d/m/Y')}):\n\n"
                . $url . "\n\n"
                . "Marca cada entrega al completarla. ✅"
            );

        return response()->json([
            'success'   => true,
            'url'       => $url,
            'whatsapp'  => $whatsapp,
            'token'     => $token,
        ]);
    }

    /** POST /tracking/api/sincronizar — pull manual desde Traccar */
    public function sincronizar()
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $guardados = $this->traccar->sincronizarPosiciones();

        return response()->json(['success' => true, 'guardados' => $guardados]);
    }

    // ─── WEBHOOK TRACCAR ──────────────────────────────────────

    /** POST /api/traccar/webhook */
    public function webhook(Request $request)
    {
        try {
            $positions = $request->input('positions', []);

            if (empty($positions)) {
                return response()->json(['ok' => true]);
            }

            $motorizados = Motorizado::whereNotNull('traccar_device_id')
                ->get()->keyBy('traccar_device_id');

            foreach ($positions as $pos) {
                $deviceId = $pos['deviceId'] ?? null;
                if (!$deviceId || !$motorizados->has($deviceId)) continue;

                TrackingPosition::create([
                    'motorizado_id'       => $motorizados[$deviceId]->id,
                    'latitud'             => $pos['latitude'] ?? 0,
                    'longitud'            => $pos['longitude'] ?? 0,
                    'velocidad'           => $pos['speed'] ?? 0,
                    'rumbo'               => $pos['course'] ?? null,
                    'altitud'             => $pos['altitude'] ?? null,
                    'traccar_position_id' => $pos['id'] ?? null,
                    'registrado_en'       => now(),
                ]);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Traccar webhook error: ' . $e->getMessage());
            return response()->json(['ok' => false], 500);
        }
    }

    // ─── GESTIÓN DE RUTAS ─────────────────────────────────────

    public function storeRuta(Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $data = $request->validate([
            'motorizado_id' => 'required|exists:motorizados,id',
            'fecha'         => 'required|date',
            'notas'         => 'nullable|string',
            'paradas'       => 'required|array|min:1',
            'paradas.*.orden_id' => 'required|exists:ordenes_tracking,id',
        ]);

        $ruta = RutaTracking::create([
            'motorizado_id' => $data['motorizado_id'],
            'fecha'         => $data['fecha'],
            'notas'         => $data['notas'] ?? null,
            'estado'        => RutaTracking::ESTADO_PENDIENTE,
        ]);

        foreach ($data['paradas'] as $idx => $parada) {
            RutaParada::create([
                'ruta_id'         => $ruta->id,
                'orden_id'        => $parada['orden_id'],
                'orden_secuencia' => $idx + 1,
                'estado'          => RutaParada::ESTADO_PENDIENTE,
            ]);
        }

        return response()->json(['success' => true, 'ruta_id' => $ruta->id]);
    }

    public function actualizarParada(Request $request, int $paradaId)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $parada = RutaParada::findOrFail($paradaId);

        $data = $request->validate([
            'estado'       => 'required|in:pendiente,en_camino,completado,fallido',
            'hora_llegada' => 'nullable|date_format:H:i',
            'hora_salida'  => 'nullable|date_format:H:i',
            'notas'        => 'nullable|string',
        ]);

        if (isset($data['hora_llegada'])) {
            $data['hora_llegada'] = today()->format('Y-m-d') . ' ' . $data['hora_llegada'] . ':00';
        }
        if (isset($data['hora_salida'])) {
            $data['hora_salida'] = today()->format('Y-m-d') . ' ' . $data['hora_salida'] . ':00';
        }

        $parada->update($data);

        return response()->json(['success' => true, 'parada' => $parada->fresh()]);
    }
}
