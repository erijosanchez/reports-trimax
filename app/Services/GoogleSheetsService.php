<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Revolution\Google\Sheets\Facades\Sheets;

class GoogleSheetsService
{
    protected $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = config('google.spreadsheet_id');
    }

    /**
     * Obtener datos del sheet
     */
    public function getSheetData($sheetName = 'Orden_x_Usuario')
    {
        try {
            $rows = Sheets::spreadsheet($this->spreadsheetId)
                ->sheet($sheetName)
                ->get();

            return $rows->toArray();

        } catch (\Exception $e) {
            Log::error('Error al obtener datos de Google Sheets: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos con caché
     */
    public function getSheetDataCached($sheetName = 'Orden_x_Usuario', $minutes = 5)
    {
        $cacheKey = "google_sheets_{$sheetName}";
        
        return Cache::remember($cacheKey, $minutes * 60, function () use ($sheetName) {
            return $this->getSheetData($sheetName);
        });
    }

    /**
     * Limpiar caché
     */
    public function clearCache($sheetName = 'Orden_x_Usuario')
    {
        Cache::forget("google_sheets_{$sheetName}");
    }

    /**
     * Convertir datos a array asociativo con headers
     */
    public function parseSheetData($data)
    {
        if (empty($data) || count($data) < 2) {
            return [];
        }

        $headers = array_shift($data);
        
        $result = [];
        foreach ($data as $row) {
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[$header] = $row[$index] ?? '';
            }
            
            if (!empty(array_filter($rowData))) {
                $result[] = $rowData;
            }
        }

        return $result;
    }

    /**
     * Obtener datos ya parseados
     */
    public function getSheetDataParsed($sheetName = 'Orden_x_Usuario')
    {
        try {
            $collection = Sheets::spreadsheet($this->spreadsheetId)
                ->sheet($sheetName)
                ->get();

            $headers = $collection->pull(0);
            
            return $collection->map(function ($row) use ($headers) {
                return $headers->combine($row);
            })->toArray();

        } catch (\Exception $e) {
            Log::error('Error al obtener datos parseados: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener datos parseados con caché
     */
    public function getSheetDataParsedCached($sheetName = 'Orden_x_Usuario', $minutes = 5)
    {
        $cacheKey = "google_sheets_parsed_{$sheetName}";
        
        return Cache::remember($cacheKey, $minutes * 60, function () use ($sheetName) {
            return $this->getSheetDataParsed($sheetName);
        });
    }

    /**
     * Buscar en los datos
     */
    public function searchInData($data, $searchTerm)
    {
        if (empty($searchTerm)) {
            return $data;
        }

        $searchTerm = mb_strtolower($searchTerm);

        return array_filter($data, function ($row) use ($searchTerm) {
            foreach ($row as $value) {
                if (stripos(mb_strtolower($value), $searchTerm) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Filtrar por columna
     */
    public function filterByColumn($data, $columnName, $value)
    {
        if (empty($value) || !is_array($data)) {
            return $data;
        }

        return array_filter($data, function ($row) use ($columnName, $value) {
            return isset($row[$columnName]) && 
                   strcasecmp(trim($row[$columnName]), trim($value)) === 0;
        });
    }

    /**
     * Filtrar por múltiples condiciones
     */
    public function filterByMultiple($data, $filters)
    {
        if (empty($filters) || !is_array($data)) {
            return $data;
        }

        return array_filter($data, function ($row) use ($filters) {
            foreach ($filters as $column => $value) {
                if (!empty($value) && isset($row[$column])) {
                    if (strcasecmp(trim($row[$column]), trim($value)) !== 0) {
                        return false;
                    }
                }
            }
            return true;
        });
    }

    /**
     * Obtener valores únicos
     */
    public function getUniqueValues($data, $columnName)
    {
        $values = array_column($data, $columnName);
        $values = array_unique($values);
        $values = array_filter($values);
        sort($values);
        return array_values($values);
    }

    /**
     * Ordenar por columna
     */
    public function sortByColumn($data, $columnName, $ascending = true)
    {
        usort($data, function ($a, $b) use ($columnName, $ascending) {
            $valueA = $a[$columnName] ?? '';
            $valueB = $b[$columnName] ?? '';
            
            $comparison = strcasecmp($valueA, $valueB);
            
            return $ascending ? $comparison : -$comparison;
        });

        return $data;
    }

    /**
     * Obtener estadísticas
     */
    public function getStats($data)
    {
        $total = count($data);
        
        $enTransito = 0;
        $enSede = 0;
        $facturados = 0;
        $entregados = 0;
        
        foreach ($data as $row) {
            $ubicacion = mb_strtoupper($row['ubicacion_orden'] ?? '');
            
            if (strpos($ubicacion, 'TRANSITO') !== false) {
                $enTransito++;
            }
            
            if (strpos($ubicacion, 'SEDE') !== false) {
                $enSede++;
            }
            
            if (strpos($ubicacion, 'FACTURADO') !== false) {
                $facturados++;
            }
            
            if (strpos($ubicacion, 'ENTREGADO') !== false) {
                $entregados++;
            }
        }

        return [
            'total' => $total,
            'en_transito' => $enTransito,
            'en_sede' => $enSede,
            'facturados' => $facturados,
            'entregados' => $entregados,
            'disponibles_facturar' => $enSede + $enTransito
        ];
    }

    /**
     * Obtener rango específico
     */
    public function getRange($sheetName, $range)
    {
        try {
            $rows = Sheets::spreadsheet($this->spreadsheetId)
                ->sheet($sheetName)
                ->range($range)
                ->get();

            return $rows->toArray();

        } catch (\Exception $e) {
            Log::error('Error al obtener rango: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las hojas del spreadsheet
     */
    public function getSheetsList()
    {
        try {
            $sheets = Sheets::spreadsheet($this->spreadsheetId)
                ->sheetList();

            return $sheets;

        } catch (\Exception $e) {
            Log::error('Error al listar hojas: ' . $e->getMessage());
            return [];
        }
    }
}