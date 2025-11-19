<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationApiController extends Controller
{
    /**
     * Guardar ubicación GPS del navegador
     */
    public function storeGpsLocation(Request $request)
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|numeric|min:0',
            ]);

            $user = auth()->user();

            Log::info('📱 GPS recibido', [
                'user_id' => $user->id,
                'lat' => $request->latitude,
                'lon' => $request->longitude,
                'accuracy' => $request->accuracy,
            ]);

            // Guardar ubicación GPS
            $location = LocationService::saveGpsLocation(
                $user->id,
                $request->latitude,
                $request->longitude,
                $request->accuracy
            );

            return response()->json([
                'success' => true,
                'message' => 'Ubicación GPS registrada',
                'data' => [
                    'id' => $location->id,
                    'city' => $location->city,
                    'address' => $location->formatted_address,
                    'accuracy' => $location->accuracy,
                    'district' => $location->district,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error guardando GPS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar ubicación',
            ], 500);
        }
    }
}
