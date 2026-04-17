<?php

namespace App\Http\Controllers;

use App\Models\Motorizado;
use App\Models\RutaMotorizado;
use App\Models\ParadaRuta;
use App\Models\UbicacionMotorizado;
use App\Services\TraccarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrackingController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // MAPA EN VIVO
    // ─────────────────────────────────────────────────────────

    public function mapa()
    {
        abort_unless(Auth::user()->puedeVerTracking(), 403);

        $motorizados = Motorizado::where('activo', true)
            ->with('rutaActiva', 'ultimaUbicacion')
            ->get();

        return view('tracking.mapa', compact('motorizados'));
    }

    /**
     * API: posiciones actuales de todos los motorizados en ruta.
     */
    public function ubicacionesEnVivo(TraccarService $traccar)
    {
        abort_unless(Auth::user()->puedeVerTracking(), 403);

        $motorizados = Motorizado::where('activo', true)
            ->with(['rutaActiva.paradas', 'ultimaUbicacion'])
            ->get();

        $data = $motorizados->map(function ($m) use ($traccar) {
            $pos = null;

            // Intentar obtener posición desde Traccar si tiene device ID
            if ($m->traccar_device_id && $traccar->isEnabled()) {
                $pos = $traccar->getPosicionActual($m->traccar_device_id);
            }

            // Fallback: última posición guardada en BD
            if (!$pos && $m->ultimaUbicacion) {
                $u = $m->ultimaUbicacion;
                $pos = [
                    'lat'   => (float) $u->latitud,
                    'lng'   => (float) $u->longitud,
                    'speed' => (float) ($u->velocidad ?? 0),
                    'time'  => $u->registrado_at?->toISOString(),
                ];
            }

            $ruta = $m->rutaActiva;

            return [
                'id'           => $m->id,
                'nombre'       => $m->nombre,
                'sede'         => $m->sede,
                'en_ruta'      => $ruta !== null,
                'ruta_id'      => $ruta?->id,
                'ruta_nombre'  => $ruta?->nombre,
                'posicion'     => $pos,
                'paradas_total'       => $ruta?->paradas->count() ?? 0,
                'paradas_completadas' => $ruta?->paradasCompletadas() ?? 0,
            ];
        });

        return response()->json($data);
    }

    // ─────────────────────────────────────────────────────────
    // MOTORIZADOS — CRUD
    // ─────────────────────────────────────────────────────────

    public function motorizados()
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);

        $motorizados = Motorizado::orderBy('sede')->orderBy('nombre')->get();
        return view('tracking.motorizados.index', compact('motorizados'));
    }

    public function crearMotorizado()
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);
        return view('tracking.motorizados.create');
    }

    public function guardarMotorizado(Request $request)
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);

        $data = $request->validate([
            'nombre'            => 'required|string|max:100',
            'telefono'          => 'nullable|string|max:20',
            'sede'              => 'required|string|max:100',
            'traccar_device_id' => 'nullable|string|max:100',
            'activo'            => 'boolean',
        ]);

        Motorizado::create($data);

        return redirect()->route('tracking.motorizados')->with('success', 'Motorizado registrado correctamente.');
    }

    public function editarMotorizado(Motorizado $motorizado)
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);
        return view('tracking.motorizados.create', compact('motorizado'));
    }

    public function actualizarMotorizado(Request $request, Motorizado $motorizado)
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);

        $data = $request->validate([
            'nombre'            => 'required|string|max:100',
            'telefono'          => 'nullable|string|max:20',
            'sede'              => 'required|string|max:100',
            'traccar_device_id' => 'nullable|string|max:100',
            'activo'            => 'boolean',
        ]);

        $data['activo'] = $request->boolean('activo');
        $motorizado->update($data);

        return redirect()->route('tracking.motorizados')->with('success', 'Motorizado actualizado.');
    }

    public function eliminarMotorizado(Motorizado $motorizado)
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);
        $motorizado->delete();
        return redirect()->route('tracking.motorizados')->with('success', 'Motorizado eliminado.');
    }

    // ─────────────────────────────────────────────────────────
    // RUTAS — CRUD
    // ─────────────────────────────────────────────────────────

    public function rutas(Request $request)
    {
        abort_unless(Auth::user()->puedeVerTracking(), 403);

        $query = RutaMotorizado::with('motorizado', 'creadoPor')
            ->orderByDesc('created_at');

        if ($request->filled('sede')) {
            $query->where('sede', $request->sede);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('motorizado_id')) {
            $query->where('motorizado_id', $request->motorizado_id);
        }

        $rutas       = $query->paginate(20)->withQueryString();
        $motorizados = Motorizado::where('activo', true)->orderBy('nombre')->get();

        return view('tracking.rutas.index', compact('rutas', 'motorizados'));
    }

    public function crearRuta()
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);

        $motorizados = Motorizado::where('activo', true)->orderBy('sede')->orderBy('nombre')->get();
        return view('tracking.rutas.create', compact('motorizados'));
    }

    public function guardarRuta(Request $request)
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);

        $request->validate([
            'motorizado_id' => 'required|exists:motorizados,id',
            'nombre'        => 'nullable|string|max:150',
            'notas'         => 'nullable|string',
            'paradas'       => 'required|array|min:1',
            'paradas.*.cliente'   => 'nullable|string|max:150',
            'paradas.*.direccion' => 'required|string|max:255',
            'paradas.*.referencia' => 'nullable|string|max:255',
            'paradas.*.latitud'   => 'nullable|numeric',
            'paradas.*.longitud'  => 'nullable|numeric',
        ]);

        $motorizado = Motorizado::findOrFail($request->motorizado_id);

        $ruta = RutaMotorizado::create([
            'motorizado_id' => $motorizado->id,
            'creado_por'    => Auth::id(),
            'nombre'        => $request->nombre ?: 'Ruta ' . now('America/Lima')->format('d/m/Y'),
            'sede'          => $motorizado->sede,
            'estado'        => 'programada',
            'notas'         => $request->notas,
        ]);

        foreach ($request->paradas as $index => $parada) {
            ParadaRuta::create([
                'ruta_id'    => $ruta->id,
                'orden'      => $index + 1,
                'cliente'    => $parada['cliente'] ?? null,
                'direccion'  => $parada['direccion'],
                'referencia' => $parada['referencia'] ?? null,
                'latitud'    => $parada['latitud'] ?? null,
                'longitud'   => $parada['longitud'] ?? null,
                'estado'     => 'pendiente',
            ]);
        }

        return redirect()->route('tracking.rutas.show', $ruta)->with('success', 'Ruta creada correctamente.');
    }

    public function verRuta(RutaMotorizado $ruta, TraccarService $traccar)
    {
        abort_unless(Auth::user()->puedeVerTracking(), 403);

        $ruta->load('motorizado', 'paradas', 'creadoPor');

        // Obtener coordenadas del recorrido (polyline)
        $polyline = [];

        if ($ruta->motorizado->traccar_device_id && $traccar->isEnabled() && $ruta->inicio_at) {
            $hasta = $ruta->fin_at ?? now();
            $polyline = $traccar->getHistorial(
                $ruta->motorizado->traccar_device_id,
                $ruta->inicio_at->toISOString(),
                $hasta->toISOString()
            );
        }

        // Si no hay Traccar, usar ubicaciones guardadas en BD
        if (empty($polyline)) {
            $polyline = $ruta->ubicaciones()
                ->select('latitud', 'longitud', 'velocidad', 'registrado_at')
                ->get()
                ->map(fn($u) => [
                    'lat'   => (float) $u->latitud,
                    'lng'   => (float) $u->longitud,
                    'speed' => (float) ($u->velocidad ?? 0),
                    'time'  => $u->registrado_at?->toISOString(),
                ])->values()->all();
        }

        return view('tracking.rutas.show', compact('ruta', 'polyline'));
    }

    public function iniciarRuta(RutaMotorizado $ruta)
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);
        abort_if($ruta->estado !== 'programada', 422);

        $ruta->update(['estado' => 'en_ruta', 'inicio_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function completarRuta(RutaMotorizado $ruta)
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);
        abort_if($ruta->estado !== 'en_ruta', 422);

        $ruta->update(['estado' => 'completada', 'fin_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function cancelarRuta(RutaMotorizado $ruta)
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);

        $ruta->update(['estado' => 'cancelada', 'fin_at' => now()]);

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────
    // VISTA MÓVIL DEL MOTORIZADO
    // ─────────────────────────────────────────────────────────

    public function vistaMotorizado(Motorizado $motorizado)
    {
        $ruta = $motorizado->rutaActiva()->with('paradas')->first();
        return view('tracking.motorizado.ruta', compact('motorizado', 'ruta'));
    }

    public function marcarParada(Request $request, ParadaRuta $parada)
    {
        $request->validate([
            'estado'       => 'required|in:entregado,fallido',
            'motivo_fallo' => 'nullable|string|max:255',
            'notas'        => 'nullable|string|max:500',
        ]);

        $parada->update([
            'estado'        => $request->estado,
            'motivo_fallo'  => $request->estado === 'fallido' ? $request->motivo_fallo : null,
            'notas'         => $request->notas,
            'completado_at' => now(),
        ]);

        return response()->json(['ok' => true, 'parada' => $parada->fresh()]);
    }

    // ─────────────────────────────────────────────────────────
    // API: RECIBIR UBICACIÓN DESDE NAVEGADOR
    // ─────────────────────────────────────────────────────────

    public function recibirUbicacion(Request $request, Motorizado $motorizado)
    {
        $request->validate([
            'lat'       => 'required|numeric',
            'lng'       => 'required|numeric',
            'speed'     => 'nullable|numeric',
            'accuracy'  => 'nullable|numeric',
            'ruta_id'   => 'nullable|exists:rutas_motorizado,id',
        ]);

        UbicacionMotorizado::create([
            'motorizado_id'    => $motorizado->id,
            'ruta_id'          => $request->ruta_id,
            'latitud'          => $request->lat,
            'longitud'         => $request->lng,
            'velocidad'        => $request->speed,
            'precision_metros' => $request->accuracy,
            'fuente'           => 'browser',
            'registrado_at'    => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // ─────────────────────────────────────────────────────────
    // API PÚBLICA — GPSLogger (sin sesión, autenticación por token)
    // ─────────────────────────────────────────────────────────

    public function gpsPublico(Request $request)
    {
        $token = $request->input('token');
        if (!$token) return response()->json(['error' => 'token requerido'], 401);

        $motorizado = Motorizado::where('api_token', $token)->where('activo', true)->first();
        if (!$motorizado) return response()->json(['error' => 'token inválido'], 401);

        $lat = $request->input('lat') ?? $request->input('latitude');
        $lng = $request->input('lng') ?? $request->input('longitude');

        if (!$lat || !$lng) return response()->json(['error' => 'coordenadas requeridas'], 422);

        // Buscar ruta activa automáticamente
        $rutaActiva = $motorizado->rutaActiva()->first();

        UbicacionMotorizado::create([
            'motorizado_id'    => $motorizado->id,
            'ruta_id'          => $rutaActiva?->id,
            'latitud'          => $lat,
            'longitud'         => $lng,
            'velocidad'        => $request->input('speed') ?? $request->input('spd'),
            'precision_metros' => $request->input('accuracy') ?? $request->input('acc'),
            'fuente'           => 'gpslogger',
            'registrado_at'    => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function generarToken(Motorizado $motorizado)
    {
        abort_unless(Auth::user()->puedeGestionarTracking(), 403);

        $motorizado->update(['api_token' => bin2hex(random_bytes(32))]);

        return response()->json(['token' => $motorizado->api_token]);
    }
}
