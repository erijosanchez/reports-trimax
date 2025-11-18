<?php

namespace App\Services;

use App\Models\UserLocation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class LocationService
{
    public static function trackLocation(
        int $userId,
        ?int $sessionId = null,
        ?string $ip = null
    ): ?UserLocation {
        $ip = $ip ?? request()->ip();

        // No trackear IPs locales
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return self::createLocalLocation($userId, $sessionId, $ip);
        }

        // Obtener datos de geolocalización
        $geoData = self::getLocationFromIp($ip);

        return UserLocation::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'latitude' => $geoData['latitude'],
            'longitude' => $geoData['longitude'],
            'city' => $geoData['city'],
            'region' => $geoData['region'],
            'country' => $geoData['country'],
            'country_code' => $geoData['country_code'],
            'is_vpn' => $geoData['is_vpn'],
            'created_at' => now(),
        ]);
    }

    /**
     * Obtener ubicación desde IP usando ipapi.co (GRATIS)
     * Límite: 1000 requests/día gratis
     */
    public static function getLocationFromIp(string $ip): array
    {
        // Cachear resultado por 24 horas
        $cacheKey = "geo_location_{$ip}";

        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            try {
                // API GRATIS: ipapi.co
                $response = Http::timeout(5)
                    ->get("https://ipapi.co/{$ip}/json/");

                if ($response->successful()) {
                    $data = $response->json();

                    return [
                        'latitude' => $data['latitude'] ?? null,
                        'longitude' => $data['longitude'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['region'] ?? null,
                        'country' => $data['country_name'] ?? null,
                        'country_code' => $data['country_code'] ?? null,
                        'is_vpn' => isset($data['is_proxy']) ? (bool) $data['is_proxy'] : false,
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning('Error obteniendo geolocalización', [
                    'ip' => $ip,
                    'error' => $e->getMessage(),
                ]);
            }

            // Fallback si falla
            return [
                'latitude' => null,
                'longitude' => null,
                'city' => null,
                'region' => null,
                'country' => null,
                'country_code' => null,
                'is_vpn' => false,
            ];
        });
    }

    /**
     * Crear ubicación para IPs locales
     */
    protected static function createLocalLocation(int $userId, ?int $sessionId, string $ip): UserLocation
    {
        return UserLocation::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'city' => 'Local',
            'region' => 'Desarrollo',
            'country' => 'Peru',
            'country_code' => 'PE',
            'created_at' => now(),
        ]);
    }

    /**
     * Obtener ubicaciones del usuario
     */
    public static function getUserLocations(int $userId, int $limit = 10)
    {
        return UserLocation::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtener última ubicación conocida
     */
    public static function getLastLocation(int $userId): ?UserLocation
    {
        return UserLocation::where('user_id', $userId)
            ->latest('created_at')
            ->first();
    }

    /**
     * Obtener ubicaciones únicas (ciudades visitadas)
     */
    public static function getUniqueCities(int $userId)
    {
        return UserLocation::where('user_id', $userId)
            ->whereNotNull('city')
            ->select('city', 'region', 'country')
            ->groupBy('city', 'region', 'country')
            ->get();
    }

    /**
     * Obtener todos los usuarios con su ubicación actual
     */
    public static function getAllUsersLocations()
    {
        return \App\Models\User::with(['locations' => function ($query) {
            $query->latest('created_at')->limit(1);
        }])
            ->whereHas('locations')
            ->get();
    }

    /**
     * Verificar si usuario está en una ciudad específica
     */
    public static function isUserInCity(int $userId, string $city): bool
    {
        $lastLocation = self::getLastLocation($userId);

        return $lastLocation &&
            strtolower($lastLocation->city) === strtolower($city);
    }

    /**
     * Guardar ubicación GPS automática
     */
    public static function saveGpsLocation(
        int $userId,
        int $sessionId,
        float $latitude,
        float $longitude,
        float $accuracy
    ): UserLocation {
        \Log::info('GPS automático', [
            'user_id' => $userId,
            'lat' => $latitude,
            'lon' => $longitude,
            'accuracy' => $accuracy,
        ]);

        $address = \App\Services\GeocodingService::reverseGeocode($latitude, $longitude);

        return UserLocation::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'street_name' => $address['street_name'],
            'street_number' => $address['street_number'],
            'district' => $address['district'],
            'city' => $address['city'],
            'region' => $address['region'],
            'country' => $address['country'],
            'country_code' => $address['country_code'],
            'postal_code' => $address['postal_code'],
            'formatted_address' => $address['formatted_address'],
            'ip_address' => get_real_ip(),
            'location_type' => 'gps',
            'accuracy' => $accuracy,
            'is_vpn' => false,
            'created_at' => now(),
        ]);
    }
}
