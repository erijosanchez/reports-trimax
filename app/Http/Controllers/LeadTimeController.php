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

        // ── 1. Leer hoja ────────────────────────────────────────
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

        // ── 2. Filtrar: solo registros hasta el mes indicado ────
        // Para semanas necesitamos todo el año hasta ese mes
        $filtered = array_values(array_filter($records, function ($rec) use ($year, $month) {
            $sol = $rec['SOLICITADO'] ?? '';
            if (empty($sol)) return false;
            try {
                $d = Carbon::parse($sol);
                return $d->year == $year && $d->month <= $month;
            } catch (\Exception $e) {
                return false;
            }
        }));

        if (empty($filtered)) {
            return $this->emptySemanalResponse($year, $month, $categorias);
        }

        // ── 3. Construir catálogo de semanas ISO del año ────────
        // Semana 1 = primera semana con al menos 4 días en enero (ISO 8601)
        // Pero para consistencia usamos semanas del año calendario simple:
        // sem_num = número de semana del año (1-53) con Carbon::weekOfYear
        $semanasMap = [];   // sem_num => ['inicio', 'fin', 'registros']
        $mesesMap   = [];   // mes_num  => ['registros']

        foreach ($filtered as $rec) {
            $sol = $rec['SOLICITADO'] ?? '';
            try {
                $d = Carbon::parse($sol);
            } catch (\Exception $e) {
                continue;
            }

            // Semana del año (1-53)
            $semNum = (int) $d->format('W');  // ISO week
            $mesNum = (int) $d->month;

            // Registrar semana
            if (!isset($semanasMap[$semNum])) {
                // Calcular lunes y domingo de esa semana ISO
                $lunes   = Carbon::now()->setISODate($year, $semNum, 1)->format('Y-m-d');
                $domingo = Carbon::now()->setISODate($year, $semNum, 7)->format('Y-m-d');
                $semanasMap[$semNum] = [
                    'num'    => $semNum,
                    'label'  => "Sem $semNum",
                    'inicio' => $lunes,
                    'fin'    => $domingo,
                    'records' => [],
                ];
            }
            $semanasMap[$semNum]['records'][] = $rec;

            // Registrar mes
            if (!isset($mesesMap[$mesNum])) {
                $mesesMap[$mesNum] = [
                    'num'    => $mesNum,
                    'label'  => Carbon::createFromDate($year, $mesNum, 1)->locale('es')->isoFormat('MMM'),
                    'year'   => $year,
                    'records' => [],
                ];
            }
            $mesesMap[$mesNum]['records'][] = $rec;
        }

        ksort($semanasMap);
        ksort($mesesMap);

        // ── 4. Calcular KPI por semana ──────────────────────────
        $semanasIndex = array_values($semanasMap);   // índice 0,1,2…
        $mesesIndex   = array_values($mesesMap);

        $weekKpi  = [];
        $weekData = array_fill_keys($categorias, []);

        foreach ($semanasIndex as $i => $semInfo) {
            $recs = $semInfo['records'];
            [$kpi, $porCat] = $this->calcularKpiYCats($recs, $categorias);
            $weekKpi[$i] = $kpi;
            foreach ($categorias as $cat) {
                $weekData[$cat][$i] = $porCat[$cat];
            }
        }

        $monthKpi  = [];
        $monthData = array_fill_keys($categorias, []);

        foreach ($mesesIndex as $i => $mesInfo) {
            $recs = $mesInfo['records'];
            [$kpi, $porCat] = $this->calcularKpiYCats($recs, $categorias);
            $monthKpi[$i] = $kpi;
            foreach ($categorias as $cat) {
                $monthData[$cat][$i] = $porCat[$cat];
            }
        }

        // ── 5. Limpiar 'records' de la respuesta (no necesarios en JSON) ──
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
            'semanas'   => $semanas,
            'meses'     => $meses,
            'weekKpi'   => $weekKpi,
            'monthKpi'  => $monthKpi,
            'weekData'  => $weekData,
            'monthData' => $monthData,
            'cats'      => $categorias,
        ];
    }

    /**
     * Dado un array de registros y las categorías,
     * devuelve [kpiGeneral, ['NOX'=>val, 'TD'=>val, ...]]
     */
    private function calcularKpiYCats(array $records, array $categorias): array
    {
        $total      = count($records);
        $enTiempo   = 0;
        $porCat     = [];

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

            $enTiempo += $catEnTiempo;

            $porCat[$cat] = $catTotal > 0
                ? round(($catEnTiempo / $catTotal) * 100, 2)
                : null;   // null = sin datos esa semana para esa cat
        }

        $kpiGeneral = $total > 0 ? round(($enTiempo / $total) * 100, 2) : null;

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
            'weekKpi'   => [],
            'monthKpi'  => [],
            'weekData'  => array_fill_keys($categorias, []),
            'monthData' => array_fill_keys($categorias, []),
            'cats'      => $categorias,
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

        // Parsear datos con headers
        $headers = array_shift($rawData);
        $records = [];

        foreach ($rawData as $row) {
            $record = [];
            foreach ($headers as $index => $header) {
                $record[trim($header)] = $row[$index] ?? '';
            }
            $records[] = $record;
        }

        // Filtrar por año y mes usando SOLICITADO (formato: YYYY-MM-DD)
        $filtered = array_filter($records, function ($record) use ($year, $month) {
            $solicitado = $record['SOLICITADO'] ?? '';
            if (empty($solicitado)) return false;

            try {
                $date = Carbon::parse($solicitado);
                return $date->year == $year && $date->month == $month;
            } catch (\Exception $e) {
                return false;
            }
        });

        $filtered = array_values($filtered);

        if (empty($filtered)) {
            return $this->emptyResponse();
        }

        // Categorías de TIPO_DE_TRABAJO
        $categorias = ['NOX', 'TD', 'DEVABLUE', 'BLANCO', 'COLOREADO'];

        $nombresDisplay = [
            'NOX' => 'NOX',
            'TD' => 'TRIDUREX',
            'DEVABLUE' => 'DEVABLUE',
            'BLANCO' => 'BLANCOS',
            'COLOREADO' => 'COLOREADO',

        ];

        $resultados = [];
        $totalGeneral = count($filtered);
        $totalEnTiempo = 0;

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
                    'nombre' => $nombresDisplay[$cat],
                    'total' => 0,
                    'porcentaje_cumplimiento' => 0,
                    'barras' => [],
                ];
                continue;
            }

            // Clasificar registros en categorías del gráfico
            $clasificacion = $this->clasificarRegistros($datosCategoria);

            // Calcular cumplimiento (todo lo que NO es "Después de...")
            $enTiempo = 0;
            foreach ($clasificacion as $label => $count) {
                if (!str_starts_with($label, 'Después')) {
                    $enTiempo += $count;
                }
            }

            $totalEnTiempo += $enTiempo;
            $porcentajeCumplimiento = round(($enTiempo / $totalCat) * 100, 2);

            // Construir barras
            $barras = [];
            foreach ($clasificacion as $label => $count) {
                $porcentaje = round(($count / $totalCat) * 100, 2);
                $esAtraso = str_starts_with($label, 'Después');
                $barras[] = [
                    'label' => $label,
                    'cantidad' => $count,
                    'porcentaje' => $porcentaje,
                    'tipo' => $esAtraso ? 'atraso' : 'cumplimiento',
                ];
            }

            $resultados[$cat] = [
                'nombre' => $nombresDisplay[$cat],
                'total' => $totalCat,
                'porcentaje_cumplimiento' => $porcentajeCumplimiento,
                'barras' => $barras,
            ];
        }

        $porcentajeGeneral = $totalGeneral > 0 ? round(($totalEnTiempo / $totalGeneral) * 100, 2) : 0;

        $ordenesAtrasadas = [];

        foreach ($filtered as $record) {
            $conclusion = strtoupper(trim($record['CONCLUSION'] ?? ''));
            $tipo = strtoupper(trim($record['TIPO_DE_TRABAJO'] ?? ''));
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

        // Ordenar: mayor atraso primero (atraso negativo, menor valor = más atraso)
        usort($ordenesAtrasadas, function ($a, $b) {
            return $a['atraso'] - $b['atraso'];
        });

        return [
            'general' => [
                'total' => $totalGeneral,
                'porcentaje' => $porcentajeGeneral,
            ],
            'categorias' => $resultados,
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
            }
        }
        Cache::forget("lead_time_available_years");

        return response()->json(['success' => true, 'message' => 'Caché limpiado']);
    }
}
