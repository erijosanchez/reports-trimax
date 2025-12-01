<?php

namespace App\Services;

use App\Models\UserLocation;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * Guardar ubicación GPS con geocoding
     */
    public static function saveGpsLocation(
        int $userId,
        float $latitude,
        float $longitude,
        ?float $accuracy = null
    ): UserLocation {

        // Obtener dirección usando geocoding
        $address = GeocodingService::reverseGeocode($latitude, $longitude);

        // Crear registro en base de datos
        return UserLocation::create([
            'user_id' => $userId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'accuracy' => $accuracy,
            'street_name' => $address['street_name'],
            'street_number' => $address['street_number'],
            'district' => $address['district'],
            'city' => $address['city'],
            'region' => $address['region'],
            'country' => $address['country'],
            'country_code' => $address['country_code'],
            'postal_code' => $address['postal_code'],
            'formatted_address' => $address['formatted_address'],
            'location_type' => 'gps',
            'created_at' => now(),
        ]);
    }

    /**
     * Obtener última ubicación de un usuario
     */
    public static function getLastLocation(int $userId): ?UserLocation
    {
        return UserLocation::where('user_id', $userId)
            ->where('location_type', 'gps')
            ->latest('created_at')
            ->first();
    }

    /**
     * Obtener ciudades únicas visitadas por un usuario
     */
    public static function getUniqueCities(int $userId)
    {
        return UserLocation::where('user_id', $userId)
            ->whereNotNull('city')
            ->distinct('city')
            ->orderBy('city')
            ->pluck('city');
    }

    /**
     * Obtener estadísticas de ubicación de un usuario
     */
    public static function getUserLocationStats(int $userId): array
    {
        $locations = UserLocation::where('user_id', $userId)->get();

        return [
            'total_locations' => $locations->count(),
            'cities_visited' => $locations->pluck('city')->unique()->count(),
            'countries_visited' => $locations->pluck('country')->unique()->count(),
            'avg_accuracy' => $locations->avg('accuracy'),
            'last_location' => $locations->sortByDesc('created_at')->first(),
        ];
    }
}
