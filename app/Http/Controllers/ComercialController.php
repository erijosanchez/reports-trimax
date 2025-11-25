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
        // Lógica para mostrar los acuerdos comerciales
        return view('comercial.acuerdos');
    }

    public function consultaOrden()
    {
        // Lógica para consultar una orden
        return view('comercial.consulta-orden');
    }

    public function obtenerOrdenes(Request $request)
    {
        try {
            // Obtener datos parseados con caché
            $useCache = !$request->has('nocache');

            if ($useCache) {
                $ordenes = $this->googleSheets->getSheetDataParsedCached('Orden_x_Usuario', 5);
            } else {
                $ordenes = $this->googleSheets->getSheetDataParsed('Orden_x_Usuario');
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

            // Filtrar por estado
            if ($request->filled('estado')) {
                $estado = mb_strtoupper($request->estado);
                $ordenes = array_filter($ordenes, function ($orden) use ($estado) {
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

            // Reindexar
            $ordenes = array_values($ordenes);

            // Estadísticas
            $stats = $this->googleSheets->getStats($ordenes);

            return response()->json([
                'success' => true,
                'data' => $ordenes,
                'stats' => $stats,
                'total' => count($ordenes)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerSedes(Request $request)
    {
        try {
            $ordenes = $this->googleSheets->getSheetDataParsedCached('Orden_x_Usuario');
            $sedes = $this->googleSheets->getUniqueValues($ordenes, 'descripcion_sede');

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

    public function limpiarCache()
    {
        try {
            $this->googleSheets->clearCache('Orden_x_Usuario');

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

    public function exportarCsv(Request $request)
    {
        try {
            $ordenes = $this->googleSheets->getSheetDataParsedCached('Orden_x_Usuario');

            if (empty($ordenes)) {
                return response()->json(['error' => 'No hay datos para exportar'], 404);
            }

            $filename = 'ordenes_' . date('Y-m-d_His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($ordenes) {
                $file = fopen('php://output', 'w');

                // BOM para Excel UTF-8
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Headers
                if (!empty($ordenes)) {
                    fputcsv($file, array_keys($ordenes[0]));

                    // Datos
                    foreach ($ordenes as $orden) {
                        fputcsv($file, $orden);
                    }
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
