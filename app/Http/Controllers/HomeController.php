<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dashboard;
use Google\Client;
use Google\Service\Sheets;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ✅ SI ES USUARIO DE SEDE Y TIENE SEDE ASIGNADA
        if ($user->isSede() && $user->hasSede()) {
            return $this->dashboardVentas();
        }

        // ✅ SI ES ADMIN, SUPER ADMIN, MARKETING O CONSULTOR - MOSTRAR DASHBOARDS DE POWER BI
        $dashboards = Dashboard::active()
            ->when(!$user->isAdmin() && !$user->isSuperAdmin(), function ($query) use ($user) {
                $query->whereHas('users', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->get();

        return view('home', compact('dashboards'));
    }

    /**
     * Dashboard de ventas para usuarios de sede
     */
    private function dashboardVentas()
    {
        $user = auth()->user();
        $sedeUsuario = strtoupper($user->sede);

        // Obtener mes y año actual automáticamente
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));
        $anioActual = Carbon::now()->year;

        // ID del Google Sheet
        $spreadsheetId = '1zQ8h0cX8YdQ4Jko69vGpDNMXMRrXD0ICCH6G4O6ubiY';

        try {
            // Configurar Google Sheets
            $client = new Client();
            $client->setApplicationName('TRIMAX Ventas');
            $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig(storage_path('app/google/service-account.json'));
            $client->setAccessType('offline');

            $service = new Sheets($client);

            // Obtener datos del mes actual
            $datos = $this->obtenerDatosVentas($service, $spreadsheetId, $sedeUsuario, $mesActual, $anioActual);

            // Obtener datos históricos del año actual
            $historico = $this->obtenerDatosHistoricos($service, $spreadsheetId, $sedeUsuario, $anioActual);

            // ✅ NUEVO: Obtener años disponibles
            $aniosDisponibles = $this->obtenerAniosDisponibles($service, $spreadsheetId, $sedeUsuario);

            // ✅ NUEVO: Obtener datos anuales para comparación
            $datosAnuales = $this->obtenerDatosAnuales($service, $spreadsheetId, $sedeUsuario);
        } catch (\Exception $e) {
            Log::error('Error conectando con Google Sheets: ' . $e->getMessage());

            $datos = [
                'venta_general' => 0,
                'venta_proyectada' => 0,
                'cuota' => 0,
                'cumplimiento_cuota' => 0,
                'venta_total' => 0
            ];

            $historico = [
                'meses' => [],
                'ventas' => [],
                'cuotas' => [],
                'cumplimientos' => []
            ];

            $aniosDisponibles = [$anioActual];
            $datosAnuales = ['anios' => [], 'ventas' => []];
        }

        return view('home-ventas', compact('datos', 'historico', 'sedeUsuario', 'mesActual', 'anioActual', 'aniosDisponibles', 'datosAnuales'));
    }

    /**
     * ✅ NUEVO: API para obtener datos de un año específico (AJAX)
     */
    public function getVentasData(Request $request)
    {
        $user = auth()->user();

        if (!$user->isSede() || !$user->hasSede()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $sedeUsuario = strtoupper($user->sede);
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

            $datos = $this->obtenerDatosVentas($service, $spreadsheetId, $sedeUsuario, $mes, $anio);
            $historico = $this->obtenerDatosHistoricos($service, $spreadsheetId, $sedeUsuario, $anio);

            return response()->json([
                'datos' => $datos,
                'historico' => $historico,
                'anio' => $anio,
                'mes' => $mes
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo datos de ventas: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener datos'], 500);
        }
    }

    /**
     * Obtener datos de ventas del mes específico
     */
    private function obtenerDatosVentas($service, $spreadsheetId, $sede, $mes, $anio)
    {
        $range = 'Historico!A:H';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        $datos = [
            'venta_general' => 0,
            'venta_proyectada' => 0,
            'cuota' => 0,
            'cumplimiento_cuota' => 0,
            'venta_total' => 0
        ];

        if (empty($values)) {
            return $datos;
        }

        foreach ($values as $index => $row) {
            if ($index == 0) continue;

            if (!empty($row[0]) && !empty($row[1]) && !empty($row[2])) {
                $sedeSheet = trim(strtoupper($row[0]));
                $anioSheet = trim($row[1]);
                $mesSheet = trim(ucfirst(strtolower($row[2])));

                if ($sedeSheet == $sede && $anioSheet == $anio && $mesSheet == $mes) {
                    $datos['venta_general'] = $this->limpiarNumero($row[3] ?? 0);
                    $datos['venta_proyectada'] = $this->limpiarNumero($row[4] ?? 0);
                    $datos['cuota'] = $this->limpiarNumero($row[5] ?? 0);
                    $datos['cumplimiento_cuota'] = $this->limpiarPorcentaje($row[6] ?? '0%');
                    $datos['venta_total'] = $this->limpiarNumero($row[7] ?? 0);
                    break;
                }
            }
        }

        return $datos;
    }

    /**
     * Obtener datos históricos del año
     */
    private function obtenerDatosHistoricos($service, $spreadsheetId, $sede, $anio)
    {
        $range = 'Historico!A:H';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        $historico = [
            'meses' => [],
            'ventas' => [],
            'cuotas' => [],
            'cumplimientos' => []
        ];

        if (empty($values)) {
            return $historico;
        }

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

        $datosTemp = [];

        foreach ($values as $index => $row) {
            if ($index == 0) continue;

            if (!empty($row[0]) && !empty($row[1]) && !empty($row[2])) {
                $sedeSheet = trim(strtoupper($row[0]));
                $anioSheet = trim($row[1]);

                if ($sedeSheet == $sede && $anioSheet == $anio) {
                    $mes = trim(ucfirst(strtolower($row[2])));

                    $datosTemp[] = [
                        'mes' => $mes,
                        'orden' => $ordenMeses[strtolower($mes)] ?? 99,
                        'venta' => $this->limpiarNumero($row[3] ?? 0),
                        'cuota' => $this->limpiarNumero($row[5] ?? 0),
                        'cumplimiento' => $this->limpiarPorcentaje($row[6] ?? '0%')
                    ];
                }
            }
        }

        usort($datosTemp, function ($a, $b) {
            return $a['orden'] <=> $b['orden'];
        });

        foreach ($datosTemp as $dato) {
            $historico['meses'][] = $dato['mes'];
            $historico['ventas'][] = $dato['venta'];
            $historico['cuotas'][] = $dato['cuota'];
            $historico['cumplimientos'][] = $dato['cumplimiento'];
        }

        return $historico;
    }

    /**
     * ✅ NUEVO: Obtener años disponibles en el sheet para esta sede
     */
    private function obtenerAniosDisponibles($service, $spreadsheetId, $sede)
    {
        $range = 'Historico!A:B';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        $anios = [];

        foreach ($values as $index => $row) {
            if ($index == 0) continue;

            if (!empty($row[0]) && !empty($row[1])) {
                $sedeSheet = trim(strtoupper($row[0]));
                $anio = trim($row[1]);

                if ($sedeSheet == $sede && is_numeric($anio)) {
                    $anios[] = (int)$anio;
                }
            }
        }

        $anios = array_unique($anios);
        rsort($anios); // Ordenar descendente (más reciente primero)

        return $anios;
    }

    /**
     * ✅ NUEVO: Obtener datos anuales (suma de ventas por año)
     */
    private function obtenerDatosAnuales($service, $spreadsheetId, $sede)
    {
        $range = 'Historico!A:H';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        // Obtener mes actual
        $mesActual = ucfirst(Carbon::now()->locale('es')->translatedFormat('F'));

        $datosAnuales = [];

        foreach ($values as $index => $row) {
            if ($index == 0) continue;

            if (!empty($row[0]) && !empty($row[1]) && !empty($row[2]) && !empty($row[3])) {
                $sedeSheet = trim(strtoupper($row[0]));
                $anio = trim($row[1]);
                $mesSheet = trim(ucfirst(strtolower($row[2])));
                $venta = $this->limpiarNumero($row[3] ?? 0);

                // 🔥 SOLO tomar datos del MES ACTUAL
                if ($sedeSheet == $sede && $mesSheet == $mesActual && is_numeric($anio)) {
                    $datosAnuales[$anio] = $venta;
                }
            }
        }

        // Ordenar por año
        ksort($datosAnuales);

        return [
            'anios' => array_keys($datosAnuales),
            'ventas' => array_values($datosAnuales),
            'mes' => $mesActual // Para mostrar en el título
        ];
    }

    /**
     * Limpiar números con formato europeo
     */
    private function limpiarNumero($valor)
    {
        if (empty($valor)) {
            return 0;
        }

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

    /**
     * Limpiar porcentajes
     */
    private function limpiarPorcentaje($valor)
    {
        if (empty($valor)) {
            return 0;
        }

        $valor = str_replace('%', '', $valor);
        $valor = trim($valor);
        $valor = str_replace(',', '.', $valor);

        return floatval($valor);
    }
}
