<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\RutaParada;
use App\Models\RutaTracking;
use Illuminate\Http\Request;

class EntregaPublicaController extends Controller
{
    /** GET /entrega/{token} — vista móvil del motorizado */
    public function show(string $token)
    {
        $ruta = RutaTracking::where('token_acceso', $token)
            ->with(['motorizado', 'paradas.orden'])
            ->firstOrFail();

        return view('tracking.entrega-publica', compact('ruta', 'token'));
    }

    /** POST /entrega/{token}/parada/{parada} — marcar estado */
    public function marcarParada(Request $request, string $token, int $paradaId)
    {
        $ruta = RutaTracking::where('token_acceso', $token)->firstOrFail();

        $parada = RutaParada::where('id', $paradaId)
            ->where('ruta_id', $ruta->id)
            ->firstOrFail();

        $data = $request->validate([
            'estado' => 'required|in:completado,fallido',
            'notas'  => 'nullable|string|max:300',
        ]);

        $now = now();

        if ($data['estado'] === 'completado') {
            $parada->update([
                'estado'       => 'completado',
                'hora_llegada' => $parada->hora_llegada ?? $now,
                'hora_salida'  => $now,
                'notas'        => $data['notas'] ?? $parada->notas,
            ]);
        } else {
            $parada->update([
                'estado' => 'fallido',
                'notas'  => $data['notas'] ?? $parada->notas,
            ]);
        }

        // Si todas las paradas tienen estado final → cerrar la ruta
        $pendientes = $ruta->paradas()
            ->whereIn('estado', ['pendiente', 'en_camino'])
            ->count();

        if ($pendientes === 0) {
            $ruta->update(['estado' => 'completado']);
        } elseif ($ruta->estado === 'pendiente') {
            $ruta->update(['estado' => 'en_ruta']);
        }

        return response()->json([
            'success'  => true,
            'estado'   => $parada->fresh()->estado,
            'ruta_estado' => $ruta->fresh()->estado,
        ]);
    }
}
