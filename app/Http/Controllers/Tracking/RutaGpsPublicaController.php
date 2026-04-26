<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\Motorizado;
use App\Models\OrdenTracking;
use App\Models\RutaGps;
use App\Models\RutaParada;
use App\Models\RutaTracking;
use App\Services\RutaGpsService;
use Illuminate\Http\Request;

class RutaGpsPublicaController extends Controller
{
    public function __construct(protected RutaGpsService $service) {}

    // ─── VISTA PRINCIPAL ─────────────────────────────────────

    public function show(string $token)
    {
        $motorizado = Motorizado::where('token_gps', $token)->firstOrFail();

        $rutaGps = RutaGps::where('motorizado_id', $motorizado->id)
            ->where('status', RutaGps::STATUS_ACTIVE)
            ->first();

        // Ruta de entregas de hoy (cualquier estado para que vean aunque esté completada)
        $rutaEntrega = RutaTracking::with(['paradas.orden'])
            ->where('motorizado_id', $motorizado->id)
            ->whereDate('fecha', today())
            ->orderByDesc('id')
            ->first();

        $paradasNav = $rutaEntrega
            ? $rutaEntrega->paradas->map(fn($p) => [
                'id'        => $p->id,
                'lat'       => $p->orden->latitud  ? (float) $p->orden->latitud  : null,
                'lng'       => $p->orden->longitud ? (float) $p->orden->longitud : null,
                'direccion' => $p->orden->direccion,
                'cliente'   => $p->orden->cliente_nombre,
                'estado'    => $p->estado,
                'secuencia' => $p->orden_secuencia,
            ])->values()
            : collect();

        return view('tracking.rutas-gps-motorizado', compact(
            'motorizado', 'rutaGps', 'rutaEntrega', 'paradasNav', 'token'
        ));
    }

    // ─── INICIAR GPS ──────────────────────────────────────────

    public function iniciar(string $token)
    {
        $motorizado = Motorizado::where('token_gps', $token)->firstOrFail();

        $yaActiva = RutaGps::where('motorizado_id', $motorizado->id)
            ->where('status', RutaGps::STATUS_ACTIVE)
            ->exists();

        if ($yaActiva) {
            return response()->json(['success' => false, 'message' => 'Ya tienes una ruta GPS activa.'], 422);
        }

        $rutaGps = RutaGps::create([
            'motorizado_id' => $motorizado->id,
            'started_at'    => now(),
            'status'        => RutaGps::STATUS_ACTIVE,
        ]);

        // Marcar la ruta de entregas como en_ruta si estaba pendiente
        RutaTracking::where('motorizado_id', $motorizado->id)
            ->whereDate('fecha', today())
            ->where('estado', RutaTracking::ESTADO_PENDIENTE)
            ->update(['estado' => RutaTracking::ESTADO_EN_RUTA]);

        return response()->json(['success' => true, 'ruta_id' => $rutaGps->id]);
    }

    // ─── FINALIZAR GPS ────────────────────────────────────────

    public function finalizar(string $token, int $id)
    {
        $motorizado = Motorizado::where('token_gps', $token)->firstOrFail();
        $rutaGps    = RutaGps::with('motorizado')
            ->where('id', $id)
            ->where('motorizado_id', $motorizado->id)
            ->firstOrFail();

        if ($rutaGps->status === RutaGps::STATUS_COMPLETED) {
            return response()->json(['success' => false, 'message' => 'La ruta ya está completada.'], 422);
        }

        $resultado = $this->service->finalizar($rutaGps);

        return response()->json(['success' => true] + $resultado);
    }

    // ─── MARCAR PARADA ────────────────────────────────────────

    public function marcarParada(string $token, int $paradaId, Request $request)
    {
        $motorizado = Motorizado::where('token_gps', $token)->firstOrFail();

        $parada = RutaParada::with(['ruta'])
            ->where('id', $paradaId)
            ->whereHas('ruta', fn($q) => $q->where('motorizado_id', $motorizado->id))
            ->firstOrFail();

        $data = $request->validate([
            'estado' => 'required|in:completado,fallido',
            'notas'  => 'nullable|string|max:300',
        ]);

        $ruta = $parada->ruta;
        $now  = now();

        if ($data['estado'] === 'completado') {
            $parada->update([
                'estado'       => 'completado',
                'hora_llegada' => $parada->hora_llegada ?? $now,
                'hora_salida'  => $now,
                'notas'        => $data['notas'] ?? $parada->notas,
            ]);
            OrdenTracking::where('id', $parada->orden_id)->update(['estado' => 'entregado']);
        } else {
            $parada->update([
                'estado' => 'fallido',
                'notas'  => $data['notas'] ?? $parada->notas,
            ]);
            OrdenTracking::where('id', $parada->orden_id)->update(['estado' => 'fallido']);
        }

        // Cerrar la ruta de entregas automáticamente si ya no hay pendientes
        $pendientes = $ruta->paradas()->whereIn('estado', ['pendiente', 'en_camino'])->count();
        if ($pendientes === 0) {
            $ruta->update(['estado' => RutaTracking::ESTADO_COMPLETADO]);
        } elseif ($ruta->estado === RutaTracking::ESTADO_PENDIENTE) {
            $ruta->update(['estado' => RutaTracking::ESTADO_EN_RUTA]);
        }

        return response()->json([
            'success'     => true,
            'estado'      => $parada->fresh()->estado,
            'ruta_estado' => $ruta->fresh()->estado,
        ]);
    }
}
