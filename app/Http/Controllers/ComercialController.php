<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheetsService;

class ComercialController extends Controller
{

    protected $sheetsService;

    public function __construct(GoogleSheetsService $sheetsService)
    {
        $this->sheetsService = $sheetsService;
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

    public function index(Request $request)
    {
        try {
            Log::info('Solicitud de órdenes recibida', [
                'filtros' => $request->all()
            ]);

            // Forzar actualización si se solicita
            $forceRefresh = $request->has('nocache');
            if ($forceRefresh) {
                $this->sheetsService->clearCache();
            }

            // Obtener datos
            $result = $this->sheetsService->getOrdenes(!$forceRefresh);

            if (!$result['success']) {
                return response()->json($result, 500);
            }

            $ordenes = collect($result['data']);

            // Aplicar filtros
            if ($request->filled('sede')) {
                $ordenes = $ordenes->filter(function ($orden) use ($request) {
                    return stripos($orden['descripcion_sede'] ?? '', $request->sede) !== false;
                });
            }

            if ($request->filled('estado')) {
                $estado = strtoupper($request->estado);
                $ordenes = $ordenes->filter(function ($orden) use ($estado) {
                    $ubicacion = strtoupper($orden['ubicacion_orden'] ?? '');

                    switch ($estado) {
                        case 'FACTURADO':
                            return str_contains($ubicacion, 'FACTURADO') ||
                                str_contains($ubicacion, 'ENTREGADO');
                        case 'EN TRANSITO':
                            return str_contains($ubicacion, 'TRANSITO');
                        case 'EN SEDE':
                            return str_contains($ubicacion, 'SEDE') &&
                                !str_contains($ubicacion, 'TRANSITO');
                        case 'OTROS':
                            return !str_contains($ubicacion, 'FACTURADO') &&
                                !str_contains($ubicacion, 'ENTREGADO') &&
                                !str_contains($ubicacion, 'TRANSITO') &&
                                !str_contains($ubicacion, 'SEDE');
                        default:
                            return true;
                    }
                });
            }

            if ($request->filled('tipo_orden')) {
                $ordenes = $ordenes->filter(function ($orden) use ($request) {
                    return stripos($orden['tipo_orden'] ?? '', $request->tipo_orden) !== false;
                });
            }

            if ($request->filled('buscar')) {
                $buscar = strtolower($request->buscar);
                $ordenes = $ordenes->filter(function ($orden) use ($buscar) {
                    $searchFields = implode(' ', [
                        $orden['numero_orden'] ?? '',
                        $orden['Cliente'] ?? '',
                        $orden['RUC'] ?? '',
                        $orden['descripcion_producto'] ?? '',
                        $orden['descripcion_sede'] ?? ''
                    ]);
                    return stripos($searchFields, $buscar) !== false;
                });
            }

            // Obtener estadísticas
            $stats = $this->sheetsService->getEstadisticas();

            Log::info('Órdenes filtradas', [
                'total_original' => $result['total'],
                'total_filtrado' => $ordenes->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => $ordenes->values()->toArray(),
                'stats' => $stats,
                'total' => $ordenes->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error en OrdenController::index', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener sedes disponibles
     */
    public function getSedes()
    {
        try {
            $result = $this->sheetsService->getSedes();
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener sedes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar caché manualmente
     */
    public function clearCache()
    {
        try {
            $this->sheetsService->clearCache();
            return response()->json([
                'success' => true,
                'message' => 'Caché limpiado correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al limpiar caché: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar caché: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Probar conexión con Google Sheets
     */
    public function testConnection()
    {
        try {
            $result = $this->sheetsService->testConnection();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al probar conexión: ' . $e->getMessage()
            ], 500);
        }
    }
}
