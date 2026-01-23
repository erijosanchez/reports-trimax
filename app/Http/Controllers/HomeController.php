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
            // Configurar Google Sheets (ajusta la ruta según tu proyecto)
            $client = new Client();
            $client->setApplicationName('TRIMAX Ventas');
            $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
            $client->setAuthConfig(storage_path('app/google/service-account.json'));
            $client->setAccessType('offline');

            $service = new Sheets($client);

            // Obtener datos
            $datos = $this->obtenerDatosVentas($service, $spreadsheetId, $sedeUsuario, $mesActual, $anioActual);
            $historico = $this->obtenerDatosHistoricos($service, $spreadsheetId, $sedeUsuario, $anioActual);
        } catch (\Exception $e) {
            Log::error('Error conectando con Google Sheets: ' . $e->getMessage());

            // Datos vacíos en caso de error
            $datos = [
                'venta_general' => 0,
                'venta_proyectada' => 0,
                'cuota' => 0,
                'cumplimiento_cuota' => 0,
            ];

            $historico = [
                'meses' => [],
                'ventas' => [],
                'cuotas' => [],
                'cumplimientos' => []
            ];
        }

        return view('home-ventas', compact('datos', 'historico', 'sedeUsuario', 'mesActual', 'anioActual'));
    }

    /**
     * Obtener datos de ventas del mes actual
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
        ];

        if (empty($values)) {
            return $datos;
        }

        foreach ($values as $index => $row) {
            if ($index == 0) continue; // Saltar encabezados

            if (!empty($row[0]) && !empty($row[1]) && !empty($row[2])) {
                $sedeSheet = trim(strtoupper($row[0]));
                $anioSheet = trim($row[1]);
                $mesSheet = trim(ucfirst(strtolower($row[2])));

                if ($sedeSheet == $sede && $anioSheet == $anio && $mesSheet == $mes) {
                    $datos['venta_general'] = $this->limpiarNumero($row[3] ?? 0);
                    $datos['venta_proyectada'] = $this->limpiarNumero($row[4] ?? 0);
                    $datos['cuota'] = $this->limpiarNumero($row[5] ?? 0);
                    $datos['cumplimiento_cuota'] = $this->limpiarPorcentaje($row[6] ?? '0%');
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

        // Ordenar por mes
        usort($datosTemp, function ($a, $b) {
            return $a['orden'] <=> $b['orden'];
        });

        // Construir arrays finales
        foreach ($datosTemp as $dato) {
            $historico['meses'][] = $dato['mes'];
            $historico['ventas'][] = $dato['venta'];
            $historico['cuotas'][] = $dato['cuota'];
            $historico['cumplimientos'][] = $dato['cumplimiento'];
        }

        return $historico;
    }

    /**
     * Limpiar números (remover comas)
     */
    private function limpiarNumero($valor)
    {
        if (empty($valor)) {
            return 0;
        }

        // Convertir a string por si acaso
        $valor = (string) $valor;

        // Remover espacios
        $valor = trim($valor);

        // Si tiene punto Y coma, el punto es separador de miles
        if (strpos($valor, '.') !== false && strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        // Si solo tiene coma, es el separador decimal
        elseif (strpos($valor, ',') !== false) {
            $valor = str_replace(',', '.', $valor);
        }
        // Si solo tiene punto, ya está en formato correcto

        return floatval($valor);
    }

    /**
     * Limpiar porcentajes (remover %)
     */
    private function limpiarPorcentaje($valor)
    {
        $valor = str_replace(['%', ' '], '', $valor);
        return floatval($valor);
    }
}
