<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LeadTimeController extends Controller
{
    protected $sheetsService;

    public function __construct(GoogleSheetsService $sheetsService)
    {
        $this->sheetsService = $sheetsService;
    }

    
    // ════════════════════════════════════════════════════════════
    //  VISTAS
    // ════════════════════════════════════════════════════════════

    /**
     * Vista principal del Dashboard Lead Time
     */
    public function index()
    {
        if (!auth()->user()->puedeVerLeadTime()) {
            abort(403, 'No tienes permiso para ver el Lead Time');
        }

        return view('comercial.lead-time');
    }

    /**
     * Vista Lead Time Objetivo +
     */
    public function objetivoMas()
    {
        if (!auth()->user()->puedeVerLeadTime()) {
            abort(403, 'No tienes permiso para ver el Lead Time');
        }

        return view('comercial.lead-time-objetivo-mas');
    }

    /**
     * API: Obtener datos Lead Time Objetivo +
     */
    public function getObjetivoMasData(Request $request)
    {
        try {
            $year = (int) $request->get('year', Carbon::now()->year);

            $cacheKey = "lead_time_objetivo_mas_{$year}";

            $result = Cache::remember($cacheKey, 300, function () use ($year) {
                return $this->processObjetivoMasData($year);
            });

            return response()->json([
                'success' => true,
                'data'    => $result,
                'filters' => ['year' => $year],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en LeadTime getObjetivoMasData: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Vista KPI Lead Time Semanal
     */
    public function semanal()
    {
        if (!auth()->user()->puedeVerLeadTime()) {
            abort(403, 'No tienes permiso para ver el Lead Time');
        }

        return view('comercial.lead-time-semanal');
    }

    /**
     * API: Obtener datos de Lead Time filtrados
     */
    public function getData(Request $request)
    {
        try {
            $year = $request->get('year', Carbon::now()->year);
            $month = $request->get('month', Carbon::now()->month);

            $cacheKey = "lead_time_data_{$year}_{$month}";

            $result = Cache::remember($cacheKey, 300, function () use ($year, $month) {
                return $this->processLeadTimeData($year, $month);
            });

            return response()->json([
                'success' => true,
                'data' => $result,
                'filters' => [
                    'year' => (int) $year,
                    'month' => (int) $month,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en LeadTime getData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtener datos de Lead Time Semanal
     * 
     * Devuelve KPI semanal con clasificación de cumplimiento y detalle de órdenes atrasadas, filtrado por año y mes de SOLICITADO
     */
    public function getSemanalData(Request $request)
    {
        try {
            $year  = (int) $request->get('year',  Carbon::now()->year);
            $month = (int) $request->get('month', Carbon::now()->month);

            $cacheKey = "lead_time_semanal_{$year}_{$month}";

            $result = Cache::remember($cacheKey, 300, function () use ($year, $month) {
                return $this->processSemanalData($year, $month);
            });

            return response()->json([
                'success' => true,
                'data'    => $result,
                'filters' => ['year' => $year, 'month' => $month],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en LeadTime getSemanalData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesar datos para Lead Time Semanal
     */

    private function processSemanalData(int $year, int $month): array
    {
        $categorias = ['NOX', 'TD', 'DEVABLUE', 'BLANCO', 'COLOREADO'];

        // Leer hoja de Google Sheets
        $spreadsheetId = config('google.lead_time_spreadsheet_id');
        $rawData = $this->sheetsService->getSheetDataFromSpreadsheet($spreadsheetId, 'Historico');

        if (empty($rawData)) {
            return $this->emptySemanalResponse($year, $month, $categorias);
        }

        $headers = array_shift($rawData);
        $records = [];
        foreach ($rawData as $row) {
            $rec = [];
            foreach ($headers as $idx => $header) {
                $rec[trim($header)] = $row[$idx] ?? '';
            }
            $records[] = $rec;
        }

        // Inicializar todos los dias del mes seleccionado aunque no tengan datos
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $diasMap = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $fecha = Carbon::createFromDate($year, $month, $d)->format('Y-m-d');
            $diasMap[$fecha] = [
                'label'   => $d,
                'fecha'   => $fecha,
                'records' => [],
            ];
        }

        // Inicializar mapa de vencidos por dia (ordenes cuyo LEAD_TIME cayo ese dia y no se entregaron a tiempo)
        $diasVencidosMap = array_fill_keys(array_keys($diasMap), 0);

        // --- PASO 1: Filtrar registros entregados (TIME es fecha valida, no "PENDIENTE") ---
        // Solo se incluyen los del mes seleccionado segun TIME
        // La logica del KPI no se toca: sigue usando CONCLUSION para calcular cumplimiento
        $filtered = [];
        foreach ($records as $rec) {
            $time = trim($rec['TIME'] ?? '');

            // Ignorar registros sin TIME o con TIME = PENDIENTE
            if (empty($time) || strtoupper($time) === 'PENDIENTE') continue;

            try {
                $d = Carbon::parse($time);
            } catch (\Exception $e) {
                continue;
            }

            // Solo registros cuyo TIME cae dentro del anno y mes seleccionado
            if ($d->year != $year || $d->month > $month) continue;

            $filtered[] = $rec;

            // Agrupar por dia (solo los del mes exacto para el grafico diario)
            if ($d->month == $month) {
                $fechaDia = $d->format('Y-m-d');
                if (isset($diasMap[$fechaDia])) {
                    $diasMap[$fechaDia]['records'][] = $rec;
                }
            }
        }

        if (empty($filtered)) {
            return $this->emptySemanalResponse($year, $month, $categorias);
        }

        // --- PASO 2: Contar ordenes NO entregadas a tiempo por dia de LEAD_TIME ---
        // Una orden cuenta como "no entregada a tiempo" en su fecha de LEAD_TIME cuando:
        //   a) TIME = "PENDIENTE" (aun no se entrega)
        //   b) TIME es una fecha posterior a LEAD_TIME (se entrego tarde)
        // Esta logica es independiente del KPI: no modifica calcularKpiYCats()
        foreach ($records as $rec) {
            $leadTime = trim($rec['LEAD_TIME'] ?? '');
            if (empty($leadTime)) continue;

            try {
                $fechaLead = Carbon::parse($leadTime);
            } catch (\Exception $e) {
                continue;
            }

            // Solo si el LEAD_TIME cae dentro del mes seleccionado
            if ($fechaLead->year != $year || $fechaLead->month != $month) continue;

            $fechaDia = $fechaLead->format('Y-m-d');
            if (!isset($diasVencidosMap[$fechaDia])) continue;

            $timeVal   = trim($rec['TIME'] ?? '');
            $isPending = empty($timeVal) || strtoupper($timeVal) === 'PENDIENTE';

            if ($isPending) {
                // Orden aun no entregada: vencida en su LEAD_TIME
                $diasVencidosMap[$fechaDia]++;
                continue;
            }

            // Orden ya entregada: verificar si fue tarde (TIME > LEAD_TIME)
            try {
                $fechaTime = Carbon::parse($timeVal);
                if ($fechaTime->gt($fechaLead)) {
                    // Se entrego despues de la fecha prevista: igual cuenta como vencida en LEAD_TIME
                    // Nota: esta orden TAMBIEN aparece en su dia TIME como "entregada" en dayOrders
                    // Ambos conteos coexisten sin pisarse
                    $diasVencidosMap[$fechaDia]++;
                }
            } catch (\Exception $e) {
                // ignorar fechas invalidas
            }
        }

        // --- PASO 3: Calcular KPI diario, dayOrders y dayVencidos ---
        $dayKpi     = [];
        $dayOrders  = []; // total de ordenes entregadas (TIME) ese dia
        $dayVencidos = []; // total de ordenes cuyo LEAD_TIME vencio ese dia sin entrega a tiempo
        $dayData    = array_fill_keys($categorias, []);

        foreach (array_values($diasMap) as $i => $diaInfo) {
            $recs = $diaInfo['records'];

            // Cantidad de ordenes entregadas ese dia (TIME = fecha del dia)
            $dayOrders[$i] = count($recs);

            // Cantidad de ordenes vencidas ese dia (LEAD_TIME = fecha del dia, entregadas tarde o pendientes)
            $dayVencidos[$i] = $diasVencidosMap[$diaInfo['fecha']] ?? 0;

            if (empty($recs)) {
                $dayKpi[$i] = null;
                foreach ($categorias as $cat) {
                    $dayData[$cat][$i] = null;
                }
            } else {
                // KPI se calcula igual que siempre usando CONCLUSION — no se toca esta logica
                [$kpi, $porCat] = $this->calcularKpiYCats($recs, $categorias);
                $dayKpi[$i] = $kpi;
                foreach ($categorias as $cat) {
                    $dayData[$cat][$i] = $porCat[$cat];
                }
            }
        }

        $dias = array_map(fn($d) => [
            'label' => $d['label'],
            'fecha' => $d['fecha'],
        ], array_values($diasMap));

        // --- PASO 4: Construir semanas ISO y meses (igual que antes) ---
        $semanasMap = [];
        $mesesMap   = [];

        foreach ($filtered as $rec) {
            $time = $rec['TIME'] ?? '';
            if (empty($time)) continue;
            try {
                $d = Carbon::parse($time);
            } catch (\Exception $e) {
                continue;
            }

            $semNum = (int) $d->format('W');
            $mesNum = (int) $d->month;

            if (!isset($semanasMap[$semNum])) {
                $lunes   = Carbon::now()->setISODate($year, $semNum, 1)->format('Y-m-d');
                $domingo = Carbon::now()->setISODate($year, $semNum, 7)->format('Y-m-d');
                $semanasMap[$semNum] = [
                    'num'     => $semNum,
                    'label'   => "Sem $semNum",
                    'inicio'  => $lunes,
                    'fin'     => $domingo,
                    'records' => [],
                ];
            }
            $semanasMap[$semNum]['records'][] = $rec;

            if (!isset($mesesMap[$mesNum])) {
                $mesesMap[$mesNum] = [
                    'num'     => $mesNum,
                    'label'   => Carbon::createFromDate($year, $mesNum, 1)->locale('es')->isoFormat('MMM'),
                    'year'    => $year,
                    'records' => [],
                ];
            }
            $mesesMap[$mesNum]['records'][] = $rec;
        }

        ksort($semanasMap);
        ksort($mesesMap);

        $semanasIndex = array_values($semanasMap);
        $mesesIndex   = array_values($mesesMap);

        // KPI semanal — logica intacta
        $weekKpi  = [];
        $weekData = array_fill_keys($categorias, []);
        foreach ($semanasIndex as $i => $semInfo) {
            [$kpi, $porCat] = $this->calcularKpiYCats($semInfo['records'], $categorias);
            $weekKpi[$i] = $kpi;
            foreach ($categorias as $cat) {
                $weekData[$cat][$i] = $porCat[$cat];
            }
        }

        // KPI mensual — logica intacta
        $monthKpi  = [];
        $monthData = array_fill_keys($categorias, []);
        foreach ($mesesIndex as $i => $mesInfo) {
            [$kpi, $porCat] = $this->calcularKpiYCats($mesInfo['records'], $categorias);
            $monthKpi[$i] = $kpi;
            foreach ($categorias as $cat) {
                $monthData[$cat][$i] = $porCat[$cat];
            }
        }

        $semanas = array_map(fn($s) => [
            'num'    => $s['num'],
            'label'  => $s['label'],
            'inicio' => $s['inicio'],
            'fin'    => $s['fin'],
        ], $semanasIndex);

        $meses = array_map(fn($m) => [
            'num'   => $m['num'],
            'label' => $m['label'],
            'year'  => $m['year'],
        ], $mesesIndex);

        return [
            'semanas'    => $semanas,
            'meses'      => $meses,
            'weekKpi'    => $weekKpi,
            'monthKpi'   => $monthKpi,
            'weekData'   => $weekData,
            'monthData'  => $monthData,
            'cats'       => $categorias,
            'dias'       => $dias,
            'dayKpi'     => $dayKpi,
            'dayData'    => $dayData,
            'dayOrders'  => $dayOrders,   // nuevo: entregadas por dia (segun TIME)
            'dayVencidos' => $dayVencidos, // nuevo: vencidas por dia (segun LEAD_TIME)
        ];
    }

    /**
     * Dado un array de registros y las categorías,
     * devuelve [kpiGeneral, ['NOX'=>val, 'TD'=>val, ...]]
     */
    /**
     * Dado un array de registros y las categorías,
     * devuelve [kpiGeneral, ['NOX'=>val, 'TD'=>val, ...]]
     */
    private function calcularKpiYCats(array $records, array $categorias): array
    {
        $total = count($records);

        // ✅ KPI general directo — cuenta TODOS los en tiempo sin importar categoría
        $enTiempoGeneral = 0;
        foreach ($records as $r) {
            $conclusion = strtoupper(trim($r['CONCLUSION'] ?? ''));
            if ($conclusion === 'DENTRO DE TIEMPO' || $conclusion === 'EN TIEMPO') {
                $enTiempoGeneral++;
            }
        }

        // KPI por categoría
        $porCat = [];
        foreach ($categorias as $cat) {
            $catRecs = array_filter($records, function ($r) use ($cat) {
                $tipo = strtoupper(trim($r['TIPO_DE_TRABAJO'] ?? ''));
                if ($cat === 'BLANCO') return $tipo === 'BLANCO' || $tipo === 'BLANCOS';
                return $tipo === $cat;
            });

            $catTotal    = count($catRecs);
            $catEnTiempo = 0;

            foreach ($catRecs as $r) {
                $conclusion = strtoupper(trim($r['CONCLUSION'] ?? ''));
                if ($conclusion === 'DENTRO DE TIEMPO' || $conclusion === 'EN TIEMPO') {
                    $catEnTiempo++;
                }
            }

            $porCat[$cat] = $catTotal > 0
                ? round(($catEnTiempo / $catTotal) * 100, 2)
                : null;
        }

        $kpiGeneral = $total > 0 ? round(($enTiempoGeneral / $total) * 100, 2) : null;

        return [$kpiGeneral, $porCat];
    }

    /**
     * Respuesta vacía para cuando no hay datos semanales
     */
    private function emptySemanalResponse(int $year, int $month, array $categorias): array
    {
        return [
            'semanas'   => [],
            'meses'     => [],
            'dias'      => [],
            'weekKpi'   => [],
            'monthKpi'  => [],
            'dayKpi'   => [],
            'weekData'  => array_fill_keys($categorias, []),
            'monthData' => array_fill_keys($categorias, []),
            'dayData'  => array_fill_keys($categorias, []),
            'cats'      => $categorias,
            'dayOrders' => [], // nuevo: entregadas por dia
            'dayVencidos' => [], // nuevo: vencidas por dia 
        ];
    }

    /**
     * Procesar datos del Sheet de Lead Time
     */
    private function processLeadTimeData($year, $month)
    {
        $spreadsheetId = config('google.lead_time_spreadsheet_id');
        $rawData = $this->sheetsService->getSheetDataFromSpreadsheet($spreadsheetId, 'Historico');

        if (empty($rawData)) {
            return $this->emptyResponse();
        }

        $headers = array_shift($rawData);
        $records = [];

        foreach ($rawData as $row) {
            $record = [];
            foreach ($headers as $index => $header) {
                $record[trim($header)] = $row[$index] ?? '';
            }
            $records[] = $record;
        }

        // ✅ Filtrar por TIME
        $filtered = array_filter($records, function ($record) use ($year, $month) {
            $time = $record['TIME'] ?? '';
            if (empty($time)) return false;
            try {
                $date = Carbon::parse($time);
                return $date->year == $year && $date->month == $month;
            } catch (\Exception $e) {
                return false;
            }
        });

        $filtered = array_values($filtered);

        if (empty($filtered)) {
            return $this->emptyResponse();
        }

        $categorias = ['NOX', 'TD', 'DEVABLUE', 'BLANCO', 'COLOREADO'];

        $nombresDisplay = [
            'NOX'       => 'NOX',
            'TD'        => 'TRIDUREX',
            'DEVABLUE'  => 'DEVABLUE',
            'BLANCO'    => 'BLANCOS',
            'COLOREADO' => 'COLOREADO',
        ];

        $resultados  = [];
        $totalGeneral = count($filtered);

        // ✅ totalEnTiempo directo sobre todos los filtrados
        $totalEnTiempo = 0;
        foreach ($filtered as $record) {
            $conclusion = strtoupper(trim($record['CONCLUSION'] ?? ''));
            if ($conclusion === 'DENTRO DE TIEMPO' || $conclusion === 'EN TIEMPO') {
                $totalEnTiempo++;
            }
        }

        foreach ($categorias as $cat) {
            $datosCategoria = array_filter($filtered, function ($r) use ($cat) {
                $tipo = strtoupper(trim($r['TIPO_DE_TRABAJO'] ?? ''));
                if ($cat === 'BLANCO') {
                    return $tipo === 'BLANCO' || $tipo === 'BLANCOS';
                }
                return $tipo === $cat;
            });

            $totalCat = count($datosCategoria);

            if ($totalCat === 0) {
                $resultados[$cat] = [
                    'nombre'                  => $nombresDisplay[$cat],
                    'total'                   => 0,
                    'porcentaje_cumplimiento' => 0,
                    'barras'                  => [],
                ];
                continue;
            }

            $clasificacion = $this->clasificarRegistros($datosCategoria);

            $enTiempo = 0;
            foreach ($clasificacion as $label => $count) {
                if (!str_starts_with($label, 'Después')) {
                    $enTiempo += $count;
                }
            }

            $porcentajeCumplimiento = round(($enTiempo / $totalCat) * 100, 2);

            $barras = [];
            foreach ($clasificacion as $label => $count) {
                $porcentaje = round(($count / $totalCat) * 100, 2);
                $esAtraso   = str_starts_with($label, 'Después');
                $barras[]   = [
                    'label'    => $label,
                    'cantidad' => $count,
                    'porcentaje' => $porcentaje,
                    'tipo'     => $esAtraso ? 'atraso' : 'cumplimiento',
                ];
            }

            $resultados[$cat] = [
                'nombre'                  => $nombresDisplay[$cat],
                'total'                   => $totalCat,
                'porcentaje_cumplimiento' => $porcentajeCumplimiento,
                'barras'                  => $barras,
            ];
        }

        $porcentajeGeneral = $totalGeneral > 0
            ? round(($totalEnTiempo / $totalGeneral) * 100, 2)
            : 0;

        $ordenesAtrasadas = [];
        foreach ($filtered as $record) {
            $conclusion = strtoupper(trim($record['CONCLUSION'] ?? ''));
            $tipo       = strtoupper(trim($record['TIPO_DE_TRABAJO'] ?? ''));
            $categoriasValidas = ['NOX', 'TD', 'DEVABLUE', 'BLANCO', 'COLOREADO'];

            if ($conclusion === 'FUERA DE TIEMPO' && in_array($tipo, $categoriasValidas)) {
                $ordenesAtrasadas[] = [
                    'numero_orden'    => $record['NUMERO_ORDEN'] ?? '',
                    'sede'            => $record['SEDE'] ?? '',
                    'tipo'            => $record['TIPO'] ?? '',
                    'producto'        => $record['PRODUCTO'] ?? '',
                    'tipo_de_trabajo' => $record['TIPO_DE_TRABAJO'] ?? '',
                    'meta'            => intval($record['META'] ?? 0),
                    'solicitado'      => $record['SOLICITADO'] ?? '',
                    'lead_time'       => $record['LEAD_TIME'] ?? '',
                    'time'            => $record['TIME'] ?? '',
                    'atraso'          => intval($record['ATRASO'] ?? 0),
                    'conclusion'      => $record['CONCLUSION'] ?? '',
                ];
            }
        }

        usort($ordenesAtrasadas, function ($a, $b) {
            return $a['atraso'] - $b['atraso'];
        });

        return [
            'general' => [
                'total'      => $totalGeneral,
                'porcentaje' => $porcentajeGeneral,
                'total_en_tiempo' => $totalEnTiempo,
                'total_fuera'     => $totalGeneral - $totalEnTiempo,
            ],
            'categorias'        => $resultados,
            'ordenes_atrasadas' => $ordenesAtrasadas,
        ];
    }

    /**
     * Clasificar registros según CONCLUSION y ATRASO
     */
    private function clasificarRegistros($registros)
    {
        $clasificacion = [];

        foreach ($registros as $record) {
            $conclusion = strtoupper(trim($record['CONCLUSION'] ?? ''));
            $atraso = intval($record['ATRASO'] ?? 0);

            if ($conclusion === 'DENTRO DE TIEMPO' || $conclusion === 'EN TIEMPO') {
                $label = $this->getLabelDentroTiempo($record);
            } elseif ($conclusion === 'FUERA DE TIEMPO') {
                $diasAtraso = abs($atraso);
                if ($diasAtraso === 0) $diasAtraso = 1;

                if ($diasAtraso === 1) {
                    $label = 'Después de 1 día';
                } elseif ($diasAtraso === 2) {
                    $label = 'Después de 2 días';
                } elseif ($diasAtraso === 3) {
                    $label = 'Después de 3 días';
                } else {
                    $label = 'Después de 3 días a más';
                }
            } else {
                $label = 'En tiempo';
            }

            if (!isset($clasificacion[$label])) {
                $clasificacion[$label] = 0;
            }
            $clasificacion[$label]++;
        }

        // Ordenar según el orden visual del PPT
        $ordenPrioritario = [
            'El mismo día',
            'Al día Siguiente',
            'En 2 días',
            'En 3 días',
            'En tiempo',
            'Después de 1 día',
            'Después de 2 días',
            'Después de 3 días',
            'Después de 3 días a más',
        ];

        $ordenado = [];
        foreach ($ordenPrioritario as $key) {
            if (isset($clasificacion[$key]) && $clasificacion[$key] > 0) {
                $ordenado[$key] = $clasificacion[$key];
            }
        }

        return $ordenado;
    }

    /**
     * Determinar label para registros "DENTRO DE TIEMPO"
     */
    private function getLabelDentroTiempo($record)
    {
        try {
            $solicitado = $record['SOLICITADO'] ?? '';
            $time = $record['TIME'] ?? '';
            $meta = intval($record['META'] ?? 0);

            if (empty($solicitado) || empty($time)) {
                return 'En tiempo';
            }

            $fechaSolicitado = Carbon::parse($solicitado);
            $fechaEntrega = Carbon::parse($time);
            $diasEntrega = $fechaSolicitado->diffInDays($fechaEntrega);

            if ($diasEntrega === 0) {
                return 'El mismo día';
            }

            if ($meta > 1 && $diasEntrega === 1) {
                return 'Al día Siguiente';
            }

            if ($meta > 2 && $diasEntrega <= 2) {
                return 'En 2 días';
            }

            if ($meta > 3 && $diasEntrega <= 3) {
                return 'En 3 días';
            }

            return 'En tiempo';
        } catch (\Exception $e) {
            return 'En tiempo';
        }
    }

    /**
     * Procesar datos para Lead Time Objetivo +
     * Muestra órdenes FUERA DE TIEMPO agrupadas por días de atraso y semana del mes.
     */
    private function processObjetivoMasData(int $year): array
    {
        $categorias = ['NOX', 'TD', 'DEVABLUE', 'BLANCO', 'COLOREADO'];

        $spreadsheetId = config('google.lead_time_spreadsheet_id');
        $rawData = $this->sheetsService->getSheetDataFromSpreadsheet($spreadsheetId, 'Historico');

        if (empty($rawData)) {
            return $this->emptyObjetivoMasResponse($categorias);
        }

        $headers = array_shift($rawData);
        $records = [];
        foreach ($rawData as $row) {
            $rec = [];
            foreach ($headers as $idx => $header) {
                $rec[trim($header)] = $row[$idx] ?? '';
            }
            $records[] = $rec;
        }

        // Solo FUERA DE TIEMPO para el año completo (hasta hoy)
        $today = Carbon::today();
        $filtered = array_values(array_filter($records, function ($rec) use ($year, $today) {
            $conclusion = strtoupper(trim($rec['CONCLUSION'] ?? ''));
            if ($conclusion !== 'FUERA DE TIEMPO') return false;
            $time = $rec['TIME'] ?? '';
            if (empty($time)) return false;
            try {
                $d = Carbon::parse($time);
                return $d->year == $year && $d->lte($today);
            } catch (\Exception $e) {
                return false;
            }
        }));

        if (empty($filtered)) {
            return $this->emptyObjetivoMasResponse($categorias);
        }

        // ── Construir semanas ISO que aparecen en los datos ────────
        // En lugar de iterar días del año, usamos las semanas reales de los registros
        $semanasMap = [];

        foreach ($filtered as $rec) {
            $time = $rec['TIME'] ?? '';
            if (empty($time)) continue;
            try {
                $d      = Carbon::parse($time);
                $semNum = (int) $d->format('W');

                if (!isset($semanasMap[$semNum])) {
                    $lunes   = Carbon::now()->setISODate($year, $semNum, 1);
                    $domingo = Carbon::now()->setISODate($year, $semNum, 7);
                    $semanasMap[$semNum] = [
                        'num'   => $semNum,
                        'label' => "Semana $semNum",
                        'rango' => $lunes->format('d/m') . ' – ' . $domingo->format('d/m'),
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        ksort($semanasMap);
        $semNums   = array_keys($semanasMap);
        $maxAtraso = 10;

        // Closure que construye la tabla para un conjunto de registros
        $buildTable = function (array $recs) use ($semanasMap, $semNums, $maxAtraso) {
            $data       = [];
            $weekTotals = array_fill_keys($semNums, 0);

            for ($a = 1; $a <= $maxAtraso; $a++) {
                $data[$a] = array_fill_keys($semNums, 0);
            }
            $data['mas'] = array_fill_keys($semNums, 0);

            foreach ($recs as $rec) {
                $atraso = abs((int) ($rec['ATRASO'] ?? 0));
                if ($atraso === 0) $atraso = 1;

                try {
                    $d      = Carbon::parse($rec['TIME'] ?? '');
                    $semNum = (int) $d->format('W');
                } catch (\Exception $e) {
                    continue;
                }

                if (!isset($semanasMap[$semNum])) continue;

                $key = $atraso <= $maxAtraso ? $atraso : 'mas';
                $data[$key][$semNum]++;
                $weekTotals[$semNum]++;
            }

            $grandTotal = array_sum($weekTotals);

            // Filas -1 a -10
            $filas = [];
            for ($a = 1; $a <= $maxAtraso; $a++) {
                $acumCant = 0;
                $semRow   = [];
                foreach ($semNums as $w) {
                    $cant = $data[$a][$w];
                    $pct  = $weekTotals[$w] > 0 ? round($cant / $weekTotals[$w] * 100) : 0;
                    $semRow[$w] = ['cant' => $cant, 'pct' => $pct];
                    $acumCant  += $cant;
                }
                $acumPct = $grandTotal > 0 ? round($acumCant / $grandTotal * 100) : 0;
                $filas[] = [
                    'label'   => "-$a",
                    'atraso'  => $a,
                    'semanas' => $semRow,
                    'acum'    => ['cant' => $acumCant, 'pct' => $acumPct],
                ];
            }

            // Fila >10
            $masCant = array_sum($data['mas']);
            if ($masCant > 0) {
                $acumCant = 0;
                $semRow   = [];
                foreach ($semNums as $w) {
                    $cant = $data['mas'][$w];
                    $pct  = $weekTotals[$w] > 0 ? round($cant / $weekTotals[$w] * 100) : 0;
                    $semRow[$w] = ['cant' => $cant, 'pct' => $pct];
                    $acumCant  += $cant;
                }
                $acumPct = $grandTotal > 0 ? round($acumCant / $grandTotal * 100) : 0;
                $filas[] = [
                    'label'   => '>10',
                    'atraso'  => 11,
                    'semanas' => $semRow,
                    'acum'    => ['cant' => $acumCant, 'pct' => $acumPct],
                ];
            }

            // Totales por semana
            $totalRow = [];
            foreach ($semNums as $w) {
                $totalRow[$w] = ['cant' => $weekTotals[$w], 'pct' => 100];
            }

            return [
                'filas'   => $filas,
                'totales' => [
                    'semanas' => $totalRow,
                    'acum'    => ['cant' => $grandTotal, 'pct' => 100],
                ],
            ];
        };

        // General
        $general = $buildTable($filtered);

        // Por categoría
        $cats = [];
        $nombresDisplay = [
            'NOX'       => 'NOX',
            'TD'        => 'TRIDUREX',
            'DEVABLUE'  => 'DEVABLUE',
            'BLANCO'    => 'BLANCOS',
            'COLOREADO' => 'COLOREADO',
        ];
        foreach ($categorias as $cat) {
            $catRecs = array_values(array_filter($filtered, function ($r) use ($cat) {
                $tipo = strtoupper(trim($r['TIPO_DE_TRABAJO'] ?? ''));
                if ($cat === 'BLANCO') return $tipo === 'BLANCO' || $tipo === 'BLANCOS';
                return $tipo === $cat;
            }));
            $cats[$cat] = array_merge($buildTable($catRecs), ['nombre' => $nombresDisplay[$cat]]);
        }

        return [
            'semanas'    => array_values($semanasMap),
            'general'    => $general,
            'categorias' => $cats,
        ];
    }
    /**
     * Respuesta vacía para Lead Time Objetivo +
     */
    private function emptyObjetivoMasResponse(array $categorias): array
    {
        $empty = ['filas' => [], 'totales' => ['semanas' => [], 'acum' => ['cant' => 0, 'pct' => 0]]];
        return [
            'semanas'    => [],
            'general'    => $empty,
            'categorias' => array_fill_keys($categorias, array_merge($empty, ['nombre' => ''])),
        ];
    }

    /**
     * Respuesta vacía
     */
    private function emptyResponse()
    {
        return [
            'general' => [
                'total' => 0,
                'porcentaje' => 0,
            ],
            'categorias' => [],
            'ordenes_atrasadas' => [],
        ];
    }

    /**
     * API: Obtener años disponibles
     */
    public function getAvailableYears()
    {
        try {
            $cacheKey = "lead_time_available_years";

            $years = Cache::remember($cacheKey, 600, function () {
                $spreadsheetId = config('google.lead_time_spreadsheet_id');
                $rawData = $this->sheetsService->getSheetDataFromSpreadsheet($spreadsheetId, 'Historico');

                if (empty($rawData)) return [Carbon::now()->year];

                $headers = array_shift($rawData);
                $solicitadoIdx = array_search('SOLICITADO', $headers);

                if ($solicitadoIdx === false) return [Carbon::now()->year];

                $years = [];
                foreach ($rawData as $row) {
                    $val = $row[$solicitadoIdx] ?? '';
                    if (!empty($val)) {
                        try {
                            $y = Carbon::parse($val)->year;
                            $years[$y] = true;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }

                $years = array_keys($years);
                sort($years);
                return $years;
            });

            return response()->json(['success' => true, 'years' => $years]);
        } catch (\Exception $e) {
            return response()->json(['success' => true, 'years' => [Carbon::now()->year]]);
        }
    }

    /**
     * API: Limpiar caché
     */
    public function clearCache()
    {
        for ($m = 1; $m <= 12; $m++) {
            for ($y = 2024; $y <= 2027; $y++) {
                Cache::forget("lead_time_data_{$y}_{$m}");
                Cache::forget("lead_time_semanal_{$y}_{$m}");
                Cache::forget("lead_time_objetivo_mas_{$y}_{$m}");
            }
        }
        Cache::forget("lead_time_available_years");

        return response()->json(['success' => true, 'message' => 'Caché limpiado']);
    }
}
