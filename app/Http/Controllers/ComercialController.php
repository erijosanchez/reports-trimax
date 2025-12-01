<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheetsService;
use App\Models\AcuerdoComercial;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AcuerdoCreado;
use App\Notifications\AcuerdoAprobado;

class ComercialController extends Controller
{
    protected $googleSheets;

    public function __construct(GoogleSheetsService $googleSheets)
    {
        $this->googleSheets = $googleSheets;
    }

    public function acuerdos()
    {
        $acuerdos = AcuerdoComercial::with(['creador', 'validador', 'aprobador'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('comercial.acuerdos', compact('acuerdos'));
    }

    /** Acuerdos Comerciales Funciones */
    public function obtenerAcuerdos(Request $request)
    {
        try {
            $query = AcuerdoComercial::with(['creador', 'validador', 'aprobador']);

            // Filtros
            if ($request->filled('cliente')) {
                $query->where('razon_social', 'like', '%' . $request->cliente . '%');
            }

            if ($request->filled('sede')) {
                $query->where('sede', $request->sede);
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            // Búsqueda general
            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_acuerdo', 'like', "%{$buscar}%")
                        ->orWhere('razon_social', 'like', "%{$buscar}%")
                        ->orWhere('ruc', 'like', "%{$buscar}%");
                });
            }

            $acuerdos = $query->orderBy('created_at', 'desc')->get();

            // Actualizar estados automáticamente
            foreach ($acuerdos as $acuerdo) {
                $acuerdo->actualizarEstado();
            }

            return response()->json([
                'success' => true,
                'data' => $acuerdos
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en obtenerAcuerdos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener acuerdos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo acuerdo
     */
    public function crearAcuerdo(Request $request)
    {
        try {
            $validated = $request->validate([
                'sede' => 'required|string',
                'ruc' => 'required|string',
                'razon_social' => 'required|string',
                'consultor' => 'required|string',
                'ciudad' => 'required|string',
                'acuerdo_comercial' => 'required|string',
                'tipo_promocion' => 'required|string',
                'marca' => 'required|string',
                'ar' => 'nullable|string',
                'disenos' => 'nullable|string',
                'material' => 'nullable|string',
                'comentarios' => 'nullable|string',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'archivos.*' => 'nullable|file|max:10240' // 10MB
            ]);

            // Generar número de acuerdo
            $numeroAcuerdo = AcuerdoComercial::generarNumeroAcuerdo();

            // Subir archivos si existen
            $archivosAdjuntos = [];
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    $path = $archivo->store('acuerdos/' . $numeroAcuerdo, 'public');
                    $archivosAdjuntos[] = [
                        'nombre' => $archivo->getClientOriginalName(),
                        'path' => $path,
                        'size' => $archivo->getSize()
                    ];
                }
            }

            // Crear acuerdo
            $acuerdo = AcuerdoComercial::create([
                'numero_acuerdo' => $numeroAcuerdo,
                'user_id' => Auth::id(),
                'sede' => $validated['sede'],
                'ruc' => $validated['ruc'],
                'razon_social' => $validated['razon_social'],
                'consultor' => $validated['consultor'],
                'ciudad' => $validated['ciudad'],
                'acuerdo_comercial' => $validated['acuerdo_comercial'],
                'tipo_promocion' => $validated['tipo_promocion'],
                'marca' => $validated['marca'],
                'ar' => $validated['ar'] ?? null,
                'disenos' => $validated['disenos'] ?? null,
                'material' => $validated['material'] ?? null,
                'comentarios' => $validated['comentarios'] ?? null,
                'fecha_inicio' => $validated['fecha_inicio'],
                'fecha_fin' => $validated['fecha_fin'],
                'archivos_adjuntos' => $archivosAdjuntos,
                'estado' => 'Solicitado',
                'validado' => 'Pendiente',
                'aprobado' => 'Pendiente'
            ]);

            // 📧 Enviar notificaciones
            $this->enviarNotificacionCreacion($acuerdo);

            return response()->json([
                'success' => true,
                'message' => 'Acuerdo creado exitosamente',
                'acuerdo' => $acuerdo->load(['creador', 'validador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al crear acuerdo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear acuerdo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function validarAcuerdo(Request $request, $id)
    {
        try {
            $acuerdo = AcuerdoComercial::findOrFail($id);

            // Verificar permisos
            if (Auth::user()->email !== 'planeamiento.comercial@trimaxperu.com') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para validar acuerdos'
                ], 403);
            }

            $validated = $request->validate([
                'accion' => 'required|in:Aprobado,Rechazado'
            ]);

            $acuerdo->update([
                'validado' => $validated['accion'],
                'validado_por' => Auth::id(),
                'validado_at' => now()
            ]);

            // Actualizar estado
            $acuerdo->actualizarEstado();

            // Enviar notificación si está completamente aprobado
            if ($acuerdo->validado === 'Aprobado' && $acuerdo->aprobado === 'Aprobado') {
                $this->enviarNotificacionAprobacion($acuerdo);
            }

            return response()->json([
                'success' => true,
                'message' => 'Acuerdo validado correctamente',
                'acuerdo' => $acuerdo->load(['creador', 'validador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al validar acuerdo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar acuerdo (Gerencia)
     */
    public function aprobarAcuerdo(Request $request, $id)
    {
        try {
            $acuerdo = AcuerdoComercial::findOrFail($id);

            // Verificar permisos
            if (Auth::user()->email !== 'smonopoli@trimaxperu.com') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para aprobar acuerdos'
                ], 403);
            }

            $validated = $request->validate([
                'accion' => 'required|in:Aprobado,Rechazado'
            ]);

            $acuerdo->update([
                'aprobado' => $validated['accion'],
                'aprobado_por' => Auth::id(),
                'aprobado_at' => now()
            ]);

            // Actualizar estado
            $acuerdo->actualizarEstado();

            // Enviar notificación si está completamente aprobado
            if ($acuerdo->validado === 'Aprobado' && $acuerdo->aprobado === 'Aprobado') {
                $this->enviarNotificacionAprobacion($acuerdo);
            }

            return response()->json([
                'success' => true,
                'message' => 'Acuerdo aprobado correctamente',
                'acuerdo' => $acuerdo->load(['creador', 'validador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al aprobar acuerdo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📧 Enviar notificación de creación
     */
    private function enviarNotificacionCreacion($acuerdo)
    {
        try {
            // Obtener usuarios que deben ser notificados
            $validador = User::where('email', 'planeamiento.comercial@trimaxperu.com')->first();
            $aprobador = User::where('email', 'smonopoli@trimaxperu.com')->first();

            $usuarios = collect([$validador, $aprobador, $acuerdo->creador])->filter();

            Notification::send($usuarios, new AcuerdoCreado($acuerdo));

            \Log::info('Notificaciones de creación enviadas para acuerdo: ' . $acuerdo->numero_acuerdo);
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificaciones de creación: ' . $e->getMessage());
        }
    }

    /**
     * 📧 Enviar notificación de aprobación
     */
    private function enviarNotificacionAprobacion($acuerdo)
    {
        try {
            // Notificar al creador
            $acuerdo->creador->notify(new AcuerdoAprobado($acuerdo));

            \Log::info('Notificación de aprobación enviada para acuerdo: ' . $acuerdo->numero_acuerdo);
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificación de aprobación: ' . $e->getMessage());
        }
    }

    /**
     * Descargar archivo adjunto
     */
    public function descargarArchivo($id, $index)
    {
        try {
            $acuerdo = AcuerdoComercial::findOrFail($id);
            $archivos = $acuerdo->archivos_adjuntos;

            if (!isset($archivos[$index])) {
                return response()->json(['error' => 'Archivo no encontrado'], 404);
            }

            $archivo = $archivos[$index];
            $path = storage_path('app/public/' . $archivo['path']);

            if (!file_exists($path)) {
                return response()->json(['error' => 'Archivo no existe'], 404);
            }

            return response()->download($path, $archivo['nombre']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al descargar archivo'], 500);
        }
    }

    /**
     * Vista para consultar órdenes
     */

    public function consultarOrden()
    {
        return view('comercial.consulta-orden');
    }

    public function obtenerOrdenes(Request $request)
    {
        try {
            // 🔥 OPTIMIZACIÓN PARA 80K FILAS
            ini_set('memory_limit', '1024M'); // 1GB para estar seguros
            ini_set('max_execution_time', '300'); // 5 minutos
            set_time_limit(300);

            \Log::info('🚀 Iniciando carga de órdenes', [
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time')
            ]);

            // NO usar caché de base de datos para datasets grandes
            // Usar caché de archivos o sin caché
            $useCache = !$request->has('nocache');

            if ($useCache) {
                // Usar caché de archivos en lugar de database
                $cacheKey = 'google_sheets_historico';
                $ordenes = \Cache::store('file')->remember($cacheKey, 600, function () { // 10 minutos de caché
                    \Log::info('📥 Obteniendo datos desde Google Sheets (sin caché)');
                    $rawData = $this->googleSheets->getSheetData('Historico');
                    \Log::info('📊 Filas obtenidas: ' . count($rawData));
                    return $this->googleSheets->parseSheetData($rawData);
                });
            } else {
                \Log::info('📥 Obteniendo datos desde Google Sheets (forzado, sin caché)');
                $rawData = $this->googleSheets->getSheetData('Historico');
                \Log::info('📊 Filas obtenidas: ' . count($rawData));
                $ordenes = $this->googleSheets->parseSheetData($rawData);
            }

            if (empty($ordenes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudieron obtener datos del Google Sheet'
                ], 500);
            }

            \Log::info('✅ Órdenes parseadas: ' . count($ordenes));

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
                \Log::info('📊 Después de filtros: ' . count($ordenes));
            }

            // Filtrar por estado (ubicación)
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
                \Log::info('📊 Después de filtro estado: ' . count($ordenes));
            }

            // Búsqueda general
            if ($request->filled('buscar')) {
                $ordenes = $this->googleSheets->searchInData($ordenes, $request->buscar);
                \Log::info('📊 Después de búsqueda: ' . count($ordenes));
            }

            // Reindexar array
            $ordenes = array_values($ordenes);

            // Estadísticas
            $stats = $this->calcularEstadisticas($ordenes);

            // Liberar memoria
            gc_collect_cycles();

            \Log::info('✅ Respuesta lista', [
                'total_ordenes' => count($ordenes),
                'memory_usado' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
                'memory_pico' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB'
            ]);

            return response()->json([
                'success' => true,
                'data' => $ordenes,
                'stats' => $stats,
                'total' => count($ordenes)
            ]);
        } catch (\Exception $e) {
            \Log::error('❌ Error en obtenerOrdenes: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('Memory usado: ' . round(memory_get_usage() / 1024 / 1024, 2) . ' MB');

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

            // Forzar garbage collection
            gc_collect_cycles();

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
            // Aumentar límite de memoria para exportación
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', '300');

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

            $callback = function () use ($rawData) {
                $file = fopen('php://output', 'w');

                // BOM para Excel UTF-8
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

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
