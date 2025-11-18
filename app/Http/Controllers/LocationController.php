<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LocationService;

class LocationController extends Controller
{
    /**
     * API: Guardar ubicación GPS automática
     */
    public function storeGpsLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0',
        ]);

        $user = auth()->user();

        $session = $user->sessions()
            ->where('session_id', session()->getId())
            ->where('is_online', true)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión no encontrada',
            ], 404);
        }

        try {
            $location = LocationService::saveGpsLocation(
                $user->id,
                $session->id,
                $request->latitude,
                $request->longitude,
                $request->accuracy
            );

            return response()->json([
                'success' => true,
                'message' => 'Ubicación GPS registrada',
                'data' => [
                    'formatted_address' => $location->formatted_address,
                    'city' => $location->city,
                    'accuracy' => $location->accuracy,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error guardando GPS', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar ubicación',
            ], 500);
        }
    }
}
