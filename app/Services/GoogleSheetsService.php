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
    public function getSheetData($sheetName = 'Orden_x_Usuario', $range = null)
    {
        try {
            // Si no se especifica rango, obtener todo
            $fullRange = $range ? "{$sheetName}!{$range}" : $sheetName;
            
            $response = $this->service->spreadsheets_values->get(
                $this->spreadsheetId,
                $fullRange
            );

            return $response->getValues();
        } catch (\Exception $e) {
            \Log::error('Error al obtener datos de Google Sheets: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos con caché (5 minutos)
     */
    public function getSheetDataCached($sheetName = 'Orden_x_Usuario', $range = null)
    {
        $cacheKey = "google_sheets_{$sheetName}_" . md5($range);
        
        return Cache::remember($cacheKey, 300, function () use ($sheetName, $range) {
            return $this->getSheetData($sheetName, $range);
        });
    }

    /**
     * Convertir datos de sheet a array asociativo
     */
    public function parseSheetData($data)
    {
        if (empty($data)) {
            return [];
        }

        // Primera fila son los headers
        $headers = array_shift($data);
        
        $result = [];
        foreach ($data as $row) {
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[$header] = $row[$index] ?? '';
            }
            $result[] = $rowData;
        }

        return $result;
    }

    /**
     * Buscar en los datos
     */
    public function searchInData($data, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $data;
        }

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
}