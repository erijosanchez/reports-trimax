<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entrega;
use App\Models\GpsRuta;

class EntregaController extends Controller
{
    // ── GET /api/entregas/hoy ─────────────────────────────
    public function hoy(Request $request)
    {
        $motorizado = $request->user();

        $ruta = GpsRuta::where('motorizado_id', $motorizado->id)
            ->whereDate('fecha', today())
            ->whereIn('status', ['pendiente', 'activa'])
            ->with('entregas')
            ->first();

        if (!$ruta) {
            return response()->json(['entregas' => [], 'ruta' => null]);
        }

        return response()->json([
            'ruta' => [
                'id'          => $ruta->id,
                'status'      => $ruta->status,
                'distance_km' => round($ruta->distance_km, 2),
                'started_at'  => $ruta->started_at?->format('H:i'),
            ],
            'entregas' => $ruta->entregas->map(fn($e) => [
                'id'               => $e->id,
                'cliente_nombre'   => $e->cliente_nombre,
                'cliente_telefono' => $e->cliente_telefono,
                'referencia'       => $e->referencia,
                'direccion'        => $e->direccion,
                'latitud'          => $e->latitud,
                'longitud'         => $e->longitud,
                'orden_secuencia'  => $e->orden_secuencia,
                'estado'           => $e->estado,
                'notas'            => $e->notas,
            ]),
        ]);
    }

    // ── POST /api/entregas/{id}/completar ─────────────────
    public function completar(Request $request, int $id)
    {
        $motorizado = $request->user();

        $data = $request->validate([
            'latitud'  => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'notas'    => 'nullable|string|max:300',
        ]);

        $entrega = Entrega::where('id', $id)
            ->where('motorizado_id', $motorizado->id)
            ->firstOrFail();

        $entrega->update([
            'estado'           => 'completado',
            'entrega_latitud'  => $data['latitud'] ?? null,
            'entrega_longitud' => $data['longitud'] ?? null,
            'entregado_en'     => now()->setTimezone('America/Lima'),
            'notas'            => $data['notas'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'entrega' => $entrega->fresh()->only([
                'id', 'estado', 'entregado_en', 'notas'
            ]),
        ]);
    }

    // ── POST /api/entregas/{id}/fallar ────────────────────
    public function fallar(Request $request, int $id)
    {
        $motorizado = $request->user();

        $data = $request->validate([
            'notas' => 'nullable|string|max:300',
        ]);

        $entrega = Entrega::where('id', $id)
            ->where('motorizado_id', $motorizado->id)
            ->firstOrFail();

        $entrega->update([
            'estado' => 'fallido',
            'notas'  => $data['notas'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }
}
