<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PendienteEntregaMonturaController extends Controller
{
    protected $sheetsService;

    public function __construct(GoogleSheetsService $sheetsService)
    {
        $this->sheetsService = $sheetsService;
    }

    // ════════════════════════════════════════════════════════════
    //  VISTA
    // ════════════════════════════════════════════════════════════

    public function index()
    {
        if (!auth()->user()->puedeVerPendienteEntregaMontura()) {
            abort(403, 'No tienes permiso para ver Pendiente - Entrega Montura');
        }

        return view('comercial.pendiente-entrega-montura');
    }

    // ════════════════════════════════════════════════════════════
    //  API
    // ════════════════════════════════════════════════════════════

    public function getData(Request $request)
    {
        try {
            $sede  = $request->get('sede', '');
            $estado = $request->get('estado', '');

            $cacheKey = "pendiente_montura_data";

            $todasLasOrdenes = Cache::remember($cacheKey, 300, function () {
                return $this->fetchOrdenes();
            });

            $ordenes = $todasLasOrdenes;

            if (!empty($sede)) {
                $ordenes = array_values(array_filter($ordenes, fn($o) => strtoupper($o['sede']) === strtoupper($sede)));
            }
            if (!empty($estado)) {
                $ordenes = array_values(array_filter($ordenes, fn($o) => strtoupper($o['estado']) === strtoupper($estado)));
            }

            $sedes = array_values(array_unique(array_column($todasLasOrdenes, 'sede')));
            sort($sedes);

            return response()->json([
                'success' => true,
                'data'    => $ordenes,
                'total'   => count($ordenes),
                'sedes'   => $sedes,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en PendienteMontura getData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function clearCache()
    {
        Cache::forget('pendiente_montura_data');
        return response()->json(['success' => true, 'message' => 'Cache limpiado']);
    }

    // ════════════════════════════════════════════════════════════
    //  LOGICA PRIVADA
    // ════════════════════════════════════════════════════════════

    private function fetchOrdenes(): array
    {
        $spreadsheetId = config('google.lead_time_spreadsheet_id');
        $rawData = $this->sheetsService->getSheetDataFromSpreadsheet($spreadsheetId, 'Espera_Biselados');

        if (empty($rawData)) return [];

        $headers           = array_shift($rawData);
        $normalizedHeaders = array_map(fn($h) => strtolower(trim(str_replace(' ', '_', $h))), $headers);

        $hoy    = Carbon::today();
        $result = [];

        foreach ($rawData as $row) {
            // Saltar filas completamente vacías
            if (empty(array_filter($row))) continue;

            $rec = [];
            foreach ($normalizedHeaders as $idx => $key) {
                $rec[$key] = isset($row[$idx]) ? trim($row[$idx]) : '';
            }

            // ✅ Saltar filas de metadata como "Actualizado: 03/03/2026..."
            // Solo procesar filas con numero_orden numérico válido
            $numeroOrden = $rec['numero_orden'] ?? '';
            if (empty($numeroOrden) || !is_numeric($numeroOrden)) continue;

            // Calcular TIEMPO en días: Hoy - fecha_orden
            $tiempo   = null;
            $fechaRaw = $rec['fecha_orden'] ?? '';
            if (!empty($fechaRaw)) {
                try {
                    // ✅ Detectar formato DD/MM/YYYY (peruano) vs YYYY-MM-DD (ISO)
                    if (preg_match('/^\d{2}\/\d{2}\/\d{4}/', $fechaRaw)) {
                        $fechaOrden = Carbon::createFromFormat('d/m/Y', explode(' ', $fechaRaw)[0]);
                    } else {
                        $fechaOrden = Carbon::parse($fechaRaw);
                    }
                    $tiempo = (int) $fechaOrden->diffInDays($hoy);
                } catch (\Exception $e) {
                    $tiempo = null;
                }
            }

            $result[] = [
                'sede'              => $rec['descripcion_sede']   ?? $rec['sede']                  ?? '',
                'numero_orden'      => $rec['numero_orden']      ?? '',
                'ruc'               => $rec['ruc']               ?? '',
                'cliente'           => $rec['cliente']           ?? '',
                'diseno'            => $rec['diseno']            ?? $rec['diseño']                ?? '',
                'descripcion_prod'  => $rec['descripcion_prod']  ?? $rec['descripcion_producto']  ?? '',
                'importe'           => $rec['importe']           ?? '',
                'orden_compra'      => $rec['orden_compra']      ?? '',
                'fecha_orden'       => $rec['fecha_orden']       ?? '',
                'hora_orden'        => $rec['hora_orden']        ?? '',
                'tipo_orden'        => $rec['tipo_orden']        ?? '',
                'nombre_usuario'    => $rec['nombre_usuario']    ?? '',
                'estado'            => $rec['estado_orden']      ?? $rec['estado']                ?? '',
                'ubicacion'         => $rec['ubicacion_orden']   ?? '',
                'descripcion_talla' => $rec['descripcion_talla'] ?? '',
                'tratamiento'       => $rec['tratamiento']       ?? '',
                'tiempo_dias'       => $tiempo,
            ];
        }

        // Más antiguos primero (mayor tiempo arriba)
        usort($result, fn($a, $b) => ($b['tiempo_dias'] ?? 0) <=> ($a['tiempo_dias'] ?? 0));

        return $result;
    }
}
