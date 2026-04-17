<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TraccarService
{
    private string $baseUrl;
    private string $user;
    private string $password;
    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.traccar.url', ''), '/');
        $this->user     = config('services.traccar.user', '');
        $this->password = config('services.traccar.password', '');
        $this->enabled  = !empty($this->baseUrl);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get latest position for a device.
     * Returns ['lat' => float, 'lng' => float, 'speed' => float, 'time' => string] or null.
     */
    public function getPosicionActual(string $deviceId): ?array
    {
        if (!$this->enabled) return null;

        try {
            $response = Http::withBasicAuth($this->user, $this->password)
                ->timeout(5)
                ->get("{$this->baseUrl}/api/positions", ['deviceId' => $deviceId]);

            if (!$response->ok()) return null;

            $positions = $response->json();
            if (empty($positions)) return null;

            $last = end($positions);
            return [
                'lat'   => $last['latitude'],
                'lng'   => $last['longitude'],
                'speed' => $last['speed'] ?? 0,
                'time'  => $last['fixTime'] ?? now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::warning("Traccar getPosicionActual failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get position history for a device between two timestamps.
     * Returns array of ['lat', 'lng', 'speed', 'time'].
     */
    public function getHistorial(string $deviceId, string $desde, string $hasta): array
    {
        if (!$this->enabled) return [];

        try {
            $response = Http::withBasicAuth($this->user, $this->password)
                ->timeout(10)
                ->get("{$this->baseUrl}/api/positions", [
                    'deviceId' => $deviceId,
                    'from'     => $desde,
                    'to'       => $hasta,
                ]);

            if (!$response->ok()) return [];

            return collect($response->json())->map(fn($p) => [
                'lat'   => $p['latitude'],
                'lng'   => $p['longitude'],
                'speed' => $p['speed'] ?? 0,
                'time'  => $p['fixTime'] ?? null,
            ])->values()->all();
        } catch (\Exception $e) {
            Log::warning("Traccar getHistorial failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Register a new device in Traccar.
     */
    public function registrarDispositivo(string $nombre, string $uniqueId): ?int
    {
        if (!$this->enabled) return null;

        try {
            $response = Http::withBasicAuth($this->user, $this->password)
                ->timeout(5)
                ->post("{$this->baseUrl}/api/devices", [
                    'name'     => $nombre,
                    'uniqueId' => $uniqueId,
                ]);

            if (!$response->ok()) return null;
            return $response->json('id');
        } catch (\Exception $e) {
            Log::warning("Traccar registrarDispositivo failed: " . $e->getMessage());
            return null;
        }
    }
}
