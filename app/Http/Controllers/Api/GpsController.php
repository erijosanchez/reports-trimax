<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GpsRuta;
use App\Models\GpsPosicion;
use App\Models\Motorizado;

class GpsController extends Controller
{
    // ── POST /api/gps/iniciar ─────────────────────────────
    public function iniciar(Request $request)
    {
        $motorizado = $request->user();

        // Verificar que no haya ruta activa
        $rutaActiva = GpsRuta::where('motorizado_id', $motorizado->id)
            ->where('status', 'activa')
            ->first();

        if ($rutaActiva) {
            return response()->json([
                'message' => 'Ya tienes una ruta activa',
                'ruta_id' => $rutaActiva->id,
            ], 422);
        }

        $ruta = GpsRuta::create([
            'motorizado_id' => $motorizado->id,
            'fecha'         => today(),
            'started_at'    => now()->setTimezone('America/Lima'),
            'status'        => 'activa',
            'distance_km'   => 0,
        ]);

        return response()->json([
            'success' => true,
            'ruta_id' => $ruta->id,
            'started_at' => $ruta->started_at->format('H:i:s'),
        ]);
    }

    // ── POST /api/gps/posicion ────────────────────────────
    public function guardarPosicion(Request $request)
    {
        $motorizado = $request->user();

        $data = $request->validate([
            'ruta_id'      => 'required|exists:gps_rutas,id',
            'latitud'      => 'required|numeric|between:-90,90',
            'longitud'     => 'required|numeric|between:-180,180',
            'velocidad'    => 'nullable|numeric|min:0',
            'precicion'    => 'nullable|numeric|min:0',
            'capturado_en' => 'nullable|date',
        ]);

        // Verificar que la ruta pertenece al motorizado
        $ruta = GpsRuta::where('id', $data['ruta_id'])
            ->where('motorizado_id', $motorizado->id)
            ->where('status', 'activa')
            ->firstOrFail();

        // Filtrar puntos con baja precisión (más de 50 metros de error)
        if (isset($data['precicion']) && $data['precicion'] > 50) {
            return response()->json(['success' => true, 'filtrado' => true]);
        }

        // Obtener última posición para filtrar puntos muy cercanos
        $ultima = GpsPosicion::where('ruta_id', $ruta->id)
            ->latest('capturado_en')
            ->first();

        if ($ultima) {
            $distancia = $this->haversineKm(
                $ultima->latitud,
                $ultima->longitud,
                $data['latitud'],
                $data['longitud']
            );

            // Ignorar si se movió menos de 10 metros
            if ($distancia < 0.01) {
                return response()->json(['success' => true, 'filtrado' => true]);
            }

            // Acumular km en la ruta
            $ruta->increment('distance_km', $distancia);
        }

        GpsPosicion::create([
            'motorizado_id' => $motorizado->id,
            'ruta_id'       => $ruta->id,
            'latitud'       => $data['latitud'],
            'longitud'      => $data['longitud'],
            'velocidad'     => $data['velocidad'] ?? 0,
            'precicion'     => $data['precicion'] ?? null,
            'capturado_en'  => $data['capturado_en'] ?? now()->setTimezone('America/Lima'),
        ]);

        return response()->json([
            'success'     => true,
            'distance_km' => round($ruta->fresh()->distance_km, 2),
        ]);
    }

    // ── POST /api/gps/finalizar ───────────────────────────
    public function finalizar(Request $request)
    {
        $motorizado = $request->user();

        $data = $request->validate([
            'ruta_id' => 'required|exists:gps_rutas,id',
        ]);

        $ruta = GpsRuta::where('id', $data['ruta_id'])
            ->where('motorizado_id', $motorizado->id)
            ->where('status', 'activa')
            ->firstOrFail();

        // Construir polyline desde posiciones guardadas
        $polyline = GpsPosicion::where('ruta_id', $ruta->id)
            ->orderBy('capturado_en')
            ->get(['latitud', 'longitud'])
            ->map(fn($p) => [$p->latitud, $p->longitud])
            ->values()
            ->toArray();

        $ruta->update([
            'ended_at'  => now()->setTimezone('America/Lima'),
            'polyline'  => $polyline,
            'status'    => 'completada',
        ]);

        return response()->json([
            'success'     => true,
            'distance_km' => round($ruta->distance_km, 2),
            'puntos'      => count($polyline),
            'duracion'    => $ruta->duracion,
        ]);
    }

    // ── GET /api/gps/ruta-activa ──────────────────────────
    public function rutaActiva(Request $request)
    {
        $motorizado = $request->user();

        $ruta = GpsRuta::where('motorizado_id', $motorizado->id)
            ->whereDate('fecha', today())
            ->where('status', 'activa')
            ->first();

        if (!$ruta) {
            return response()->json(['ruta' => null]);
        }

        return response()->json([
            'ruta' => [
                'id'          => $ruta->id,
                'started_at'  => $ruta->started_at->format('H:i:s'),
                'distance_km' => round($ruta->distance_km, 2),
                'status'      => $ruta->status,
            ],
        ]);
    }

    // ── GET /api/admin/mapa-vivo ──────────────────────────
    public function mapaVivo()
    {
        $motorizados = Motorizado::where('estado', 'activo')
            ->with(['ultimaPosicion', 'rutaActivaHoy'])
            ->get();

        return response()->json($motorizados->map(function ($m) {
            $pos = $m->ultimaPosicion;
            return [
                'id'          => $m->id,
                'nombre'      => $m->nombre,
                'sede'        => $m->sede,
                'latitud'     => $pos?->latitud,
                'longitud'    => $pos?->longitud,
                'velocidad'   => $pos ? round($pos->velocidad, 1) : 0,
                'actualizado' => $pos?->capturado_en?->format('H:i:s'),
                'en_ruta'     => $m->rutaActivaHoy !== null,
                'distance_km' => round($m->rutaActivaHoy?->distance_km ?? 0, 2),
            ];
        })->filter(fn($m) => $m['latitud'])->values());
    }

    // ── GET /api/admin/resumen-diario ─────────────────────
    public function resumenDiario(Request $request)
    {
        $fecha = $request->get('fecha', today()->toDateString());

        $rutas = GpsRuta::with(['motorizado', 'entregas'])
            ->whereDate('fecha', $fecha)
            ->get();

        return response()->json($rutas->map(fn($r) => [
            'motorizado'  => $r->motorizado->nombre,
            'sede'        => $r->motorizado->sede,
            'fecha'       => $r->fecha->format('d/m/Y'),
            'started_at'  => $r->started_at?->format('H:i'),
            'ended_at'    => $r->ended_at?->format('H:i'),
            'distance_km' => round($r->distance_km, 2),
            'duracion'    => $r->duracion,
            'status'      => $r->status,
            'completadas' => $r->entregas->where('estado', 'completado')->count(),
            'fallidas'    => $r->entregas->where('estado', 'fallido')->count(),
            'total'       => $r->entregas->count(),
        ]));
    }

    // ── GET /api/admin/historial-km ───────────────────────
    public function historialKm(Request $request)
    {
        $motorizadoId = $request->get('motorizado_id');
        $desde        = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta        = $request->get('hasta', today()->toDateString());

        $query = GpsRuta::with('motorizado')
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('status', 'completada');

        if ($motorizadoId) $query->where('motorizado_id', $motorizadoId);

        return response()->json($query->get()->map(fn($r) => [
            'motorizado'  => $r->motorizado->nombre,
            'sede'        => $r->motorizado->sede,
            'fecha'       => $r->fecha->format('d/m/Y'),
            'distance_km' => round($r->distance_km, 2),
            'duracion'    => $r->duracion,
            'started_at'  => $r->started_at?->format('H:i'),
            'ended_at'    => $r->ended_at?->format('H:i'),
        ]));
    }

    public function historialMotorizado(Request $request)
    {
        $motorizado = $request->user();

        $rutas = GpsRuta::with('entregas')
            ->where('motorizado_id', $motorizado->id)
            ->where('status', 'completada')
            ->orderByDesc('fecha')
            ->limit(30)
            ->get();

        return response()->json($rutas->map(fn($r) => [
            'fecha'       => $r->fecha->format('d/m/Y'),
            'started_at'  => $r->started_at?->setTimezone('America/Lima')->format('H:i'),
            'ended_at'    => $r->ended_at?->setTimezone('America/Lima')->format('H:i'),
            'distance_km' => round($r->distance_km, 2),
            'duracion'    => $r->duracion,
            'completadas' => $r->entregas->where('estado','completado')->count(),
            'fallidas'    => $r->entregas->where('estado','fallido')->count(),
            'total'       => $r->entregas->count(),
        ]));
    }

    // ── Haversine ─────────────────────────────────────────
    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
