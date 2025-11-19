<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Convertir coordenadas GPS a dirección usando OpenStreetMap
     */
    public static function reverseGeocode(float $latitude, float $longitude): array
    {
        $cacheKey = "geocode_{$latitude}_{$longitude}";

        return Cache::remember($cacheKey, 86400, function () use ($latitude, $longitude) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'ReportsTrimax/1.0 (Laravel App)',
                    ])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'format' => 'json',
                        'addressdetails' => 1,
                        'zoom' => 18,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $address = $data['address'] ?? [];

                    Log::info('✅ Geocoding exitoso', [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'city' => $address['city'] ?? 'desconocida',
                    ]);

                    return [
                        'street_name' => $address['road'] ?? $address['street'] ?? null,
                        'street_number' => $address['house_number'] ?? null,
                        'district' => $address['suburb'] ?? $address['neighbourhood'] ?? $address['district'] ?? null,
                        'city' => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
                        'region' => $address['state'] ?? $address['province'] ?? null,
                        'country' => $address['country'] ?? null,
                        'country_code' => strtoupper($address['country_code'] ?? ''),
                        'postal_code' => $address['postcode'] ?? null,
                        'formatted_address' => $data['display_name'] ?? null,
                    ];
                }

                Log::warning('⚠️ Geocoding falló - respuesta no exitosa', [
                    'status' => $response->status(),
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Error en geocoding', [
                    'error' => $e->getMessage(),
                    'lat' => $latitude,
                    'lon' => $longitude,
                ]);
            }

            // Valores por defecto si falla
            return [
                'street_name' => null,
                'street_number' => null,
                'district' => null,
                'city' => null,
                'region' => null,
                'country' => null,
                'country_code' => null,
                'postal_code' => null,
                'formatted_address' => "Lat: {$latitude}, Lon: {$longitude}",
            ];
        });
    }
}
