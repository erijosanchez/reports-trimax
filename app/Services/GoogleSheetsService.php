<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Illuminate\Support\Facades\Cache;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = config('google.spreadsheet_id');
        $this->initializeClient();
    }

    private function initializeClient()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('google.application_name'));
        $this->client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $this->client->setAuthConfig(config('google.service_account_file'));
        $this->service = new Sheets($this->client);
    }

    /**
     * Obtener datos de una hoja específica
     */
    public function getSheetData($sheetName = 'Historico', $range = null)
    {
        try {
            // Si no se especifica rango, obtener todo
            $fullRange = $range ? "{$sheetName}!{$range}" : $sheetName;

            \Log::info('🔍 Intentando leer hoja:', ['range' => $fullRange]);

            $response = $this->service->spreadsheets_values->get(
                $this->spreadsheetId,
                $fullRange
            );

            $values = $response->getValues();
            \Log::info('✅ Datos obtenidos correctamente', ['filas' => count($values)]);

            return $values;
        } catch (\Exception $e) {
            \Log::error('❌ Error al obtener datos de Google Sheets: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos con caché (10 minutos para datasets grandes)
     */
    public function getSheetDataCached($sheetName = 'Historico', $range = null)
    {
        $cacheKey = "google_sheets_{$sheetName}_" . md5($range);

        return Cache::remember($cacheKey, 600, function () use ($sheetName, $range) {
            return $this->getSheetData($sheetName, $range);
        });
    }

    /**
     * Convertir datos de sheet a array asociativo
     * 🔥 SÚPER OPTIMIZADO PARA 80K FILAS
     */
    public function parseSheetData($data)
{
    if (empty($data)) {
        \Log::warning('⚠️ No hay datos para parsear');
        return [];
    }

    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    \Log::info('📊 Iniciando parseo de ' . count($data) . ' filas');

    // 🔥 VERIFICAR si la primera fila es una fila de "actualizado" y saltarla
    if (!empty($data[0]) && is_array($data[0])) {
        $firstCell = strtolower(trim($data[0][0] ?? ''));
        
        if (strpos($firstCell, 'actualizado') !== false) {
            array_shift($data);
            \Log::info('⚠️ Se detectó y eliminó fila de actualización');
        }
    }

    // Primera fila son los headers
    $headers = array_shift($data);
    
    // 🔥 NORMALIZAR HEADERS - AHORA CON SOPORTE PARA Ñ
    $normalizedHeaders = [];
    foreach ($headers as $header) {
        $normalized = trim($header);
        $normalized = mb_strtolower($normalized, 'UTF-8'); // Usar mb_strtolower para soportar ñ
        $normalized = str_replace(' ', '_', $normalized);
        // 🔥 PERMITIR ñ en los nombres de columnas
        $normalized = preg_replace('/[^a-z0-9_ñ]/', '', $normalized);
        $normalizedHeaders[] = $normalized;
    }

    \Log::info('✅ Headers normalizados: ' . implode(', ', array_slice($normalizedHeaders, 0, 10)) . '...');

    $headerCount = count($normalizedHeaders);
    $result = [];
    $processedCount = 0;
    $skippedCount = 0;

    // 🔥 PROCESAR FILA POR FILA OPTIMIZADO
    foreach ($data as $index => $row) {
        if (empty($row)) {
            $skippedCount++;
            continue;
        }

        $rowData = [];
        $hasData = false;
        
        for ($i = 0; $i < $headerCount; $i++) {
            if (isset($row[$i]) && $row[$i] !== '') {
                $rowData[$normalizedHeaders[$i]] = trim($row[$i]);
                $hasData = true;
            } else {
                $rowData[$normalizedHeaders[$i]] = '';
            }
        }
        
        if ($hasData) {
            $result[] = $rowData;
            $processedCount++;
        } else {
            $skippedCount++;
        }
        
        if ($processedCount % 5000 === 0 && $processedCount > 0) {
            gc_collect_cycles();
            $currentMemory = round((memory_get_usage() - $startMemory) / 1024 / 1024, 2);
            \Log::info("📊 Progreso: {$processedCount} filas procesadas, memoria usada: {$currentMemory} MB");
        }
    }

    unset($data);
    gc_collect_cycles();

    $endTime = microtime(true);
    $endMemory = memory_get_usage();
    $executionTime = round($endTime - $startTime, 2);
    $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);

    \Log::info("✅ Parseo completado", [
        'procesadas' => $processedCount,
        'saltadas' => $skippedCount,
        'tiempo' => $executionTime . 's',
        'memoria' => $memoryUsed . ' MB'
    ]);

    return $result;
}

    /**
     * Buscar en los datos (optimizado)
     */
    public function searchInData($data, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $data;
        }

        $searchTerm = strtolower($searchTerm);

        return array_filter($data, function ($row) use ($searchTerm) {
            foreach ($row as $value) {
                if (stripos($value, $searchTerm) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Obtener valores únicos de una columna
     */
    public function getUniqueValues(array $data, string $key)
    {
        $values = [];

        foreach ($data as $row) {
            if (isset($row[$key]) && $row[$key] !== '') {
                $values[$row[$key]] = true; // Usar key para evitar duplicados más rápido
            }
        }

        return array_values(array_keys($values));
    }

    /**
     * Filtrar por columna específica
     */
    public function filterByColumn($data, $columnName, $value)
    {
        if (empty($value)) {
            return $data;
        }

        return array_filter($data, function ($row) use ($columnName, $value) {
            return isset($row[$columnName]) &&
                strcasecmp($row[$columnName], $value) === 0;
        });
    }

    /**
     * Filtrar por múltiples columnas
     */
    public function filterByMultiple($data, array $filters)
    {
        if (empty($filters)) {
            return $data;
        }

        return array_filter($data, function ($row) use ($filters) {
            foreach ($filters as $column => $value) {
                if (!isset($row[$column]) || strcasecmp($row[$column], $value) !== 0) {
                    return false;
                }
            }
            return true;
        });
    }
}