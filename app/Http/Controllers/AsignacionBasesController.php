<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AsignacionBasesService;
use Carbon\Carbon;

class AsignacionBasesController extends Controller
{
    protected AsignacionBasesService $service;

    public function __construct(AsignacionBasesService $service)
    {
        $this->service = $service;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EVOLUTIVO
    // ──────────────────────────────────────────────────────────────────────────

    public function evolutivo(Request $request)
    {
        $aniosDisponibles = $this->service->getAniosDisponibles();
        $anioActual       = $request->get('anio', $aniosDisponibles[0] ?? now()->year);

        $semanal  = $this->service->getEvolutivoSemanal((int) $anioActual);
        $mensual  = $this->service->getEvolutivoMensual((int) $anioActual);
        $grafLinea = $this->service->getGraficoLinealDiario((int) $anioActual);
        $grafBarras = $this->service->getGraficoBarrasMensual((int) $anioActual);

        return view('produccion.asignacion-bases.evolutivo', compact(
            'aniosDisponibles',
            'anioActual',
            'semanal',
            'mensual',
            'grafLinea',
            'grafBarras'
        ));
    }

    /**
     * AJAX: refresca todos los datos del evolutivo para un año dado.
     */
    public function getEvolutivoData(Request $request)
    {
        $anio = (int) $request->get('anio', now()->year);

        return response()->json([
            'semanal'    => $this->service->getEvolutivoSemanal($anio),
            'mensual'    => $this->service->getEvolutivoMensual($anio),
            'grafLinea'  => $this->service->getGraficoLinealDiario($anio),
            'grafBarras' => $this->service->getGraficoBarrasMensual($anio),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DEMANDA
    // ──────────────────────────────────────────────────────────────────────────

    public function demanda(Request $request)
    {
        $aniosDisponibles = $this->service->getAniosDisponibles();
        $anioActual       = (int) $request->get('anio', $aniosDisponibles[0] ?? now()->year);
        $mesActual        = (int) $request->get('mes', now()->month);

        $semanal  = $this->service->getDemandaSemanal($anioActual, $mesActual);
        $mensual  = $this->service->getDemandaMensual($anioActual);

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

        return view('produccion.asignacion-bases.demanda', compact(
            'aniosDisponibles',
            'anioActual',
            'mesActual',
            'mesesNombres',
            'semanal',
            'mensual'
        ));
    }

    /**
     * AJAX: refresca la tabla semanal de demanda para año/mes dado.
     */
    public function getDemandaSemanalData(Request $request)
    {
        $anio = (int) $request->get('anio', now()->year);
        $mes  = (int) $request->get('mes',  now()->month);

        return response()->json(
            $this->service->getDemandaSemanal($anio, $mes)
        );
    }

    /**
     * AJAX: refresca la tabla mensual de demanda para año dado.
     */
    public function getDemandaMensualData(Request $request)
    {
        $anio = (int) $request->get('anio', now()->year);

        return response()->json(
            $this->service->getDemandaMensual($anio)
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CACHE
    // ──────────────────────────────────────────────────────────────────────────

    public function clearCache()
    {
        $this->service->clearCache();

        return response()->json(['message' => 'Caché limpiada correctamente']);
    }
}
