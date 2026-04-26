<?php

namespace App\Services;

use App\Models\RutaGps;

class RutaGpsService
{
    public function __construct(protected TraccarService $traccar) {}

    public function finalizar(RutaGps $ruta): array
    {
        $endedAt  = now();
        $deviceId = $ruta->motorizado->traccar_device_id;
        $polyline = [];
        $totalKm  = 0.0;

        if ($deviceId) {
            $from   = $ruta->started_at->utc()->format('Y-m-d\TH:i:s.') . '000Z';
            $to     = $endedAt->utc()->format('Y-m-d\TH:i:s.') . '000Z';
            $puntos = $this->traccar->getRutaGps($deviceId, $from, $to);

            foreach ($puntos as $p) {
                $lat = $p['latitude']  ?? null;
                $lng = $p['longitude'] ?? null;
                if ($lat !== null && $lng !== null) {
                    $polyline[] = [(float) $lat, (float) $lng];
                }
            }

            for ($i = 1; $i < count($polyline); $i++) {
                $totalKm += self::haversineKm(
                    $polyline[$i - 1][0], $polyline[$i - 1][1],
                    $polyline[$i][0],     $polyline[$i][1]
                );
            }
        }

        $ruta->update([
            'ended_at'    => $endedAt,
            'distance_km' => round($totalKm, 3),
            'polyline'    => $polyline,
            'status'      => RutaGps::STATUS_COMPLETED,
        ]);

        return [
            'distance_km' => round($totalKm, 2),
            'puntos'      => count($polyline),
        ];
    }

    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
              + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
