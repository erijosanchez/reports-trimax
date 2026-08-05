<?php

namespace App\Http\Controllers;

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

    private function sedeFijaDelUsuario(): ?string
    {
        $user = auth()->user();
        return $user->isSede() ? strtoupper(trim($user->sede)) : null;
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
