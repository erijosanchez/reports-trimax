<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AsignacionBasesService
{
    protected int $cacheTtl = 1800; // 30 minutos

    // ──────────────────────────────────────────────────────────────────────────
    // AÑOS DISPONIBLES
    // ──────────────────────────────────────────────────────────────────────────

    public function getAniosDisponibles(): array
    {
        return Cache::remember('asignacion_anios', $this->cacheTtl, function () {
            $anios = DB::table('asignacion_bases')
                ->distinct()
                ->orderByDesc('anio')
                ->pluck('anio')
                ->toArray();

            return $anios ?: [now()->year];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EVOLUTIVO SEMANAL
    // ──────────────────────────────────────────────────────────────────────────

    public function getEvolutivoSemanal(int $anio): array
    {
        return Cache::remember("asignacion_evolutivo_semanal_{$anio}", $this->cacheTtl, function () use ($anio) {

            // Traer agrupado por fecha y estado directo desde MySQL
            $rows = DB::table('asignacion_bases')
                ->select(
                    'fecha_asignacion',
                    'estado_asignacion',
                    DB::raw('SUM(cantidad) as total_cant'),
                    DB::raw('SUM(precio * cantidad) as total_precio')
                )
                ->where('anio', $anio)
                ->groupBy('fecha_asignacion', 'estado_asignacion')
                ->orderBy('fecha_asignacion')
                ->get();

            $semanas = [];

            foreach ($rows as $row) {
                $semana = (int) Carbon::parse($row->fecha_asignacion)->isoWeek();
                $estado = $row->estado_asignacion;

                if (!isset($semanas[$semana])) {
                    $semanas[$semana] = [
                        'semana'     => $semana,
                        'label'      => 'S' . $semana,
                        'R_cantidad' => 0,
                        'N_cantidad' => 0,
                        'R_precio'   => 0,
                        'N_precio'   => 0,
                    ];
                }

                if ($estado === 'R') {
                    $semanas[$semana]['R_cantidad'] += (int)   $row->total_cant;
                    $semanas[$semana]['R_precio']   += (float) $row->total_precio;
                } else {
                    $semanas[$semana]['N_cantidad'] += (int)   $row->total_cant;
                    $semanas[$semana]['N_precio']   += (float) $row->total_precio;
                }
            }

            ksort($semanas);

            $result = [];
            foreach ($semanas as $s) {
                $kpi = $s['N_cantidad'] > 0
                    ? round(($s['R_cantidad'] / $s['N_cantidad']) * 100, 1)
                    : 0;

                $result[] = array_merge($s, [
                    'kpi'      => $kpi,
                    'R_precio' => round($s['R_precio'], 2),
                    'N_precio' => round($s['N_precio'], 2),
                    'total'    => $s['R_cantidad'] + $s['N_cantidad'],
                ]);
            }

            $totalR = array_sum(array_column($result, 'R_cantidad'));
            $totalN = array_sum(array_column($result, 'N_cantidad'));

            return [
                'semanas'        => $result,
                'total_R'        => $totalR,
                'total_N'        => $totalN,
                'total_kpi'      => $totalN > 0 ? round(($totalR / $totalN) * 100, 1) : 0,
                'total_R_precio' => round(array_sum(array_column($result, 'R_precio')), 2),
                'total_N_precio' => round(array_sum(array_column($result, 'N_precio')), 2),
            ];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EVOLUTIVO MENSUAL
    // ──────────────────────────────────────────────────────────────────────────

    public function getEvolutivoMensual(int $anio): array
    {
        return Cache::remember("asignacion_evolutivo_mensual_{$anio}", $this->cacheTtl, function () use ($anio) {

            $mesesNombres = [
                1 => 'Enero',
                2 => 'Febrero',
                3 => 'Marzo',
                4 => 'Abril',
                5 => 'Mayo',
                6 => 'Junio',
                7 => 'Julio',
                8 => 'Agosto',
                9 => 'Setiembre',
                10 => 'Octubre',
                11 => 'Noviembre',
                12 => 'Diciembre',
            ];

            $rows = DB::table('asignacion_bases')
                ->select(
                    'mes',
                    'estado_asignacion',
                    DB::raw('SUM(cantidad) as total_cant'),
                    DB::raw('SUM(precio * cantidad) as total_precio')
                )
                ->where('anio', $anio)
                ->groupBy('mes', 'estado_asignacion')
                ->orderBy('mes')
                ->get();

            $meses = array_fill_keys(range(1, 12), [
                'R_cantidad' => 0,
                'N_cantidad' => 0,
                'R_precio'   => 0,
                'N_precio'   => 0,
            ]);

            foreach ($rows as $row) {
                $m = (int) $row->mes;
                if ($row->estado_asignacion === 'R') {
                    $meses[$m]['R_cantidad'] += (int)   $row->total_cant;
                    $meses[$m]['R_precio']   += (float) $row->total_precio;
                } else {
                    $meses[$m]['N_cantidad'] += (int)   $row->total_cant;
                    $meses[$m]['N_precio']   += (float) $row->total_precio;
                }
            }

            $result = [];
            foreach ($meses as $num => $m) {
                $kpi = $m['N_cantidad'] > 0
                    ? round(($m['R_cantidad'] / $m['N_cantidad']) * 100, 1)
                    : 0;

                $result[] = [
                    'mes'        => $num,
                    'label'      => $mesesNombres[$num],
                    'R_cantidad' => $m['R_cantidad'],
                    'N_cantidad' => $m['N_cantidad'],
                    'kpi'        => $kpi,
                    'R_precio'   => round($m['R_precio'], 2),
                    'N_precio'   => round($m['N_precio'], 2),
                ];
            }

            $totalR = array_sum(array_column($result, 'R_cantidad'));
            $totalN = array_sum(array_column($result, 'N_cantidad'));

            return [
                'meses'          => $result,
                'total_R'        => $totalR,
                'total_N'        => $totalN,
                'total_kpi'      => $totalN > 0 ? round(($totalR / $totalN) * 100, 1) : 0,
                'total_R_precio' => round(array_sum(array_column($result, 'R_precio')), 2),
                'total_N_precio' => round(array_sum(array_column($result, 'N_precio')), 2),
            ];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GRÁFICOS
    // ──────────────────────────────────────────────────────────────────────────

    public function getGraficoLinealDiario(int $anio, int $mes): array
    {
        return Cache::remember("asignacion_grafico_diario_{$anio}_{$mes}", $this->cacheTtl, function () use ($anio, $mes) {

            $rows = DB::table('asignacion_bases')
                ->select('fecha_asignacion', DB::raw('SUM(cantidad) as total'))
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->where('estado_asignacion', 'R')
                ->groupBy('fecha_asignacion')
                ->orderBy('fecha_asignacion')
                ->get();

            return [
                'labels' => $rows->map(fn($r) => Carbon::parse($r->fecha_asignacion)->format('d/m/Y'))->toArray(),
                'data'   => $rows->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            ];
        });
    }


    public function getGraficoBarrasMensual(int $anio): array
    {
        return Cache::remember("asignacion_grafico_barras_{$anio}", $this->cacheTtl, function () use ($anio) {

            $mesesNombres = [
                1 => 'Ene',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Abr',
                5 => 'May',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Ago',
                9 => 'Set',
                10 => 'Oct',
                11 => 'Nov',
                12 => 'Dic',
            ];

            $rows = DB::table('asignacion_bases')
                ->select('mes', DB::raw('SUM(precio * cantidad) as total_precio'))
                ->where('anio', $anio)
                ->where('estado_asignacion', 'R')
                ->whereNotNull('precio')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get()
                ->filter(fn($r) => $r->total_precio > 0);

            return [
                'labels' => $rows->map(fn($r) => $mesesNombres[(int) $r->mes] ?? '')->values()->toArray(),
                'data'   => $rows->map(fn($r) => round((float) $r->total_precio, 2))->values()->toArray(),
            ];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DEMANDA SEMANAL
    // ──────────────────────────────────────────────────────────────────────────

    public function getDemandaSemanal(int $anio, int $mes): array
    {
        return Cache::remember("asignacion_demanda_semanal_{$anio}_{$mes}", $this->cacheTtl, function () use ($anio, $mes) {

            $rows = DB::table('asignacion_bases')
                ->select(
                    'descripcion_base',
                    'descripcion_art',
                    'fecha_asignacion',
                    DB::raw('SUM(cantidad) as total_cant')
                )
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->groupBy('descripcion_base', 'descripcion_art', 'fecha_asignacion')
                ->orderBy('fecha_asignacion')
                ->get();

            $productos      = [];
            $totalPorSemana = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

            foreach ($rows as $row) {
                $dia    = (int) Carbon::parse($row->fecha_asignacion)->format('j');
                $semana = (int) ceil($dia / 7);
                if ($semana > 5) $semana = 5;

                $key = ($row->descripcion_base ?? '') . '|' . ($row->descripcion_art ?? '');

                if (!isset($productos[$key])) {
                    $productos[$key] = [
                        'codigo'      => $row->descripcion_base ?? '',   // Código Base = descripcion_base
                        'descripcion' => $row->descripcion_art  ?? '',   // Descripción = descripcion_art
                        'semanas'     => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                        'total'       => 0,
                    ];
                }

                $cant = (int) $row->total_cant;
                $productos[$key]['semanas'][$semana] += $cant;
                $productos[$key]['total']            += $cant;
                $totalPorSemana[$semana]             += $cant;
            }

            $totalGeneral = array_sum($totalPorSemana);

            // Formatear celdas con cantidad (%)
            foreach ($productos as &$p) {
                foreach ($p['semanas'] as $s => $cant) {
                    $pct = $totalPorSemana[$s] > 0 ? round(($cant / $totalPorSemana[$s]) * 100) : 0;
                    $p['semanas_fmt'][$s] = $cant > 0 ? "{$cant} ({$pct}%)" : '0';
                }
                $pctTotal = $totalGeneral > 0 ? round(($p['total'] / $totalGeneral) * 100) : 0;
                $p['total_fmt'] = "{$p['total']} ({$pctTotal}%)";
            }
            unset($p);

            usort($productos, fn($a, $b) => $b['total'] <=> $a['total']);
            $productos = $this->calcularRankingABC(array_values($productos), $totalGeneral);

            return [
                'productos'     => $productos,
                'total_semanas' => $totalPorSemana,
                'total_general' => $totalGeneral,
            ];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DEMANDA MENSUAL
    // ──────────────────────────────────────────────────────────────────────────

    public function getDemandaMensual(int $anio): array
    {
        return Cache::remember("asignacion_demanda_mensual_{$anio}", $this->cacheTtl, function () use ($anio) {

            $mesesNombres = [
                1 => 'Ene',
                2 => 'Feb',
                3 => 'Mar',
                4 => 'Abr',
                5 => 'May',
                6 => 'Jun',
                7 => 'Jul',
                8 => 'Ago',
                9 => 'Set',
                10 => 'Oct',
                11 => 'Nov',
                12 => 'Dic',
            ];

            $rows = DB::table('asignacion_bases')
                ->select(
                    'descripcion_base',
                    'descripcion_art',
                    'mes',
                    DB::raw('SUM(cantidad) as total_cant')
                )
                ->where('anio', $anio)
                ->groupBy('descripcion_base', 'descripcion_art', 'mes')
                ->orderBy('mes')
                ->get();

            $productos   = [];
            $totalPorMes = array_fill_keys(range(1, 12), 0);

            foreach ($rows as $row) {
                $m    = (int) $row->mes;
                $key  = ($row->descripcion_base ?? '') . '|' . ($row->descripcion_art ?? '');
                $cant = (int) $row->total_cant;

                if (!isset($productos[$key])) {
                    $productos[$key] = [
                        'codigo'      => $row->descripcion_base ?? '',   // Código Base = descripcion_base
                        'descripcion' => $row->descripcion_art  ?? '',   // Descripción = descripcion_art
                        'meses'       => array_fill_keys(range(1, 12), 0),
                        'total'       => 0,
                    ];
                }

                $productos[$key]['meses'][$m] += $cant;
                $productos[$key]['total']     += $cant;
                $totalPorMes[$m]             += $cant;
            }

            $totalGeneral = array_sum($totalPorMes);

            foreach ($productos as &$p) {
                foreach ($p['meses'] as $m => $cant) {
                    $pct = $totalPorMes[$m] > 0 ? round(($cant / $totalPorMes[$m]) * 100) : 0;
                    $p['meses_fmt'][$m] = $cant > 0 ? "{$cant} ({$pct}%)" : '0';
                }
                $pctTotal = $totalGeneral > 0 ? round(($p['total'] / $totalGeneral) * 100) : 0;
                $p['total_fmt'] = "{$p['total']} ({$pctTotal}%)";
            }
            unset($p);

            usort($productos, fn($a, $b) => $b['total'] <=> $a['total']);
            $productos = $this->calcularRankingABC(array_values($productos), $totalGeneral);

            return [
                'productos'     => $productos,
                'total_meses'   => $totalPorMes,
                'meses_labels'  => $mesesNombres,
                'total_general' => $totalGeneral,
            ];
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────────────

    private function calcularRankingABC(array $productos, int $totalGeneral): array
    {
        $acumulado = 0;
        foreach ($productos as &$p) {
            $acumulado += $p['total'];
            $pct = $totalGeneral > 0 ? ($acumulado / $totalGeneral) * 100 : 0;
            $p['categoria'] = match (true) {
                $pct <= 70 => 'A',
                $pct <= 90 => 'B',
                $pct <= 97 => 'C',
                default    => 'D',
            };
        }
        unset($p);
        return $productos;
    }

    public function clearCache(): void
    {
        $anios = DB::table('asignacion_bases')
            ->distinct()
            ->pluck('anio')
            ->toArray();

        Cache::forget('asignacion_anios');

        foreach ($anios as $anio) {
            Cache::forget("asignacion_evolutivo_semanal_{$anio}");
            Cache::forget("asignacion_evolutivo_mensual_{$anio}");
            Cache::forget("asignacion_grafico_diario_{$anio}");
            Cache::forget("asignacion_grafico_barras_{$anio}");
            Cache::forget("asignacion_demanda_mensual_{$anio}");

            for ($m = 1; $m <= 12; $m++) {
                Cache::forget("asignacion_demanda_semanal_{$anio}_{$m}");
                Cache::forget("asignacion_evolutivo_diario_{$anio}_{$m}");
            }
        }
    }

    public function getEvolutivoDiario(int $anio, int $mes): array
    {
        return Cache::remember("asignacion_evolutivo_diario_{$anio}_{$mes}", $this->cacheTtl, function () use ($anio, $mes) {

            $rows = DB::table('asignacion_bases')
                ->select(
                    'fecha_asignacion',
                    'estado_asignacion',
                    DB::raw('SUM(cantidad) as total_cant'),
                    DB::raw('SUM(precio * cantidad) as total_precio')
                )
                ->where('anio', $anio)
                ->where('mes', $mes)
                ->groupBy('fecha_asignacion', 'estado_asignacion')
                ->orderBy('fecha_asignacion')
                ->get();

            // Número de días del mes
            $diasEnMes = \Carbon\Carbon::createFromDate($anio, $mes, 1)->daysInMonth;

            // Inicializar todos los días
            $dias = [];
            for ($d = 1; $d <= $diasEnMes; $d++) {
                $dias[$d] = [
                    'dia'        => $d,
                    'R_cantidad' => 0,
                    'N_cantidad' => 0,
                    'R_precio'   => 0,
                    'N_precio'   => 0,
                ];
            }

            foreach ($rows as $row) {
                $dia    = (int) \Carbon\Carbon::parse($row->fecha_asignacion)->format('j');
                $estado = $row->estado_asignacion;

                if ($estado === 'R') {
                    $dias[$dia]['R_cantidad'] += (int)   $row->total_cant;
                    $dias[$dia]['R_precio']   += (float) $row->total_precio;
                } else {
                    $dias[$dia]['N_cantidad'] += (int)   $row->total_cant;
                    $dias[$dia]['N_precio']   += (float) $row->total_precio;
                }
            }

            // Calcular KPI por día y redondear precios
            $result = [];
            foreach ($dias as $d) {
                $kpi = $d['N_cantidad'] > 0
                    ? round(($d['R_cantidad'] / $d['N_cantidad']) * 100, 1)
                    : 0;

                $result[] = [
                    'dia'        => $d['dia'],
                    'R_cantidad' => $d['R_cantidad'],
                    'N_cantidad' => $d['N_cantidad'],
                    'kpi'        => $kpi,
                    'R_precio'   => round($d['R_precio'], 2),
                    'N_precio'   => round($d['N_precio'], 2),
                ];
            }

            $totalR = array_sum(array_column($result, 'R_cantidad'));
            $totalN = array_sum(array_column($result, 'N_cantidad'));

            return [
                'dias'           => $result,
                'total_R'        => $totalR,
                'total_N'        => $totalN,
                'total_kpi'      => $totalN > 0 ? round(($totalR / $totalN) * 100, 1) : 0,
                'total_R_precio' => round(array_sum(array_column($result, 'R_precio')), 2),
                'total_N_precio' => round(array_sum(array_column($result, 'N_precio')), 2),
            ];
        });
    }
}
