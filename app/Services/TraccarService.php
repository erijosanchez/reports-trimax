<?php

namespace App\Services;

use App\Models\Motorizado;
use App\Models\TrackingPosition;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TraccarService
{
    protected string $baseUrl;
    protected string $email;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('traccar.url', 'http://localhost:8082'), '/');
        $this->email   = config('traccar.email', 'admin');
        $this->password = config('traccar.password', 'admin');
    }

    protected function client()
    {
        return Http::withBasicAuth($this->email, $this->password)
            ->baseUrl($this->baseUrl)
            ->timeout(10)
            ->acceptJson();
    }

    public function getPosicionesActuales(): array
    {
        try {
            $response = $this->client()->get('/api/positions');
            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error('Traccar getPosicionesActuales: ' . $e->getMessage());
            return [];
        }
    }

    public function getHistorial(int $deviceId, string $fecha): array
    {
        try {
            $response = $this->client()->get('/api/positions', [
                'deviceId' => $deviceId,
                'from'     => $fecha . 'T00:00:00.000Z',
                'to'       => $fecha . 'T23:59:59.000Z',
            ]);
            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error('Traccar getHistorial: ' . $e->getMessage());
            return [];
        }
    }

    public function getDispositivos(): array
    {
        try {
            $response = $this->client()->get('/api/devices');
            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error('Traccar getDispositivos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Sincroniza posiciones actuales de Traccar → tracking_positions local.
     * Mapea traccar_device_id → motorizado.
     */
    public function sincronizarPosiciones(): int
    {
        $posiciones = $this->getPosicionesActuales();

        if (empty($posiciones)) {
            return 0;
        }

        $motorizados = Motorizado::whereNotNull('traccar_device_id')
            ->where('estado', 'activo')
            ->get()
            ->keyBy('traccar_device_id');

        $guardados = 0;

        foreach ($posiciones as $pos) {
            $deviceId = $pos['deviceId'] ?? null;

            if (!$deviceId || !$motorizados->has($deviceId)) {
                continue;
            }

            $motorizado = $motorizados[$deviceId];

            TrackingPosition::create([
                'motorizado_id'       => $motorizado->id,
                'latitud'             => $pos['latitude'] ?? 0,
                'longitud'            => $pos['longitude'] ?? 0,
                'velocidad'           => $pos['speed'] ?? 0,
                'rumbo'               => $pos['course'] ?? null,
                'altitud'             => $pos['altitude'] ?? null,
                'traccar_position_id' => $pos['id'] ?? null,
                'registrado_en'       => now(),
            ]);

            $guardados++;
        }

        return $guardados;
    }
}
