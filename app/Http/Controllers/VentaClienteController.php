<?php

namespace App\Http\Controllers;

use App\Models\Feriado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaClienteController extends Controller
{
    // Los datos se sincronizan desde Google Sheets a esta tabla cada 30 min
    // por el comando trimax:sync-venta-clientes (ver routes/console.php).
    private const TABLA = 'venta_clientes_historico';

    private const MESES = [
        1  => 'ENERO',
        2  => 'FEBRERO',
        3  => 'MARZO',
        4  => 'ABRIL',
        5  => 'MAYO',
        6  => 'JUNIO',
        7  => 'JULIO',
        8  => 'AGOSTO',
        9  => 'SETIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];

    // Cuentas que aparecen en el sheet como "sede" pero no son sedes físicas.
    // Mismo criterio que ComercialController::sedesMonturas() / HomeController,
    // sumando "(EN BLANCO)" (ventas sin sede asignada en el sheet).
    private const SEDES_EXCLUIDAS_TOP = [
        'CONSULTOR DE MONTURAS 1',
        'CONSULTOR DE MONTURAS 2',
        'MONTURAS GENERAL',
        '(EN BLANCO)',
    ];

    private const TOP_N = 25;

    private function sedeFijaDelUsuario(): ?string
    {
        $user = auth()->user();
        return $user->isSede() ? strtoupper(trim($user->sede)) : null;
    }

    /** Cuenta días hábiles (no domingo, no feriado nacional) entre dos fechas, ambas inclusive. */
    private function diasHabilesEnRango(Carbon $inicio, Carbon $fin): int
    {
        $count = 0;
        $cursor = $inicio->copy();
        while ($cursor->lte($fin)) {
            if (Feriado::esDiaLaborable($cursor)) {
                $count++;
            }
            $cursor->addDay();
        }
        return $count;
    }

    // ─────────────────────────────────────────────────────────────────
    // VISTA 0: Top Clientes por Sede
    // ─────────────────────────────────────────────────────────────────
    public function topClientes()
    {
        if (!auth()->user()->puedeVerTopClientes()) {
            abort(403, 'No tienes permiso para ver Top Clientes');
        }

        return view('comercial.venta-cliente.top-clientes');
    }

    public function getTopClientesData(Request $request)
    {
        if (!auth()->user()->puedeVerTopClientes()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $hoy = Carbon::now();
            $mesCerrado = false;

            // Mes de referencia opcional (?anio=2026&mes=6) para ver la misma
            // comparativa de un mes ya completado. Si coincide con el mes
            // actual, se ignora y sigue comportándose como "en curso" (con
            // proyección) — evita que alguien mande el mes de hoy y se
            // trate como cerrado a mitad de mes.
            $anioParam = (int) $request->input('anio');
            $mesParam  = (int) $request->input('mes');

            if ($anioParam && $mesParam >= 1 && $mesParam <= 12) {
                $seleccionado = Carbon::create($anioParam, $mesParam, 1);
                if (!$seleccionado->isSameMonth($hoy)) {
                    $hoy = $seleccionado->endOfMonth();
                    $mesCerrado = true;
                }
            }

            // Los 3 meses anteriores al actual + el mes actual, con manejo de cruce de año.
            $periodos = []; // 'actual','m1','m2','m3' => ['anio'=>..,'mes'=>..,'codigo'=>anio*100+mes]
            foreach (['actual' => 0, 'm1' => 1, 'm2' => 2, 'm3' => 3] as $clave => $offset) {
                $fecha = $hoy->copy()->subMonthsNoOverflow($offset);
                $periodos[$clave] = [
                    'anio'   => $fecha->year,
                    'mes'    => $fecha->month,
                    'codigo' => $fecha->year * 100 + $fecha->month,
                    'label'  => self::MESES[$fecha->month] . ' ' . $fecha->year,
                ];
            }

            $inicioMes = $hoy->copy()->startOfMonth();
            $finMes    = $hoy->copy()->endOfMonth();
            $diasHabilesTranscurridos = $this->diasHabilesEnRango($inicioMes, $hoy->copy()->startOfDay());
            $diasHabilesTotales       = $this->diasHabilesEnRango($inicioMes, $finMes);

            $sedeFija = $this->sedeFijaDelUsuario();

            $codigos = array_column($periodos, 'codigo');

            $query = DB::table(self::TABLA)
                ->select('sede', 'ruc', 'razon_social', 'anio', 'mes', 'importe')
                ->whereIn(DB::raw('(anio * 100 + mes)'), $codigos)
                ->whereNotIn('sede', self::SEDES_EXCLUIDAS_TOP);

            if ($sedeFija) {
                $query->where('sede', $sedeFija);
            }

            // Agrupar en memoria por sede -> ruc
            $porSede = [];
            foreach ($query->get() as $row) {
                $codigo = (int) $row->anio * 100 + (int) $row->mes;
                if ($row->razon_social || !isset($porSede[$row->sede][$row->ruc]['razon'])) {
                    $porSede[$row->sede][$row->ruc]['razon'] = $row->razon_social ?: '';
                }
                $porSede[$row->sede][$row->ruc]['periodos'][$codigo] = (float) $row->importe;
            }

            $resultado = [];
            foreach ($porSede as $sede => $clientes) {
                $filas = [];

                foreach ($clientes as $ruc => $info) {
                    $p = $info['periodos'] ?? [];
                    $ventaM3 = $p[$periodos['m3']['codigo']] ?? 0.0;
                    $ventaM2 = $p[$periodos['m2']['codigo']] ?? 0.0;
                    $ventaM1 = $p[$periodos['m1']['codigo']] ?? 0.0;
                    $prom    = round(($ventaM3 + $ventaM2 + $ventaM1) / 3, 2);

                    if ($prom <= 0) continue; // sin actividad relevante en los 3 meses previos

                    $ventaActualReal = $p[$periodos['actual']['codigo']] ?? 0.0;
                    $proyeccion = $diasHabilesTranscurridos > 0
                        ? round(($ventaActualReal / $diasHabilesTranscurridos) * $diasHabilesTotales, 2)
                        : 0.0;

                    $variacionPct = round((($proyeccion - $prom) / $prom) * 100, 1);
                    $semaforo = $variacionPct < 0 ? 'rojo' : ($variacionPct < 10 ? 'amarillo' : 'verde');

                    $filas[] = [
                        'ruc'               => $ruc,
                        'razon'             => $info['razon'],
                        'venta_m3'          => $ventaM3,
                        'venta_m2'          => $ventaM2,
                        'venta_m1'          => $ventaM1,
                        'prom'              => $prom,
                        'venta_actual'      => $proyeccion,
                        'venta_actual_real' => $ventaActualReal,
                        'variacion_pct'     => $variacionPct,
                        'semaforo'          => $semaforo,
                    ];
                }

                if (empty($filas)) continue; // sede sin ningún cliente con historial en los 3 meses previos

                usort($filas, fn($a, $b) => $b['prom'] <=> $a['prom']);

                $resultado[] = [
                    'sede'     => $sede,
                    'clientes' => array_slice($filas, 0, self::TOP_N),
                ];
            }

            usort($resultado, fn($a, $b) => $a['sede'] <=> $b['sede']);

            return response()->json([
                'success' => true,
                'periodos' => [
                    'm3'     => $periodos['m3']['label'],
                    'm2'     => $periodos['m2']['label'],
                    'm1'     => $periodos['m1']['label'],
                    'actual' => $periodos['actual']['label'],
                ],
                'dias_habiles_transcurridos' => $diasHabilesTranscurridos,
                'dias_habiles_totales'       => $diasHabilesTotales,
                'fecha_corte'                => $hoy->toDateString(),
                'mes_cerrado'                => $mesCerrado,
                'sedes'                      => $resultado,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getTopClientesData: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // VISTA 1: Evolutivo por Mes
    // ─────────────────────────────────────────────────────────────────
    public function evolutivoMes()
    {
        if (!auth()->user()->puedeVerVentaClientes()) {
            abort(403, 'No tienes permiso para ver Venta Clientes');
        }

        return view('comercial.venta-cliente.evolutivo-mes');
    }

    public function getEvolutivoMesData(Request $request)
    {
        if (!auth()->user()->puedeVerVentaClientes()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $anio     = (int) $request->input('anio', now()->year);
            $sedeFija = $this->sedeFijaDelUsuario();
            $meses    = array_values(self::MESES);

            $query = DB::table(self::TABLA)
                ->select('sede', 'ruc', 'razon_social', 'mes', 'importe')
                ->where('anio', $anio);

            if ($sedeFija) {
                $query->where('sede', $sedeFija);
            }

            $clientes = [];

            foreach ($query->get() as $row) {
                $sede  = $row->sede;
                $ruc   = $row->ruc;
                $razon = $row->razon_social ?? '';
                $key   = "{$sede}||{$ruc}||{$razon}";

                if (!isset($clientes[$key])) {
                    $clientes[$key] = [
                        'sede'  => $sede,
                        'ruc'   => $ruc,
                        'razon' => $razon,
                        'meses' => array_fill_keys($meses, 0),
                        'total' => 0,
                    ];
                }

                $mesNombre = self::MESES[(int) $row->mes] ?? null;
                if ($mesNombre) {
                    $clientes[$key]['meses'][$mesNombre] += (float) $row->importe;
                    $clientes[$key]['total']              += (float) $row->importe;
                }
            }

            usort(
                $clientes,
                fn($a, $b) =>
                $a['sede'] <=> $b['sede'] ?: $a['razon'] <=> $b['razon']
            );

            return response()->json([
                'success' => true,
                'data'    => array_values($clientes),
                'meses'   => $meses,
                'anio'    => $anio,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getEvolutivoMesData: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // VISTA 2: Evolutivo por Año
    // ─────────────────────────────────────────────────────────────────
    public function evolutivoAnio()
    {
        if (!auth()->user()->puedeVerVentaClientes()) {
            abort(403, 'No tienes permiso para ver Venta Clientes');
        }

        return view('comercial.venta-cliente.evolutivo-anio');
    }

    public function getEvolutivoAnioData(Request $request)
    {
        if (!auth()->user()->puedeVerVentaClientes()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        try {
            $sedeFija = $this->sedeFijaDelUsuario();

            $query = DB::table(self::TABLA)
                ->select('sede', 'ruc', 'razon_social', 'anio', DB::raw('SUM(importe) as importe'))
                ->groupBy('sede', 'ruc', 'razon_social', 'anio');

            if ($sedeFija) {
                $query->where('sede', $sedeFija);
            }

            $clientes = [];
            $aniosSet = [];

            foreach ($query->get() as $row) {
                $sede    = $row->sede;
                $ruc     = $row->ruc;
                $razon   = $row->razon_social ?? '';
                $anioInt = (int) $row->anio;
                $importe = (float) $row->importe;

                $aniosSet[$anioInt] = true;
                $key                = "{$sede}||{$ruc}||{$razon}";

                if (!isset($clientes[$key])) {
                    $clientes[$key] = [
                        'sede'  => $sede,
                        'ruc'   => $ruc,
                        'razon' => $razon,
                        'anios' => [],
                        'total' => 0,
                    ];
                }

                $clientes[$key]['anios'][$anioInt] = ($clientes[$key]['anios'][$anioInt] ?? 0) + $importe;
                $clientes[$key]['total']           += $importe;
            }

            $anios = array_keys($aniosSet);
            sort($anios);

            // Normalizar: todos los clientes tienen todos los años (para celdas vacías)
            foreach ($clientes as &$c) {
                foreach ($anios as $a) {
                    if (!isset($c['anios'][$a])) $c['anios'][$a] = 0;
                }
                ksort($c['anios']);
            }
            unset($c);

            usort(
                $clientes,
                fn($a, $b) =>
                $a['sede'] <=> $b['sede'] ?: $a['razon'] <=> $b['razon']
            );

            return response()->json([
                'success' => true,
                'data'    => array_values($clientes),
                'anios'   => $anios,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getEvolutivoAnioData: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Años disponibles (para el selector del Evolutivo Mes)
    // ─────────────────────────────────────────────────────────────────
    public function getAnios()
    {
        try {
            $sedeFija = $this->sedeFijaDelUsuario();

            $query = DB::table(self::TABLA)->select('anio')->distinct();

            if ($sedeFija) {
                $query->where('sede', $sedeFija);
            }

            $anios = $query->pluck('anio')->map(fn($a) => (int) $a)->all();
            rsort($anios);

            return response()->json(['success' => true, 'anios' => $anios]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Forzar una resincronización inmediata desde Google Sheets
    // (antes limpiaba un caché de 10 min; ahora los datos viven en BD
    // y se refrescan solos cada 30 min vía trimax:sync-venta-clientes).
    // ─────────────────────────────────────────────────────────────────
    public function clearCache()
    {
        try {
            Artisan::call('trimax:sync-venta-clientes');
            return response()->json(['success' => true, 'message' => 'Datos sincronizados correctamente']);
        } catch (\Exception $e) {
            Log::error('Error al forzar sync de venta-clientes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
