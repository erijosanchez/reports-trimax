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
            }
        }
        Cache::forget("lead_time_available_years");

        return response()->json(['success' => true, 'message' => 'Caché limpiado']);
    }
}
