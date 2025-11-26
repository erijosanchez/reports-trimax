<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheetsService;

class ComercialController extends Controller
{
    protected $googleSheets;

    public function __construct(GoogleSheetsService $googleSheets)
    {
        $this->googleSheets = $googleSheets;
    }

    public function acuerdos()
    {
        return view('comercial.acuerdos');
    }

    public function consultarOrden()
    {
        return view('comercial.consulta-orden');
    }

    public function obtenerOrdenes(Request $request)
    {
        try {
            // NO usar caché de base de datos para datasets grandes
            // Usar caché de archivos o sin caché
            $useCache = !$request->has('nocache');
            
            if ($useCache) {
                // Usar caché de archivos en lugar de database
                $cacheKey = 'google_sheets_historico';
                $ordenes = \Cache::store('file')->remember($cacheKey, 300, function () {
                    $rawData = $this->googleSheets->getSheetData('Historico');
                    return $this->googleSheets->parseSheetData($rawData);
                });
            } else {
                $rawData = $this->googleSheets->getSheetData('Historico');
                $ordenes = $this->googleSheets->parseSheetData($rawData);
            }

            if (empty($ordenes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudieron obtener datos del Google Sheet'
                ], 500);
            }

            // Aplicar filtros
            $filters = [];
            
            if ($request->filled('sede')) {
                $filters['descripcion_sede'] = $request->sede;
            }

            if ($request->filled('tipo_orden')) {
                $filters['tipo_orden'] = $request->tipo_orden;
            }

            if (!empty($filters)) {
                $ordenes = $this->googleSheets->filterByMultiple($ordenes, $filters);
            }

            // Filtrar por estado (ubicación)
            if ($request->filled('estado')) {
                $estado = mb_strtoupper($request->estado);
                $ordenes = array_filter($ordenes, function($orden) use ($estado) {
                    $ubicacion = mb_strtoupper($orden['ubicacion_orden'] ?? '');
                    
                    if ($estado === 'FACTURADO') {
                        return strpos($ubicacion, 'FACTURADO') !== false || 
                               strpos($ubicacion, 'ENTREGADO') !== false;
                    } elseif ($estado === 'EN TRANSITO') {
                        return strpos($ubicacion, 'TRANSITO') !== false;
                    } elseif ($estado === 'EN SEDE') {
                        return strpos($ubicacion, 'SEDE') !== false;
                    } elseif ($estado === 'OTROS') {
                        return strpos($ubicacion, 'FACTURADO') === false && 
                               strpos($ubicacion, 'ENTREGADO') === false &&
                               strpos($ubicacion, 'TRANSITO') === false &&
                               strpos($ubicacion, 'SEDE') === false;
                    }
                    
                    return true;
                });
            }

            // Búsqueda general
            if ($request->filled('buscar')) {
                $ordenes = $this->googleSheets->searchInData($ordenes, $request->buscar);
            }

            // Reindexar array
            $ordenes = array_values($ordenes);

            // Estadísticas
            $stats = $this->calcularEstadisticas($ordenes);

            return response()->json([
                'success' => true,
                'data' => $ordenes,
                'stats' => $stats,
                'total' => count($ordenes)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en obtenerOrdenes: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener sedes únicas
     */
    public function obtenerSedes(Request $request)
    {
        try {
            $cacheKey = 'google_sheets_sedes';
            
            $sedes = \Cache::store('file')->remember($cacheKey, 600, function () {
                $rawData = $this->googleSheets->getSheetData('Historico');
                $ordenes = $this->googleSheets->parseSheetData($rawData);
                return $this->googleSheets->getUniqueValues($ordenes, 'descripcion_sede');
            });

            return response()->json([
                'success' => true,
                'data' => $sedes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar caché
     */
    public function limpiarCache()
    {
        try {
            \Cache::store('file')->forget('google_sheets_historico');
            \Cache::store('file')->forget('google_sheets_sedes');
            
            return response()->json([
                'success' => true,
                'message' => 'Caché limpiado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular estadísticas de las órdenes
     */
    private function calcularEstadisticas($ordenes)
    {
        $total = count($ordenes);

        $enTransito = count(array_filter($ordenes, function ($orden) {
            return stripos($orden['ubicacion_orden'] ?? '', 'TRANSITO') !== false;
        }));

        $enSede = count(array_filter($ordenes, function ($orden) {
            return stripos($orden['ubicacion_orden'] ?? '', 'SEDE') !== false;
        }));

        $facturados = count(array_filter($ordenes, function ($orden) {
            return stripos($orden['ubicacion_orden'] ?? '', 'FACTURADO') !== false;
        }));

        $entregados = count(array_filter($ordenes, function ($orden) {
            return stripos($orden['ubicacion_orden'] ?? '', 'ENTREGADO') !== false;
        }));

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
     * Exportar a CSV
     */
    public function exportarExcel(Request $request)
    {
        try {
            // Obtener datos sin caché para export
            $rawData = $this->googleSheets->getSheetData('Historico');
            
            if (empty($rawData)) {
                return response()->json(['error' => 'No hay datos para exportar'], 404);
            }

            $filename = 'ordenes_historico_' . date('Y-m-d_His') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($rawData) {
                $file = fopen('php://output', 'w');
                
                // BOM para Excel UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Escribir todas las filas
                foreach ($rawData as $row) {
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ], 500);
        }
    }
}