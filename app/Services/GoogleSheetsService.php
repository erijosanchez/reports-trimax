<?php

namespace App\Services;

use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    protected $spreadsheetId;
    protected $sheetName;

    public function __construct()
    {
        $this->spreadsheetId = env('GOOGLE_SHEETS_SPREADSHEET_ID', '12SKxU3bvZ4psujz0DVfx-el1jKgbBX7S8pw_ngX7Ezg');
        $this->sheetName = env('GOOGLE_SHEETS_SHEET_NAME', 'Orden');
    }

    /**
     * Obtener todas las órdenes del Google Sheet
     */
    public function getOrdenes($useCache = true)
    {
        try {
            $cacheKey = 'google_sheets_ordenes';
            $cacheDuration = 300; // 5 minutos

            if ($useCache && Cache::has($cacheKey)) {
                Log::info('Obteniendo órdenes desde caché');
                return Cache::get($cacheKey);
            }

            Log::info('Conectando a Google Sheets', [
                'spreadsheet_id' => $this->spreadsheetId,
                'sheet_name' => $this->sheetName
            ]);

            // Obtener datos del sheet
            $rows = Sheets::spreadsheet($this->spreadsheetId)
                ->sheet($this->sheetName)
                ->get();

            if (empty($rows) || $rows->count() === 0) {
                Log::warning('No se encontraron datos en el Google Sheet');
                return [
                    'success' => false,
                    'message' => 'No se encontraron datos en el Google Sheet',
                    'data' => []
                ];
            }

            Log::info('Datos obtenidos correctamente', ['total_rows' => $rows->count()]);

            // Primera fila son los headers
            $headers = $rows->first();
            $dataRows = $rows->slice(1);

            Log::info('Headers encontrados', ['headers' => $headers->toArray()]);

            // Convertir a array asociativo
            $ordenes = [];
            foreach ($dataRows as $row) {
                $orden = [];
                foreach ($headers as $index => $header) {
                    $orden[$header] = $row[$index] ?? null;
                }
                // Solo agregar si tiene número de orden
                if (!empty($orden['numero_orden'])) {
                    $ordenes[] = $orden;
                }
            }

            $result = [
                'success' => true,
                'data' => $ordenes,
                'total' => count($ordenes)
            ];

            // Guardar en caché
            Cache::put($cacheKey, $result, $cacheDuration);

            Log::info('Órdenes procesadas correctamente', ['total_ordenes' => count($ordenes)]);

            return $result;
        } catch (\Google\Service\Exception $e) {
            $errorMessage = 'Error de Google Sheets API: ' . $e->getMessage();
            Log::error($errorMessage, [
                'code' => $e->getCode(),
                'errors' => $e->getErrors()
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'data' => []
            ];
        } catch (\Exception $e) {
            $errorMessage = 'Error al obtener datos de Google Sheets: ' . $e->getMessage();
            Log::error($errorMessage, [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'data' => []
            ];
        }
    }

    /**
     * Obtener sedes únicas
     */
    public function getSedes()
    {
        try {
            $result = $this->getOrdenes();

            if (!$result['success']) {
                return ['success' => false, 'data' => []];
            }

            $sedes = collect($result['data'])
                ->pluck('descripcion_sede')
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->toArray();

            Log::info('Sedes obtenidas', ['total_sedes' => count($sedes)]);

            return [
                'success' => true,
                'data' => $sedes
            ];
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes: ' . $e->getMessage());
            return ['success' => false, 'data' => []];
        }
    }

    /**
     * Calcular estadísticas
     */
    public function getEstadisticas()
    {
        try {
            $result = $this->getOrdenes();

            if (!$result['success']) {
                return [
                    'total' => 0,
                    'disponibles_facturar' => 0,
                    'en_transito' => 0,
                    'en_sede' => 0
                ];
            }

            $ordenes = collect($result['data']);

            $stats = [
                'total' => $ordenes->count(),
                'disponibles_facturar' => $ordenes->filter(function ($orden) {
                    $ubicacion = strtoupper($orden['ubicacion_orden'] ?? '');
                    return !str_contains($ubicacion, 'FACTURADO') &&
                        !str_contains($ubicacion, 'ENTREGADO');
                })->count(),
                'en_transito' => $ordenes->filter(function ($orden) {
                    $ubicacion = strtoupper($orden['ubicacion_orden'] ?? '');
                    return str_contains($ubicacion, 'TRANSITO');
                })->count(),
                'en_sede' => $ordenes->filter(function ($orden) {
                    $ubicacion = strtoupper($orden['ubicacion_orden'] ?? '');
                    return str_contains($ubicacion, 'SEDE') &&
                        !str_contains($ubicacion, 'TRANSITO');
                })->count()
            ];

            Log::info('Estadísticas calculadas', $stats);

            return $stats;
        } catch (\Exception $e) {
            Log::error('Error al calcular estadísticas: ' . $e->getMessage());
            return [
                'total' => 0,
                'disponibles_facturar' => 0,
                'en_transito' => 0,
                'en_sede' => 0
            ];
        }
    }

    /**
     * Limpiar caché
     */
    public function clearCache()
    {
        Cache::forget('google_sheets_ordenes');
        Log::info('Caché de Google Sheets limpiado');
    }

    /**
     * Verificar conexión
     */
    public function testConnection()
    {
        try {
            $rows = Sheets::spreadsheet($this->spreadsheetId)
                ->sheet($this->sheetName)
                ->range('A1:C2')
                ->get();

            if ($rows->count() > 0) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa a Google Sheets',
                    'sample_data' => $rows->toArray()
                ];
            }

            return [
                'success' => false,
                'message' => 'No se pudo obtener datos del sheet'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }
}
