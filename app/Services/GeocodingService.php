<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeocodingService
{
    public static function reverseGeocode(float $latitude, float $longitude): array
    {
        $cacheKey = "geocode_{$latitude}_{$longitude}";
        
        return Cache::remember($cacheKey, 86400, function () use ($latitude, $longitude) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'ReportsTrimax/1.0'])
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

                    return [
                        'street_name' => $address['road'] ?? $address['street'] ?? null,
                        'street_number' => $address['house_number'] ?? null,
                        'district' => $address['suburb'] ?? $address['neighbourhood'] ?? null,
                        'city' => $address['city'] ?? $address['town'] ?? null,
                        'region' => $address['state'] ?? null,
                        'country' => $address['country'] ?? null,
                        'country_code' => $address['country_code'] ?? null,
                        'postal_code' => $address['postcode'] ?? null,
                        'formatted_address' => $data['display_name'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Error geocoding', ['error' => $e->getMessage()]);
            }

            return [
                'street_name' => null,
                'street_number' => null,
                'district' => null,
                'city' => null,
                'region' => null,
                'country' => null,
                'country_code' => null,
                'postal_code' => null,
                'formatted_address' => null,
            ];
        });
    }
}
