<?php

namespace App\Http\Controllers;

use App\Models\ReporteCobranza;
use App\Models\ReporteCajaChica;
use App\Models\ReporteComentarios;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductivyController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user->puedeAccederProducitvy()) {
            abort(403, 'No tienes acceso al módulo Productivy.');
        }

        // Si solo es sede (sin permiso de vista total), filtramos a su sede
        $sedeFilter = (!$user->puedeVerProductivyTotal() && $user->isSede())
            ? strtoupper($user->sede)
            : null;

        $hoy    = Carbon::now('America/Lima');
        $semana = (int) $request->input('semana', $hoy->isoWeek());
        $anio   = (int) $request->input('anio',   $hoy->isoWeekYear());

        $weekStart = Carbon::now('America/Lima')->setIsoDate($anio, $semana)->startOfDay();
        $weekEnd   = $weekStart->copy()->addDays(5)->endOfDay(); // Sábado

        $prevWeek   = $weekStart->copy()->subWeek();
        $nextWeek   = $weekStart->copy()->addWeek();
        $prevSemana = $prevWeek->isoWeek();
        $prevAnio   = $prevWeek->isoWeekYear();
        $nextSemana = $nextWeek->isoWeek();
        $nextAnio   = $nextWeek->isoWeekYear();

        $isCurrentWeek  = $hoy->isoWeek() === $semana && $hoy->isoWeekYear() === $anio;
        $isFutureWeek   = $weekStart->gt($hoy);

        // Días de la semana (L-M-M-J-V-S)
        $diasSemana = [];
        for ($i = 0; $i < 6; $i++) {
            $diasSemana[] = $weekStart->copy()->addDays($i)->toDateString();
        }

        // Sedes activas (filtradas por sede del usuario si corresponde)
        $sedesQuery = User::whereNotNull('sede')->where('sede', '!=', '')->selectRaw('DISTINCT sede');
        if ($sedeFilter) {
            $sedesQuery->where('sede', $sedeFilter);
        }
        $sedes = $sedesQuery->orderBy('sede')->pluck('sede');

        // Depósitos enviados esta semana (detalle por día)
        $depositosBrutos = ReporteCobranza::whereBetween('semana_inicio', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ])
            ->select('sede', 'semana_inicio', 'fecha_envio_original', 'kpi_porcentaje', 'estado')
            ->get()
            ->groupBy('sede');

        // Caja chica de la semana
        $cajaChicaPorSede = ReporteCajaChica::where('semana_numero', $semana)
            ->where('anio', $anio)
            ->get()
            ->keyBy('sede');

        // Comentarios de la semana
        $comentariosPorSede = ReporteComentarios::where('semana_numero', $semana)
            ->where('anio', $anio)
            ->get()
            ->keyBy('sede');

        $tableData = [];

        foreach ($sedes as $sede) {
            $sedeDepositos = $depositosBrutos->get($sede, collect());

            // Indicadores por día (6 días L-S) + suma de KPI
            $diasIndicadores = [];
            $depKpiSum  = 0;
            $depEnviados = 0;
            foreach ($diasSemana as $fecha) {
                // semana_inicio está casteado como 'date' (Carbon), no string → comparar con toDateString()
                $registro  = $sedeDepositos->first(fn($r) => $r->semana_inicio->toDateString() === $fecha);
                $esFuturo  = Carbon::parse($fecha)->isAfter($hoy);

                if ($esFuturo) {
                    $diasIndicadores[] = ['estado' => 'futuro', 'kpi' => null];
                } elseif (!$registro || is_null($registro->fecha_envio_original)) {
                    $diasIndicadores[] = ['estado' => 'no_enviado', 'kpi' => 0];
                    // día no enviado = 0 en KPI (ya sumado implícitamente)
                } elseif (($registro->kpi_porcentaje ?? 0) >= 80) {
                    $diasIndicadores[] = ['estado' => 'en_tiempo', 'kpi' => $registro->kpi_porcentaje];
                    $depKpiSum += $registro->kpi_porcentaje;
                    $depEnviados++;
                } else {
                    $diasIndicadores[] = ['estado' => 'con_atraso', 'kpi' => $registro->kpi_porcentaje];
                    $depKpiSum += $registro->kpi_porcentaje;
                    $depEnviados++;
                }
            }
            // KPI depósitos = promedio sobre los 6 días posibles (días no enviados = 0)
            $depKpi = round($depKpiSum / 6, 1);

            $cc    = $cajaChicaPorSede[$sede] ?? null;
            $com   = $comentariosPorSede[$sede] ?? null;
            $ccKpi  = round((float) ($cc->kpi_porcentaje  ?? 0), 1);
            $comKpi = round((float) ($com->kpi_porcentaje ?? 0), 1);

            // Productivy = promedio de los 3 KPIs (igual peso a cada categoría)
            $productivy = round(($depKpi + $ccKpi + $comKpi) / 3, 1);

            $tableData[] = [
                'sede'         => $sede,
                'depositos'    => $depEnviados,
                'dep_kpi'      => $depKpi,
                'dias'         => $diasIndicadores,
                'cc_enviada'   => $cc && !is_null($cc->fecha_envio_original),
                'cc_kpi'       => $ccKpi,
                'cc_fecha'     => ($cc && !is_null($cc->fecha_envio_original))
                                    ? $cc->fecha_envio_original->setTimezone('America/Lima')->format('d/m H:i')
                                    : null,
                'com_enviados' => $com && !is_null($com->fecha_envio_original),
                'com_kpi'      => $comKpi,
                'com_fecha'    => ($com && !is_null($com->fecha_envio_original))
                                    ? $com->fecha_envio_original->setTimezone('America/Lima')->format('d/m H:i')
                                    : null,
                'productivy'   => $productivy,
            ];
        }

        usort($tableData, fn($a, $b) =>
            $b['productivy'] <=> $a['productivy'] ?: strcmp($a['sede'], $b['sede'])
        );

        // Resumen global
        $totalSedes    = count($tableData);
        $avgDepositos  = $totalSedes > 0 ? round(array_sum(array_column($tableData, 'dep_kpi'))  / $totalSedes, 1) : 0;
        $avgCajaChica  = $totalSedes > 0 ? round(array_sum(array_column($tableData, 'cc_kpi'))   / $totalSedes, 1) : 0;
        $avgComentarios = $totalSedes > 0 ? round(array_sum(array_column($tableData, 'com_kpi')) / $totalSedes, 1) : 0;
        $ccEnviadas    = count(array_filter($tableData, fn($r) => $r['cc_enviada']));
        $comEnviados   = count(array_filter($tableData, fn($r) => $r['com_enviados']));
        $avgProductivy = $totalSedes > 0 ? round(array_sum(array_column($tableData, 'productivy')) / $totalSedes, 1) : 0;

        return view('productividad.productivy.index', compact(
            'tableData', 'semana', 'anio', 'weekStart', 'weekEnd',
            'isCurrentWeek', 'isFutureWeek',
            'prevSemana', 'prevAnio', 'nextSemana', 'nextAnio',
            'totalSedes', 'avgDepositos', 'avgCajaChica', 'avgComentarios',
            'ccEnviadas', 'comEnviados', 'avgProductivy',
            'diasSemana', 'sedeFilter'
        ));
    }
}
