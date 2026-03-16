<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\GoogleSheetsService;


class VentaClienteController extends Controller
{
    protected GoogleSheetsService $googleSheets;

    // ─── Nombre de la hoja — solo esto cambia si renombran la hoja ───
    private const SHEET_NAME = 'Venta_Historica';
    private const CACHE_KEY  = 'venta_x_cliente_raw';
    private const CACHE_TTL  = 600; // 10 minutos

    public function __construct(GoogleSheetsService $googleSheets)
    {
        $this->googleSheets = $googleSheets;
    }

    // ─────────────────────────────────────────────────────────────────
    // Helper: leer hoja con caché
    // Columnas: A=Sede, B=RUC, C=Razón Social, D=Año, E=Mes, F=Importe
    // ─────────────────────────────────────────────────────────────────
    private function getRawData(): array
    {
        return Cache::store('file')->remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            function () {
                $spreadsheetId = config('google.venta_clientes_spreadsheet_id');

                $rows = $this->googleSheets->getSheetDataFromSpreadsheet(
                    $spreadsheetId,
                    self::SHEET_NAME,
                    'A:F'
                );

                // Quitar cabecera
                array_shift($rows);
                return $rows ?? [];
            }
        );
    }

    private function limpiarNumero($valor): float
    {
        if (empty($valor)) return 0.0;
        $v = str_replace([',', ' '], ['.', ''], trim((string) $valor));
        return floatval(preg_replace('/[^0-9.\-]/', '', $v));
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
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '60');

            $anio = $request->input('anio', now()->year);

            // filtrar por la sede del usuario
            $user = auth()->user();
            $esSede = $user->isSede();
            $sedeFija = $esSede ? strtoupper($user->sede) : null;

            $meses = [
                'ENERO',
                'FEBRERO',
                'MARZO',
                'ABRIL',
                'MAYO',
                'JUNIO',
                'JULIO',
                'AGOSTO',
                'SETIEMBRE',
                'OCTUBRE',
                'NOVIEMBRE',
                'DICIEMBRE'
            ];

            $raw      = $this->getRawData();
            $clientes = [];

            foreach ($raw as $row) {
                $sede    = strtoupper(trim($row[0] ?? ''));
                $ruc     = trim($row[1] ?? '');
                $razon   = trim($row[2] ?? '');
                $anioR   = trim($row[3] ?? '');
                $mesR    = strtoupper(trim($row[4] ?? ''));
                $importe = $this->limpiarNumero($row[5] ?? 0);

                if ($anioR != $anio || !$sede || !$ruc) continue;
                // Si es sede, solo su sede
                if ($sedeFija && $sede !== $sedeFija) continue;

                $key = "{$sede}||{$ruc}||{$razon}";

                if (!isset($clientes[$key])) {
                    $clientes[$key] = [
                        'sede'  => $sede,
                        'ruc'   => $ruc,
                        'razon' => $razon,
                        'meses' => array_fill_keys($meses, 0),
                        'total' => 0,
                    ];
                }

                // Normalizar variante ortográfica
                $mesR = str_replace('SEPTIEMBRE', 'SETIEMBRE', $mesR);

                if (in_array($mesR, $meses)) {
                    $clientes[$key]['meses'][$mesR] += $importe;
                    $clientes[$key]['total']        += $importe;
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
            ini_set('memory_limit', '512M');
            ini_set('max_execution_time', '60');

            $raw      = $this->getRawData();
            $clientes = [];
            $aniosSet = [];

            $user     = auth()->user();
            $esSede   = $user->isSede();
            $sedeFija = $esSede ? strtoupper(trim($user->sede)) : null;

            foreach ($raw as $row) {
                $sede    = strtoupper(trim($row[0] ?? ''));
                $ruc     = trim($row[1] ?? '');
                $razon   = trim($row[2] ?? '');
                $anioR   = trim($row[3] ?? '');
                $importe = $this->limpiarNumero($row[5] ?? 0);

                if (!$sede || !$ruc || !is_numeric($anioR)) continue;
                // Si es sede, solo su sede
                if ($sedeFija && $sede !== $sedeFija) continue;

                $anioInt            = (int) $anioR;
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
            $raw   = $this->getRawData();
            $anios = [];

            $user    = auth()->user();
            $esSede  = $user->isSede();
            $sedeFija = $esSede ? strtoupper(trim($user->sede)) : null;

            foreach ($raw as $row) {
                $sede = strtoupper(trim($row[0] ?? ''));
                $a    = trim($row[3] ?? '');
                if ($sedeFija && $sede !== $sedeFija) continue;
                if (is_numeric($a)) $anios[(int)$a] = true;
            }

            $anios = array_keys($anios);
            rsort($anios);

            return response()->json(['success' => true, 'anios' => $anios]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Limpiar caché
    // ─────────────────────────────────────────────────────────────────
    public function clearCache()
    {
        Cache::store('file')->forget(self::CACHE_KEY);
        return response()->json(['success' => true, 'message' => 'Caché limpiado correctamente']);
    }
}
