<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssistantController extends Controller
{
    public function query(Request $request)
    {
        $query = strtolower($request->input('query', ''));

        try {
            // Detectar tipo de consulta
            if ($this->contains($query, ['ventas del mes', 'ventas mes', 'mes actual'])) {
                return $this->ventasDelMes();
            }

            if ($this->contains($query, ['top clientes', 'principales clientes', 'mejores clientes'])) {
                return $this->topClientes();
            }

            if ($this->contains($query, ['productos top', 'productos más vendidos', 'qué se vende más'])) {
                return $this->productosTop();
            }

            if ($this->contains($query, ['ventas por sede', 'sede', 'sedes'])) {
                return $this->ventasPorSede();
            }

            if ($this->contains($query, ['facturas', 'facturación', 'documentos'])) {
                return $this->totalFacturacion();
            }

            // Consulta genérica
            return response()->json([
                'success' => true,
                'data' => 'No encontré datos específicos para esa consulta'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function ventasDelMes()
    {
        $mes = date('n');
        $anio = date('Y');

        $ventas = DB::table('ventas')
            ->select(
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('SUM(importe_global) as total_monto'),
                DB::raw('AVG(importe_global) as promedio')
            )
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tipo' => 'ventas_mes',
                'mes' => $mes,
                'anio' => $anio,
                'total_ventas' => $ventas->total_ventas,
                'total_monto' => number_format($ventas->total_monto, 2),
                'promedio' => number_format($ventas->promedio, 2)
            ]
        ]);
    }

    private function topClientes()
    {
        $clientes = DB::table('ventas')
            ->select(
                'razon_social',
                'ruc_dni',
                DB::raw('COUNT(*) as total_compras'),
                DB::raw('SUM(importe_global) as total_gastado')
            )
            ->groupBy('razon_social', 'ruc_dni')
            ->orderBy('total_gastado', 'DESC')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tipo' => 'top_clientes',
                'clientes' => $clientes
            ]
        ]);
    }

    private function productosTop()
    {
        $productos = DB::table('ventas')
            ->select(
                'descripcion',
                'marca',
                DB::raw('COUNT(*) as veces_vendido'),
                DB::raw('SUM(cantidad) as total_unidades'),
                DB::raw('SUM(importe_global) as total_ventas')
            )
            ->groupBy('descripcion', 'marca')
            ->orderBy('veces_vendido', 'DESC')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tipo' => 'productos_top',
                'productos' => $productos
            ]
        ]);
    }

    private function ventasPorSede()
    {
        $sedes = DB::table('ventas')
            ->select(
                'sede',
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('SUM(importe_global) as total_monto')
            )
            ->groupBy('sede')
            ->orderBy('total_monto', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tipo' => 'ventas_sede',
                'sedes' => $sedes
            ]
        ]);
    }

    private function totalFacturacion()
    {
        $anio = date('Y');

        $facturacion = DB::table('ventas')
            ->select(
                DB::raw('COUNT(*) as total_facturas'),
                DB::raw('SUM(importe_global) as total_facturado'),
                DB::raw('SUM(igv) as total_igv')
            )
            ->where('anio', $anio)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tipo' => 'facturacion',
                'anio' => $anio,
                'total_facturas' => $facturacion->total_facturas,
                'total_facturado' => number_format($facturacion->total_facturado, 2),
                'total_igv' => number_format($facturacion->total_igv, 2)
            ]
        ]);
    }

    private function contains($str, $keywords)
    {
        foreach ($keywords as $keyword) {
            if (stripos($str, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
}
