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
use App\Notifications\AcuerdoDeshabilitado;
use App\Notifications\AcuerdoExtendido;
use App\Notifications\AcuerdoRehabilitado;
use App\Notifications\AcuerdoVigente;
use Google\Client;
use Google\Service\Sheets;
use carbon\Carbon;
use Illuminate\Support\Facades\DB;



class ComercialController extends Controller
{
    protected $googleSheets;

    public function __construct(GoogleSheetsService $googleSheets)
    {
        $this->googleSheets = $googleSheets;
    }

    public function obtenerEstadisticasGenerales(Request $request)
    {
        try {
            $stats = \Cache::remember('stats_generales_global', 300, function () {

                $row = DB::table('ordenes_historico')
                    ->selectRaw("
                    COUNT(*)                                                                AS total,

                    -- En sede / En tránsito / Facturado
                    SUM(CASE WHEN ubicacion_orden = 'En sede'              THEN 1 ELSE 0 END) AS en_sede,
                    SUM(CASE WHEN ubicacion_orden = 'En Transito'          THEN 1 ELSE 0 END) AS en_transito,
                    SUM(CASE WHEN ubicacion_orden = 'Facturado y entregado' THEN 1 ELSE 0 END) AS facturados,

                    -- Disponibles para facturar = Solicitado (en sede o tránsito, aún no facturado)
                    SUM(CASE WHEN estado_orden = 'Solicitado'              THEN 1 ELSE 0 END) AS disponibles_facturar,

                    -- Importes: solo órdenes Solicitadas (pendientes de cobro)
                    SUM(CASE WHEN ubicacion_orden = 'En sede'     AND estado_orden = 'Solicitado' THEN importe ELSE 0 END) AS importe_sede,
                    SUM(CASE WHEN ubicacion_orden = 'En Transito' AND estado_orden = 'Solicitado' THEN importe ELSE 0 END) AS importe_transito
                ")
                    ->first();

                return [
                    'total'               => (int)   ($row->total               ?? 0),
                    'en_sede'             => (int)   ($row->en_sede             ?? 0),
                    'en_transito'         => (int)   ($row->en_transito         ?? 0),
                    'facturados'          => (int)   ($row->facturados          ?? 0),
                    'disponibles_facturar' => (int)   ($row->disponibles_facturar ?? 0),
                    'importe_sede'        => round((float)($row->importe_sede    ?? 0), 2),
                    'importe_transito'    => round((float)($row->importe_transito ?? 0), 2),
                    'importe_total'       => round((float)(($row->importe_sede ?? 0) + ($row->importe_transito ?? 0)), 2),
                ];
            });

            return response()->json([
                'success' => true,
                'stats'   => $stats,   // ← blade usa response.stats
                'data'    => $stats,   // ← por compatibilidad con versión anterior
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en obtenerEstadisticasGenerales: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function acuerdos()
    {
        if (!auth()->user()->puedeVerAcuerdosComerciales()) {
            abort(403, 'No tienes permiso para ver los acuerdos comerciales');
        }

        return view('comercial.acuerdos');
    }

    /**
     * Vista: Órdenes por Sede
     */
    public function ordenesPorSede()
    {
        if (!auth()->user()->puedeVerAcuerdosComerciales()) {
            abort(403, 'No tienes permiso para ver esta sección');
        }

        return view('comercial.ordenes-por-sede');
    }

    /**
     * API: Órdenes por Sede — lee de MySQL (instantáneo)
     */
    public function obtenerOrdenesPorSede(Request $request)
    {
        try {
            $mes  = (int) $request->input('mes',  now()->month);
            $anio = (int) $request->input('anio', now()->year);

            // Sin caché — MySQL es tan rápido que no hace falta
            $rows = \DB::table('ordenes_sede_stats')
                ->where('mes', $mes)
                ->where('anio', $anio)
                ->orderBy('sede')
                ->orderBy('fecha')
                ->get(['sede', 'fecha', 'cant', 'facturadas']);

            if ($rows->isEmpty()) {
                // Si no hay datos aún, disparar sync manual en background
                // y avisar al usuario
                return response()->json([
                    'success' => false,
                    'message' => 'Sin datos para este período. El sistema sincronizará en breve.',
                    'sync_pending' => true,
                ]);
            }

            // ── Agrupar para la tabla ───────────────────────────────────────
            $agrupado        = [];
            $fechaTimestamps = [];
            $sedesVistas     = [];

            foreach ($rows as $row) {
                $sede     = $row->sede;
                $fechaKey = \Carbon\Carbon::parse($row->fecha)->format('d/m/Y');
                $ts       = strtotime($row->fecha);

                $agrupado[$sede][$fechaKey] = [
                    'cant'       => $row->cant,
                    'facturadas' => $row->facturadas,
                    'pct'        => $row->cant > 0
                        ? (int) round(($row->facturadas / $row->cant) * 100)
                        : null,
                ];

                $fechaTimestamps[$fechaKey] = $ts;
                $sedesVistas[$sede]         = true;
            }

            // Ordenar fechas cronológicamente
            asort($fechaTimestamps);
            $fechasOrdenadas = array_keys($fechaTimestamps);

            // Sedes A-Z
            ksort($sedesVistas);
            $sedesOrdenadas = array_keys($sedesVistas);

            // Construir tabla
            $tabla           = [];
            $totalesPorFecha = [];
            $totalGenCant    = 0;
            $totalGenFact    = 0;

            foreach ($sedesOrdenadas as $sede) {
                $fila = [
                    'sede'             => $sede,
                    'dias'             => [],
                    'total_cant'       => 0,
                    'total_facturadas' => 0,
                    'total_pct'        => null,
                ];

                foreach ($fechasOrdenadas as $f) {
                    $dia  = $agrupado[$sede][$f] ?? null;
                    $cant = $dia['cant']       ?? 0;
                    $fact = $dia['facturadas'] ?? 0;
                    $pct  = $cant > 0 ? (int) round(($fact / $cant) * 100) : null;

                    $fila['dias'][$f]         = ['cant' => $cant, 'pct' => $pct];
                    $fila['total_cant']       += $cant;
                    $fila['total_facturadas'] += $fact;

                    $totalesPorFecha[$f]['cant']       = ($totalesPorFecha[$f]['cant']       ?? 0) + $cant;
                    $totalesPorFecha[$f]['facturadas'] = ($totalesPorFecha[$f]['facturadas'] ?? 0) + $fact;
                }

                if ($fila['total_cant'] > 0) {
                    $fila['total_pct'] = (int) round(
                        ($fila['total_facturadas'] / $fila['total_cant']) * 100
                    );
                }

                $totalGenCant += $fila['total_cant'];
                $totalGenFact += $fila['total_facturadas'];
                $tabla[]       = $fila;
            }

            $totalesConPct = [];
            foreach ($fechasOrdenadas as $f) {
                $c  = $totalesPorFecha[$f]['cant']       ?? 0;
                $ff = $totalesPorFecha[$f]['facturadas'] ?? 0;
                $totalesConPct[$f] = [
                    'cant' => $c,
                    'pct'  => $c > 0 ? (int) round(($ff / $c) * 100) : null,
                ];
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'fechas'     => $fechasOrdenadas,
                    'tabla'      => $tabla,
                    'totales'    => $totalesConPct,
                    'total_cant' => $totalGenCant,
                    'total_pct'  => $totalGenCant > 0
                        ? (int) round(($totalGenFact / $totalGenCant) * 100)
                        : null,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en obtenerOrdenesPorSede: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper: fecha DD/MM/YYYY o YYYY-MM-DD HH:MM:SS → timestamp
     * (agregar solo si no existe ya en el controller)
     */
    private function parsearFechaOrden($fechaStr)
    {
        if (!$fechaStr || $fechaStr === '-') return null;
        $s = trim($fechaStr);

        // DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $s, $m)) {
            return mktime(0, 0, 0, (int)$m[2], (int)$m[1], (int)$m[3]);
        }
        // YYYY-MM-DD (con o sin hora)
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $s, $m)) {
            return mktime(0, 0, 0, (int)$m[2], (int)$m[3], (int)$m[1]);
        }

        $ts = strtotime($s);
        return ($ts && $ts > 0) ? $ts : null;
    }

    /** Acuerdos Comerciales Funciones */
    public function obtenerAcuerdos(Request $request)
    {
        try {
            $user = Auth::user();
            $query = AcuerdoComercial::with(['creador', 'validador', 'aprobador']);

            if ($user->isSede()) {
                $query->where('sede', $user->sede);
            }

            // Filtros (solo aplican si no es Sede, o son compatibles con su sede)
            if ($request->filled('usuario') && !$user->isSede()) {
                $query->where('user_id', $request->usuario);
            }

            if ($request->filled('sede') && !$user->isSede()) {
                $query->where('sede', $request->sede);
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_acuerdo', 'like', "%{$buscar}%")
                        ->orWhere('razon_social', 'like', "%{$buscar}%")
                        ->orWhere('ruc', 'like', "%{$buscar}%");
                });
            }

            $acuerdos = $query->orderBy('created_at', 'desc')->get();

            foreach ($acuerdos as $acuerdo) {
                $acuerdo->actualizarEstado();
            }

            return response()->json([
                'success' => true,
                'data' => $acuerdos,
                'is_sede' => $user->isSede(),
                'sede_name' => $user->sede ?? null
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

            // Guardar estado anterior
            $estadoAnterior = $acuerdo->estado;

            $acuerdo->update([
                'validado' => $validated['accion'],
                'validado_por' => Auth::id(),
                'validado_at' => now()
            ]);

            // Actualizar estado
            $acuerdo->actualizarEstado();

            // Si pasa a Vigente, notificar a Juancho
            if ($estadoAnterior !== 'Vigente' && $acuerdo->estado === 'Vigente') {
                $this->enviarNotificacionVigente($acuerdo);
            }

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
     * Obtener usuarios de la sede del acuerdo (si el creador no es de esa sede)
     */

    private function obtenerUsuariosSede($acuerdo)
    {
        if ($acuerdo->creador && $acuerdo->creador->isSede()) {
            return collect(); // El creador ya ES la sede, no agregar más
        }

        return User::where('sede', $acuerdo->sede)
            ->whereHas('roles', function ($q) {
                $q->where('name', 'sede');
            })
            ->get();
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

            // Guardar estado anterior
            $estadoAnterior = $acuerdo->estado;

            $acuerdo->update([
                'aprobado' => $validated['accion'],
                'aprobado_por' => Auth::id(),
                'aprobado_at' => now()
            ]);

            // Actualizar estado
            $acuerdo->actualizarEstado();

            // Si pasa a Vigente, notificar a Juancho
            if ($estadoAnterior !== 'Vigente' && $acuerdo->estado === 'Vigente') {
                $this->enviarNotificacionVigente($acuerdo);
            }

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
            $validador = User::where('email', 'planeamiento.comercial@trimaxperu.com')->first();
            $aprobador = User::where('email', 'smonopoli@trimaxperu.com')->first();

            $usuarios = collect([$validador, $aprobador, $acuerdo->creador])
                ->filter()
                ->merge($this->obtenerUsuariosSede($acuerdo))
                ->unique('id');

            Notification::send($usuarios, new AcuerdoCreado($acuerdo));
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
            $usuarios = collect([$acuerdo->creador])
                ->filter()
                ->merge($this->obtenerUsuariosSede($acuerdo))
                ->unique('id');

            Notification::send($usuarios, new AcuerdoAprobado($acuerdo));
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

    /**
     * Obtener solo las 100 órdenes más recientes (CARGA RÁPIDA)
     */
    public function obtenerOrdenesRecientes(Request $request)
    {
        try {
            $limite = (int) $request->input('limite', 1000);
            $limite = min($limite, 2000);

            $ordenes = DB::table('ordenes_historico')
                ->orderByDesc('fecha_orden')
                ->orderByDesc('id')
                ->limit($limite)
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $ordenes,
                'total'   => $ordenes->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en obtenerOrdenesRecientes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Convertir fecha para comparación
     */
    private function convertirFechaParaComparar($fechaStr)
    {
        if (!$fechaStr || $fechaStr === '-') return 0;

        // Formato DD/MM/YYYY
        $partes = explode('/', $fechaStr);
        if (count($partes) === 3) {
            return mktime(0, 0, 0, $partes[1], $partes[0], $partes[2]);
        }

        return strtotime($fechaStr);
    }

    public function obtenerOrdenes(Request $request)
    {
        try {
            $query = DB::table('ordenes_historico')
                ->orderByDesc('fecha_orden')
                ->orderByDesc('id');

            // Filtros opcionales
            if ($sede = $request->input('sede')) {
                $query->where('descripcion_sede', $sede);
            }
            if ($estado = $request->input('estado')) {
                // Mapear valores del blade a los valores reales en BD
                $estadoUpper = strtoupper($estado);
                if ($estadoUpper === 'FACTURADO') {
                    $query->where('ubicacion_orden', 'Facturado y entregado');
                } elseif ($estadoUpper === 'EN TRANSITO') {
                    $query->where('ubicacion_orden', 'En Transito');
                } elseif ($estadoUpper === 'EN SEDE') {
                    $query->where('ubicacion_orden', 'En sede');
                } elseif ($estadoUpper === 'SOLICITADO') {
                    $query->where('estado_orden', 'Solicitado');
                } elseif ($estadoUpper === 'OTROS') {
                    $query->whereNotIn('ubicacion_orden', [
                        'Facturado y entregado',
                        'En Transito',
                        'En sede'
                    ])->where('estado_orden', '!=', 'Solicitado');
                }
            }
            if ($tipoOrden = $request->input('tipo_orden')) {
                $query->where('tipo_orden', 'like', "%{$tipoOrden}%");
            }
            if ($buscar = $request->input('buscar')) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('numero_orden', 'like', "%{$buscar}%")
                        ->orWhere('cliente',    'like', "%{$buscar}%")
                        ->orWhere('ruc',        'like', "%{$buscar}%");
                });
            }

            // limite=0 significa SIN LÍMITE (modo histórico completo)
            $limite = (int) $request->input('limite', 1000);

            if ($limite > 0) {
                $limite = min($limite, 5000);
                $query->limit($limite);
            }
            // Si limite=0 → sin limit, devuelve todo

            $ordenes = $query->get();

            return response()->json([
                'success' => true,
                'data'    => $ordenes,
                'total'   => $ordenes->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en obtenerOrdenes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Rehabilitar acuerdo
     */
    public function rehabilitarAcuerdo(Request $request, $id)
    {
        try {
            $acuerdo = AcuerdoComercial::findOrFail($id);

            // Verificar permisos
            $emailsAutorizados = ['smonopoli@trimaxperu.com', 'planeamiento.comercial@trimaxperu.com'];
            if (!in_array(Auth::user()->email, $emailsAutorizados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para rehabilitar acuerdos'
                ], 403);
            }

            // Verificar que esté deshabilitado
            if ($acuerdo->habilitado) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este acuerdo ya está habilitado'
                ], 400);
            }

            $validated = $request->validate([
                'motivo' => 'required|string|min:10'
            ]);

            $acuerdo->update([
                'habilitado' => true,
                'motivo_rehabilitacion' => $validated['motivo'],
                'rehabilitado_at' => now(),
                'rehabilitado_por' => Auth::id()
            ]);

            // Actualizar estado
            $acuerdo->actualizarEstado();

            // Enviar notificaciones
            $this->enviarNotificacionRehabilitacion($acuerdo, $validated['motivo']);

            return response()->json([
                'success' => true,
                'message' => 'Acuerdo rehabilitado correctamente',
                'acuerdo' => $acuerdo->load(['creador', 'validador', 'aprobador', 'rehabilitador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al rehabilitar acuerdo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar notificación cuando acuerdo pasa a Vigente
     */
    private function enviarNotificacionVigente($acuerdo)
    {
        try {
            // Obtener usuario de auditoría
            $auditor = User::where('email', 'auditor.junior@trimaxperu.com')->first();

            if ($auditor) {
                $auditor->notify(new AcuerdoVigente($acuerdo));
                \Log::info('✅ Notificación de acuerdo vigente enviada a auditor.junior@trimaxperu.com');
            } else {
                \Log::warning('⚠️ Usuario auditor.junior@trimaxperu.com no encontrado');
            }
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificación de acuerdo vigente: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificación de rehabilitación
     */
    private function enviarNotificacionRehabilitacion($acuerdo, $motivo)
    {
        try {
            // Usuarios autorizados
            $destinatarios = User::whereIn('email', [
                'smonopoli@trimaxperu.com',
                'planeamiento.comercial@trimaxperu.com'
            ])->get();

            // ✅ SIEMPRE incluir al creador del acuerdo
            if ($acuerdo->creador) {
                $destinatarios = $destinatarios->push($acuerdo->creador);
            }

            // Eliminar duplicados por ID
            $destinatarios = $destinatarios->unique('id');

            Notification::send($destinatarios, new AcuerdoRehabilitado($acuerdo, $motivo));
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificaciones de rehabilitación: ' . $e->getMessage());
        }
    }

    /**
     * Obtener sedes únicas
     */
    public function obtenerSedes(Request $request)
    {
        try {
            $sedes = DB::table('ordenes_historico')
                ->whereNotNull('descripcion_sede')
                ->where('descripcion_sede', '!=', '')
                ->distinct()
                ->orderBy('descripcion_sede')
                ->pluck('descripcion_sede');

            return response()->json([
                'success' => true,
                'data'    => $sedes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en obtenerSedes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Limpiar caché
     */
    public function limpiarCache()
    {
        try {
            // Limpiar caché del semáforo por sede (todos los meses/años)
            foreach (range(1, 12) as $m) {
                foreach ([date('Y'), date('Y') - 1] as $a) {
                    \Cache::store('file')->forget("ordenes_por_sede_{$m}_{$a}");
                }
            }

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
     * Obtener usuarios que han creado acuerdos para el select
     */
    public function obtenerUsuariosCreadores()
    {
        try {
            $usuarios = AcuerdoComercial::with('creador')
                ->get()
                ->pluck('creador')
                ->unique('id')
                ->filter()
                ->sortBy('name')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $usuarios->map(function ($usuario) {
                    return [
                        'id' => $usuario->id,
                        'name' => $usuario->name,
                        'email' => $usuario->email
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deshabilitar acuerdo
     */
    public function deshabilitarAcuerdo(Request $request, $id)
    {
        try {
            $acuerdo = AcuerdoComercial::findOrFail($id);

            // ✅ Verificar permisos
            $emailsAutorizados = ['smonopoli@trimaxperu.com', 'planeamiento.comercial@trimaxperu.com'];
            if (!in_array(Auth::user()->email, $emailsAutorizados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para deshabilitar acuerdos'
                ], 403);
            }

            $validated = $request->validate([
                'motivo' => 'required|string|min:10'
            ]);

            $acuerdo->update([
                'habilitado' => false,
                'motivo_deshabilitacion' => $validated['motivo'],
                'deshabilitado_at' => now(),
                'deshabilitado_por' => Auth::id()
            ]);

            // Actualizar estado
            $acuerdo->actualizarEstado();

            // Enviar notificaciones
            $this->enviarNotificacionDeshabilitacion($acuerdo, $validated['motivo']);

            return response()->json([
                'success' => true,
                'message' => 'Acuerdo deshabilitado correctamente',
                'acuerdo' => $acuerdo->load(['creador', 'validador', 'aprobador', 'deshabilitador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al deshabilitar acuerdo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extender acuerdo
     */
    public function extenderAcuerdo(Request $request, $id)
    {
        try {
            $acuerdo = AcuerdoComercial::findOrFail($id);

            // ✅ Verificar permisos
            $emailsAutorizados = ['smonopoli@trimaxperu.com', 'planeamiento.comercial@trimaxperu.com'];
            if (!in_array(Auth::user()->email, $emailsAutorizados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para extender acuerdos'
                ], 403);
            }

            $validated = $request->validate([
                'nueva_fecha_fin' => 'required|date|after:' . $acuerdo->fecha_fin,
                'motivo' => 'required|string|min:10'
            ]);

            $acuerdo->update([
                'fecha_fin' => $validated['nueva_fecha_fin'],
                'motivo_extension' => $validated['motivo'],
                'extendido_at' => now(),
                'extendido_por' => Auth::id()
            ]);

            // Actualizar estado
            $acuerdo->actualizarEstado();

            // Enviar notificaciones
            $this->enviarNotificacionExtension($acuerdo, $validated['motivo'], $validated['nueva_fecha_fin']);

            return response()->json([
                'success' => true,
                'message' => 'Acuerdo extendido correctamente',
                'acuerdo' => $acuerdo->load(['creador', 'validador', 'aprobador', 'extensor'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al extender acuerdo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar notificación de deshabilitación
     */
    private function enviarNotificacionDeshabilitacion($acuerdo, $motivo)
    {
        try {
            // Usuarios autorizados
            $destinatarios = User::whereIn('email', [
                'smonopoli@trimaxperu.com',
                'planeamiento.comercial@trimaxperu.com'
            ])->get();

            // ✅ SIEMPRE incluir al creador del acuerdo
            if ($acuerdo->creador) {
                $destinatarios = $destinatarios->push($acuerdo->creador);
            }

            // Eliminar duplicados por ID
            $destinatarios = $destinatarios->unique('id');

            Notification::send($destinatarios, new AcuerdoDeshabilitado($acuerdo, $motivo));
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificaciones de deshabilitación: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificación de extensión
     */
    private function enviarNotificacionExtension($acuerdo, $motivo, $nuevaFecha)
    {
        try {
            // Usuarios autorizados
            $destinatarios = User::whereIn('email', [
                'smonopoli@trimaxperu.com',
                'planeamiento.comercial@trimaxperu.com'
            ])->get();

            // ✅ SIEMPRE incluir al creador del acuerdo
            if ($acuerdo->creador) {
                $destinatarios = $destinatarios->push($acuerdo->creador);
            }

            // Eliminar duplicados por ID
            $destinatarios = $destinatarios->unique('id');

            Notification::send($destinatarios, new AcuerdoExtendido($acuerdo, $motivo, $nuevaFecha));
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificaciones de extensión: ' . $e->getMessage());
        }
    }

    /**
     * Editar acuerdo
     */
    public function editarAcuerdo(Request $request, $id)
    {
        try {
            $acuerdo = AcuerdoComercial::findOrFail($id);

            // Verificar permisos: solo el creador o usuarios autorizados pueden editar
            $emailsAutorizados = ['smonopoli@trimaxperu.com', 'planeamiento.comercial@trimaxperu.com'];
            if (Auth::id() !== $acuerdo->user_id && !in_array(Auth::user()->email, $emailsAutorizados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para editar este acuerdo'
                ], 403);
            }

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
                'archivos.*' => 'nullable|file|max:10240'
            ]);

            // Subir nuevos archivos si existen
            $archivosActuales = $acuerdo->archivos_adjuntos ?? [];
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $archivo) {
                    $path = $archivo->store('acuerdos/' . $acuerdo->numero_acuerdo, 'public');
                    $archivosActuales[] = [
                        'nombre' => $archivo->getClientOriginalName(),
                        'path' => $path,
                        'size' => $archivo->getSize()
                    ];
                }
            }

            // Actualizar acuerdo y resetear validaciones
            $acuerdo->update([
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
                'archivos_adjuntos' => $archivosActuales,

                // 🔥 RESETEAR VALIDACIONES
                'estado' => 'Solicitado',
                'validado' => 'Pendiente',
                'aprobado' => 'Pendiente',
                'validado_por' => null,
                'aprobado_por' => null,
                'validado_at' => null,
                'aprobado_at' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Acuerdo actualizado exitosamente. Las validaciones se han reseteado.',
                'acuerdo' => $acuerdo->load(['creador', 'validador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al editar acuerdo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al editar acuerdo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de validación (para corregir errores)
     */
    public function cambiarValidacion(Request $request, $id)
    {
        try {
            $acuerdo = AcuerdoComercial::findOrFail($id);

            // Solo planeamiento puede cambiar validación
            if (Auth::user()->email !== 'planeamiento.comercial@trimaxperu.com') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar la validación'
                ], 403);
            }

            $validated = $request->validate([
                'nuevo_estado' => 'required|in:Aprobado,Rechazado,Pendiente'
            ]);

            $acuerdo->update([
                'validado' => $validated['nuevo_estado'],
                'validado_por' => Auth::id(),
                'validado_at' => now()
            ]);

            // Actualizar estado general
            $acuerdo->actualizarEstado();

            return response()->json([
                'success' => true,
                'message' => 'Validación actualizada correctamente',
                'acuerdo' => $acuerdo->load(['creador', 'validador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cambiar validación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de aprobación (para corregir errores)
     */
    public function cambiarAprobacion(Request $request, $id)
    {
        try {
            $acuerdo = AcuerdoComercial::findOrFail($id);

            // Solo gerencia puede cambiar aprobación
            if (Auth::user()->email !== 'smonopoli@trimaxperu.com') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar la aprobación'
                ], 403);
            }

            $validated = $request->validate([
                'nuevo_estado' => 'required|in:Aprobado,Rechazado,Pendiente'
            ]);

            $acuerdo->update([
                'aprobado' => $validated['nuevo_estado'],
                'aprobado_por' => Auth::id(),
                'aprobado_at' => now()
            ]);

            // Actualizar estado general
            $acuerdo->actualizarEstado();

            return response()->json([
                'success' => true,
                'message' => 'Aprobación actualizada correctamente',
                'acuerdo' => $acuerdo->load(['creador', 'validador', 'aprobador'])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al cambiar aprobación: ' . $e->getMessage());

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

        $enTransito = 0;
        $enSede = 0;
        $facturados = 0;
        $entregados = 0;

        $importeTransito = 0;
        $importeSede = 0;

        foreach ($ordenes as $orden) {
            $ubicacion = mb_strtoupper($orden['ubicacion_orden'] ?? '');
            $estado = mb_strtoupper($orden['estado_orden'] ?? '');
            $importeOriginal = $orden['importe'] ?? null;
            $importe = $this->limpiarImporte($importeOriginal);

            if (stripos($ubicacion, 'FACTURADO') !== false || stripos($ubicacion, 'ENTREGADO') !== false) {
                $facturados++;
                // NO sumar importe aquí (ya está facturado)
            } elseif (stripos($ubicacion, 'SEDE') !== false) {
                $enSede++;

                // SOLO sumar importe si el estado es "SOLICITADO"
                if ($estado === 'SOLICITADO') {
                    $importeSede += $importe;
                }
            } elseif (stripos($ubicacion, 'TRANSITO') !== false) {
                $enTransito++;

                // SOLO sumar importe si el estado es "SOLICITADO"
                if ($estado === 'SOLICITADO') {
                    $importeTransito += $importe;
                }
            }
        }

        return [
            'total' => $total,
            'en_transito' => $enTransito,
            'en_sede' => $enSede,
            'facturados' => $facturados,
            'entregados' => $entregados,
            'disponibles_facturar' => $enSede + $enTransito,
            'importe_transito' => round($importeTransito, 2),
            'importe_sede' => round($importeSede, 2),
            'importe_total' => round($importeTransito + $importeSede, 2)
        ];
    }

    /**
     * 🔥 Limpiar importe para cálculos - CON PROTECCIÓN ANTI-FECHAS
     */
    private function limpiarImporte($importeStr)
    {
        // Si está vacío, null o es guion, retornar 0
        if (!$importeStr || $importeStr === '-' || $importeStr === '' || $importeStr === null) {
            return 0;
        }

        // Convertir a string
        $limpio = trim((string)$importeStr);

        // Si es un string vacío después del trim, retornar 0
        if ($limpio === '') {
            return 0;
        }

        if (substr_count($limpio, '/') >= 2) {
            return 0;
        }

        // Si tiene 4 dígitos consecutivos después de una barra, es fecha
        if (preg_match('/\/\d{4}/', $limpio)) {
            return 0;
        }

        $limpio = preg_replace('/[^0-9.,\-]/', '', $limpio);

        // Si tiene coma, convertirla a punto (para decimales: 2,5 -> 2.5)
        $limpio = str_replace(',', '.', $limpio);

        // Convertir a float
        $numero = floatval($limpio);

        // 🔥 VALIDACIÓN ADICIONAL: Si el número es mayor a 100,000 es sospechoso
        // (asumiendo que ninguna orden individual cuesta más de 100k)
        if ($numero > 100000) {
            return 0;
        }

        return $numero;
    }

    /**
     * Exportar a CSV
     */
    public function exportarExcel(Request $request)
    {
        try {
            ini_set('memory_limit',       '256M');
            ini_set('max_execution_time', '120');

            // Filtros opcionales
            $mes   = $request->input('mes');
            $anio  = $request->input('anio');
            $sede  = $request->input('sede');
            $estado = $request->input('estado');

            $query = DB::table('ordenes_historico')
                ->orderBy('fecha_orden')
                ->orderBy('descripcion_sede');

            if ($mes)    $query->where('mes',  (int) $mes);
            if ($anio)   $query->where('anio', (int) $anio);
            if ($sede)   $query->where('descripcion_sede', $sede);
            if ($estado) $query->where('estado_orden', strtoupper($estado));

            $filename = 'ordenes_historico_' . date('Y-m-d_His') . '.csv';

            $headers = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            ];

            $callback = function () use ($query) {
                $handle = fopen('php://output', 'w');

                // BOM para Excel
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Headers CSV
                fputcsv($handle, [
                    'Sede',
                    'N° Orden',
                    'RUC',
                    'Cliente',
                    'Diseño',
                    'Producto',
                    'Importe',
                    'Orden Compra',
                    'Fecha Orden',
                    'Hora Orden',
                    'Tipo Orden',
                    'Usuario',
                    'Estado',
                    'Ubicación',
                    'Tallado',
                    'Tratamiento',
                    'Lead Time',
                ]);

                // Streaming por chunks para no cargar todo en memoria
                $query->chunk(1000, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->descripcion_sede,
                            $row->numero_orden,
                            $row->ruc,
                            $row->cliente,
                            $row->diseno,
                            $row->descripcion_producto,
                            $row->importe,
                            $row->orden_compra,
                            $row->fecha_orden
                                ? \Carbon\Carbon::parse($row->fecha_orden)->format('d/m/Y')
                                : '',
                            $row->hora_orden,
                            $row->tipo_orden,
                            $row->nombre_usuario,
                            $row->estado_orden,
                            $row->ubicacion_orden,
                            $row->descripcion_tallado,
                            $row->tratamiento,
                            $row->lead_time,
                        ]);
                    }
                });

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Error en exportarExcel: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Dashboard consolidado de ventas de todas las sedes
     */
    public function ventasSedes()
    {
        // Verificar que sea super admin o admin
        if (!auth()->user()->puedeVerVentasConsolidadas()) {
            abort(403, 'No tienes permiso para ver las ventas consolidadas');
        }

        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
        $anioActual = Carbon::now()->year;

        $spreadsheetId = '1zQ8h0cX8YdQ4Jko69vGpDNMXMRrXD0ICCH6G4O6ubiY';

        try {
            // Configurar Google Sheets
            $client = new Client();
            $client->setApplicationName('TRIMAX Ventas');
            $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig(storage_path('app/google/service-account.json'));
            $client->setAccessType('offline');

            $service = new Sheets($client);

            // Obtener todas las sedes con sus datos
            $todasLasSedes = $this->obtenerDatosTodasLasSedes($service, $spreadsheetId, $mesActual, $anioActual);

            // Obtener años disponibles
            $aniosDisponibles = $this->obtenerAniosDisponiblesGlobal($service, $spreadsheetId);

            // Obtener datos consolidados por año
            $datosConsolidados = $this->obtenerDatosConsolidados($service, $spreadsheetId, $anioActual);

            // Obtener comparación anual
            $comparacionAnual = $this->obtenerComparacionAnual($service, $spreadsheetId);
        } catch (\Exception $e) {
            Log::error('Error en ventasSedes: ' . $e->getMessage());

            $todasLasSedes = [];
            $aniosDisponibles = [$anioActual];
            $datosConsolidados = ['meses' => [], 'ventas' => [], 'cuotas' => []];
            $comparacionAnual = ['anios' => [], 'ventas' => []];
        }

        return view('comercial.ventas-sedes', compact(
            'todasLasSedes',
            'mesActual',
            'anioActual',
            'aniosDisponibles',
            'datosConsolidados',
            'comparacionAnual'
        ));
    }

    /**
     * API para obtener datos filtrados (AJAX)
     */
    public function getVentasSedesData(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $anio = $request->input('anio', Carbon::now()->year);
        $mes = $request->input('mes', ucfirst(Carbon::now()->locale('es')->translatedFormat('F')));

        $spreadsheetId = '1zQ8h0cX8YdQ4Jko69vGpDNMXMRrXD0ICCH6G4O6ubiY';

        try {
            $client = new Client();
            $client->setApplicationName('TRIMAX Ventas');
            $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig(storage_path('app/google/service-account.json'));
            $client->setAccessType('offline');

            $service = new Sheets($client);

            $todasLasSedes = $this->obtenerDatosTodasLasSedes($service, $spreadsheetId, $mes, $anio);
            $datosConsolidados = $this->obtenerDatosConsolidados($service, $spreadsheetId, $anio);
            $comparacionAnual = $this->obtenerComparacionAnualPorMes($service, $spreadsheetId, $mes);

            return response()->json([
                'sedes' => $todasLasSedes,
                'consolidado' => $datosConsolidados,
                'comparacion' => $comparacionAnual,
                'anio' => $anio,
                'mes' => $mes
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getVentasSedesData: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener datos'], 500);
        }
    }

    /**
     * Obtener datos de todas las sedes para un mes específico
     */
    private function obtenerDatosTodasLasSedes($service, $spreadsheetId, $mes, $anio)
    {
        $range = 'Historico!A:L'; // 🔥 Antes era A:H
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        $sedes = [];

        if (empty($values)) {
            return $sedes;
        }

        foreach ($values as $index => $row) {
            if ($index == 0) continue;

            if (!empty($row[0]) && !empty($row[1]) && !empty($row[2])) {
                $sede         = trim(strtoupper($row[0]));
                $anioSheet    = trim($row[1]);
                $mesSheet     = trim(ucfirst(strtolower($row[2])));

                if ($anioSheet == $anio && $mesSheet == $mes) {
                    $ventaGeneral = $this->limpiarNumero($row[3] ?? 0);
                    $cuota        = $this->limpiarNumero($row[5] ?? 0);
                    $cumplimiento = $this->limpiarPorcentaje($row[6] ?? '0%');

                    // Columnas digitales
                    $ventaDigital     = $this->limpiarNumero($row[7] ?? 0);  // Col H
                    $ventaProyDigital = $this->limpiarNumero($row[8] ?? 0);  // Col I
                    $cuotaDigital     = $this->limpiarNumero($row[9] ?? 0);  // Col J
                    $cumCuotaDigital  = $this->limpiarPorcentaje($row[10] ?? '0%'); // Col K

                    $sedes[] = [
                        'sede'              => $sede,
                        'venta_general'     => $ventaGeneral,
                        'venta_proyectada'  => $this->limpiarNumero($row[4] ?? 0),
                        'cuota'             => $cuota,
                        'cumplimiento_cuota' => $cumplimiento,
                        'diferencia'        => $ventaGeneral - $cuota,
                        'venta_digital'     => $ventaDigital,
                        'venta_proy_digital' => $ventaProyDigital,
                        'cuota_digital'     => $cuotaDigital,
                        'cum_cuota_digital' => $cumCuotaDigital,
                    ];
                }
            }
        }

        // ─── FUSIONAR SEDES DE MONTURAS ───────────────────────────────────────
        $sedesMonturas = ['CONSULTOR DE MONTURAS 1', 'CONSULTOR DE MONTURAS 2', 'MONTURAS GENERAL'];

        $fusionada = [
            'sede'               => 'MONTURAS',
            'venta_general'      => 0,
            'venta_proyectada'   => 0,
            'cuota'              => 0,
            'cumplimiento_cuota' => 0,
            'diferencia'         => 0,
            'venta_digital'      => 0,
            'venta_proy_digital' => 0,
            'cuota_digital'      => 0,
            'cum_cuota_digital'  => 0,
        ];

        $hayMoturas = false;

        $sedes = array_filter($sedes, function ($s) use ($sedesMonturas, &$fusionada, &$hayMonturas) {
            if (in_array($s['sede'], $sedesMonturas)) {
                $fusionada['venta_general']      += $s['venta_general'];
                $fusionada['venta_proyectada']   += $s['venta_proyectada'];
                $fusionada['cuota']              += $s['cuota'];
                $fusionada['diferencia']         += $s['diferencia'];
                $fusionada['venta_digital']      += $s['venta_digital'];
                $fusionada['venta_proy_digital'] += $s['venta_proy_digital'];
                $fusionada['cuota_digital']      += $s['cuota_digital'];
                $hayMonturas = true;
                return false;
            }
            return true;
        });

        if ($hayMonturas) {
            $fusionada['cumplimiento_cuota'] = $fusionada['cuota'] > 0
                ? ($fusionada['venta_general'] / $fusionada['cuota']) * 100
                : 0;
            $fusionada['cum_cuota_digital'] = $fusionada['cuota_digital'] > 0
                ? ($fusionada['venta_digital'] / $fusionada['cuota_digital']) * 100
                : 0;

            $sedes[] = $fusionada;
        }

        $sedes = array_values($sedes);

        usort($sedes, function ($a, $b) {
            return $b['cumplimiento_cuota'] <=> $a['cumplimiento_cuota'];
        });

        return $sedes;
    }

    /**
     * Obtener datos consolidados por mes (suma de todas las sedes)
     */
    private function obtenerDatosConsolidados($service, $spreadsheetId, $anio)
    {
        $range = 'Historico!A:H';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        $ordenMeses = [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'setiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12
        ];

        $datosPorMes = [];

        foreach ($values as $index => $row) {
            if ($index == 0) continue;

            if (!empty($row[0]) && !empty($row[1]) && !empty($row[2])) {
                $anioSheet = trim($row[1]);

                if ($anioSheet == $anio) {
                    $mes = trim(ucfirst(strtolower($row[2])));
                    $mesKey = strtolower($mes);

                    if (!isset($datosPorMes[$mesKey])) {
                        $datosPorMes[$mesKey] = [
                            'mes' => $mes,
                            'orden' => $ordenMeses[$mesKey] ?? 99,
                            'ventas' => 0,
                            'cuotas' => 0,
                        ];
                    }

                    $datosPorMes[$mesKey]['ventas'] += $this->limpiarNumero($row[3] ?? 0);
                    $datosPorMes[$mesKey]['cuotas'] += $this->limpiarNumero($row[5] ?? 0);
                }
            }
        }

        // Ordenar por mes
        usort($datosPorMes, function ($a, $b) {
            return $a['orden'] <=> $b['orden'];
        });

        return [
            'meses' => array_column($datosPorMes, 'mes'),
            'ventas' => array_column($datosPorMes, 'ventas'),
            'cuotas' => array_column($datosPorMes, 'cuotas'),
        ];
    }

    /**
     * Obtener años disponibles
     */
    private function obtenerAniosDisponiblesGlobal($service, $spreadsheetId)
    {
        $range = 'Historico!B:B';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        $anios = [];

        foreach ($values as $index => $row) {
            if ($index == 0) continue;

            if (!empty($row[0]) && is_numeric($row[0])) {
                $anios[] = (int)$row[0];
            }
        }

        $anios = array_unique($anios);
        rsort($anios);

        return $anios;
    }

    /**
     * Obtener comparación anual (suma total por año)
     */
    private function obtenerComparacionAnual($service, $spreadsheetId)
    {
        $range = 'Historico!A:H';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        // Obtener mes actual
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));

        $datosPorAnio = [];

        foreach ($values as $index => $row) {
            if ($index == 0) continue;

            if (!empty($row[0]) && !empty($row[1]) && !empty($row[2]) && !empty($row[3])) {
                $anio = trim($row[1]);
                $mesSheet = trim(ucfirst(strtolower($row[2])));
                $venta = $this->limpiarNumero($row[3] ?? 0);

                // 🔥 SOLO sumar ventas del MES ACTUAL
                if ($mesSheet == $mesActual && is_numeric($anio)) {
                    if (!isset($datosPorAnio[$anio])) {
                        $datosPorAnio[$anio] = 0;
                    }
                    $datosPorAnio[$anio] += $venta;
                }
            }
        }

        ksort($datosPorAnio);

        return [
            'anios' => array_keys($datosPorAnio),
            'ventas' => array_values($datosPorAnio),
            'mes' => $mesActual
        ];
    }

    /**
     * Obtener comparación de un MES ESPECÍFICO en diferentes años (para AJAX)
     */
    private function obtenerComparacionAnualPorMes($service, $spreadsheetId, $mes)
    {
        $range = 'Historico!A:H';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        $datosPorAnio = [];

        foreach ($values as $index => $row) {
            if ($index == 0) continue;

            if (!empty($row[0]) && !empty($row[1]) && !empty($row[2]) && !empty($row[3])) {
                $anio = trim($row[1]);
                $mesSheet = trim(ucfirst(strtolower($row[2])));
                $venta = $this->limpiarNumero($row[3] ?? 0);

                // 🔥 Sumar ventas del MES RECIBIDO
                if ($mesSheet == $mes && is_numeric($anio)) {
                    if (!isset($datosPorAnio[$anio])) {
                        $datosPorAnio[$anio] = 0;
                    }
                    $datosPorAnio[$anio] += $venta;
                }
            }
        }

        ksort($datosPorAnio);

        return [
            'anios' => array_keys($datosPorAnio),
            'ventas' => array_values($datosPorAnio),
            'mes' => $mes
        ];
    }

    /**
     * Helpers para limpiar datos
     */
    private function limpiarNumero($valor)
    {
        if (empty($valor)) return 0;

        $valor = (string) $valor;
        $valor = trim($valor);

        if (strpos($valor, '.') !== false && strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (strpos($valor, ',') !== false) {
            $valor = str_replace(',', '.', $valor);
        }

        return floatval($valor);
    }

    private function limpiarPorcentaje($valor)
    {
        if (empty($valor)) return 0;

        $valor = str_replace('%', '', $valor);
        $valor = trim($valor);
        $valor = str_replace(',', '.', $valor);

        return floatval($valor);
    }
}
