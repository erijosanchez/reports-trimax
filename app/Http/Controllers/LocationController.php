<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Guardar ubicación GPS desde navegador
     */
    public function storeGpsLocation(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'accuracy' => 'required|numeric|min:0',
            ]);

            $user = auth()->user();

            // Buscar sesión activa
            $session = $user->sessions()
                ->where('session_id', session()->getId())
                ->where('is_online', true)
                ->first();

            if (!$session) {
                \Log::warning('Sesión no encontrada para GPS', [
                    'user_id' => $user->id,
                    'session_id' => session()->getId()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Sesión no encontrada',
                ], 404);
            }

            // Guardar ubicación GPS
            $location = LocationService::saveGpsLocation(
                $user->id,
                $session->id,
                $request->latitude,
                $request->longitude,
                $request->accuracy
            );

            \Log::info('✅ Ubicación GPS guardada', [
                'user_id' => $user->id,
                'city' => $location->city,
                'accuracy' => $location->accuracy,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ubicación GPS registrada',
                'data' => [
                    'formatted_address' => $location->formatted_address,
                    'city' => $location->city,
                    'accuracy' => $location->accuracy,
                    'street' => $location->street_name,
                    'district' => $location->district,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error guardando GPS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar ubicación: ' . $e->getMessage(),
            ], 500);
        }
    }
}