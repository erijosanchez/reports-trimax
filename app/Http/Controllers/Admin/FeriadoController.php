<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feriado;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class FeriadoController extends Controller
{
    public function index(Request $request)
    {
        $anio = (int) $request->input('anio', now('America/Lima')->year);

        $feriados = Feriado::whereYear('fecha', $anio)
            ->orderBy('fecha')
            ->get();

        $aniosDisponibles = Feriado::selectRaw('DISTINCT YEAR(fecha) as anio')
            ->orderByDesc('anio')
            ->pluck('anio');

        return view('admin.feriados', compact('feriados', 'anio', 'aniosDisponibles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha'  => 'required|date',
            'motivo' => 'required|string|max:255',
            'tipo'   => 'required|in:nacional,regional',
        ]);

        $feriado = Feriado::guardar($data['fecha'], $data['motivo'], $data['tipo'], 'manual');

        ActivityLogService::log(auth()->id(), 'crear_feriado', 'Feriado', $feriado->id, "Registró feriado {$data['fecha']} ({$data['motivo']})");

        return back()->with('success', 'Feriado guardado correctamente.');
    }

    public function destroy(Feriado $feriado)
    {
        ActivityLogService::log(auth()->id(), 'eliminar_feriado', 'Feriado', $feriado->id, "Eliminó feriado {$feriado->fecha->toDateString()} ({$feriado->motivo})");

        $feriado->delete();

        return back()->with('success', 'Feriado eliminado.');
    }

    /**
     * Vista previa: corre feriados:sync en --dry-run, no escribe nada en la
     * BD. El admin revisa esta lista ANTES de decidir si aplica el sync
     * real — evita que un cambio de estructura en gob.pe se cuele directo
     * a producción sin que nadie lo vea primero.
     */
    public function preview(Request $request)
    {
        $anio = (int) $request->input('anio', now('America/Lima')->year);

        $exitCode = Artisan::call('feriados:sync', ['--anio' => $anio, '--dry-run' => true]);
        $salida   = trim(Artisan::output());

        return back()->with($exitCode === 0 ? 'preview' : 'error', $salida ?: 'Sin resultados.')
            ->with('preview_anio', $anio);
    }

    /**
     * Aplica de verdad lo que se vio en la vista previa: agrega/actualiza
     * por fecha (fuente 'gob.pe'), nunca reemplaza el CRUD manual a ciegas.
     */
    public function sync(Request $request)
    {
        $anio = (int) $request->input('anio', now('America/Lima')->year);

        $exitCode = Artisan::call('feriados:sync', ['--anio' => $anio]);
        $salida   = trim(Artisan::output());

        ActivityLogService::log(auth()->id(), 'sincronizar_feriados', 'Feriado', null, "Sincronizó feriados {$anio} desde gob.pe: {$salida}");

        return back()->with($exitCode === 0 ? 'success' : 'error', $salida ?: 'Sincronización finalizada.');
    }
}
