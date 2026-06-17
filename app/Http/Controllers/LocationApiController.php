<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationApiController extends Controller
{
    /**
     * Registra en la bitácora el estado del permiso de ubicación del navegador.
     * Enfoque "suave": no bloquea la navegación, solo deja constancia de quién
     * tiene la ubicación desactivada para poder darle seguimiento.
     */
    public function reportPermission(Request $request)
    {
        $data = $request->validate([
            'status' => 'required|in:granted,denied,prompt,unavailable',
        ]);

        $user = auth()->user();

        $map = [
            'granted'     => ['location_permission_granted', 'Permitió el acceso a su ubicación en el navegador'],
            'denied'      => ['location_permission_denied', 'Tiene DESACTIVADO el permiso de ubicación del navegador'],
            'unavailable' => ['location_permission_unavailable', 'Su navegador/dispositivo no soporta geolocalización'],
            'prompt'      => ['location_permission_prompt', 'Aún no decide el permiso de ubicación'],
        ];

        [$action, $description] = $map[$data['status']];

        ActivityLogService::log($user->id, $action, 'User', $user->id, $description);

        return response()->json(['ok' => true]);
    }

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
