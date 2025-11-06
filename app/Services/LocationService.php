<?php

namespace App\Services;

use App\Models\UserLocation;

class LocationService
{
    public static function trackLocation(
        int $userId,
        ?int $sessionId = null,
        ?string $ip = null
    ): ?UserLocation {
        $ip = $ip ?? request()->ip();

        // Aquí puedes integrar un servicio de GeoIP como MaxMind
        // Por ahora creamos el registro básico

        return UserLocation::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }

    public static function getLocationFromIp(string $ip): array
    {
        // Aquí integrarías MaxMind GeoIP2 o similar
        // Por ahora retornamos datos de ejemplo

        return [
            'city' => null,
            'region' => null,
            'country' => null,
            'country_code' => null,
            'latitude' => null,
            'longitude' => null,
            'is_vpn' => false,
        ];
    }

    public static function getUserLocations(int $userId, int $limit = 10)
    {
        return UserLocation::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
