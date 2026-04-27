<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Motorizado;
use App\Models\GpsRuta;
use App\Models\Entrega;
use Illuminate\Support\Facades\Auth;

class TrackingAdminController extends Controller
{
    private function checkPermiso()
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);
    }

    // ── Mapa en vivo ──────────────────────────────────────
    public function mapaVivo()
    {
        $this->checkPermiso();
        $sedes = Motorizado::SEDES;
        return view('tracking.mapa-vivo', compact('sedes'));
    }

    // ── Resumen diario ────────────────────────────────────
    public function resumenDiario(Request $request)
    {
        $this->checkPermiso();

        $fecha = $request->get('fecha', today()->toDateString());

        $rutas = GpsRuta::with(['motorizado', 'entregas'])
            ->whereDate('fecha', $fecha)
            ->get();

        $kpi = [
            'total_rutas'   => $rutas->count(),
            'total_km'      => round($rutas->sum('distance_km'), 2),
            'completadas'   => $rutas->flatMap->entregas->where('estado', 'completado')->count(),
            'fallidas'      => $rutas->flatMap->entregas->where('estado', 'fallido')->count(),
            'en_ruta'       => $rutas->where('status', 'activa')->count(),
        ];

        $porSede = $rutas->groupBy('motorizado.sede')->map(fn($g) => [
            'rutas'    => $g->count(),
            'km'       => round($g->sum('distance_km'), 2),
            'entregas' => $g->flatMap->entregas->count(),
        ]);

        return view('tracking.resumen-diario', compact('rutas', 'kpi', 'porSede', 'fecha'));
    }

    // ── Historial km ──────────────────────────────────────
    public function historialKm(Request $request)
    {
        $this->checkPermiso();

        $motorizados = Motorizado::where('estado', 'activo')
            ->orderBy('sede')->orderBy('nombre')->get();

        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', today()->toDateString());
        $motorizadoId = $request->get('motorizado_id');

        $query = GpsRuta::with(['motorizado', 'entregas'])
            ->whereBetween('fecha', [$desde, $hasta])
            ->where('status', 'completada')
            ->orderByDesc('fecha');

        if ($motorizadoId) $query->where('motorizado_id', $motorizadoId);

        $rutas = $query->paginate(30)->withQueryString();

        return view('tracking.historial-km', compact(
            'rutas', 'motorizados', 'desde', 'hasta', 'motorizadoId'
        ));
    }

    // ── Gestión motorizados ───────────────────────────────
    public function motorizados()
    {
        $this->checkPermiso();
        $motorizados = Motorizado::withTrashed()->orderBy('sede')->get();
        $sedes = Motorizado::SEDES;
        return view('tracking.motorizados', compact('motorizados', 'sedes'));
    }

    public function storeMotorizado(Request $request)
    {
        $this->checkPermiso();

        $data = $request->validate([
            'nombre'   => 'required|string|max:255',
            'sede'     => 'required|string',
            'telefono' => 'nullable|string|max:50',
            'email'    => 'required|email|unique:motorizados,email',
            'password' => 'required|string|min:6',
            'estado'   => 'required|in:activo,inactivo',
        ]);

        $motorizado = Motorizado::create($data);

        return response()->json(['success' => true, 'motorizado' => $motorizado]);
    }

    public function updateMotorizado(Request $request, int $id)
    {
        $this->checkPermiso();

        $motorizado = Motorizado::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'nombre'   => 'required|string|max:255',
            'sede'     => 'required|string',
            'telefono' => 'nullable|string|max:50',
            'email'    => "required|email|unique:motorizados,email,{$id}",
            'password' => 'nullable|string|min:6',
            'estado'   => 'required|in:activo,inactivo',
        ]);

        if (empty($data['password'])) unset($data['password']);

        $motorizado->update($data);

        return response()->json(['success' => true]);
    }

    public function destroyMotorizado(int $id)
    {
        $this->checkPermiso();
        Motorizado::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    // ── Gestión entregas ──────────────────────────────────
    public function entregas(Request $request)
    {
        $this->checkPermiso();

        $motorizados = Motorizado::where('estado', 'activo')
            ->orderBy('nombre')->get();

        $sedes = Motorizado::SEDES;

        $query = Entrega::with(['motorizado', 'ruta'])
            ->orderByDesc('id');

        if ($request->filled('sede'))         $query->where('sede', $request->sede);
        if ($request->filled('estado'))       $query->where('estado', $request->estado);
        if ($request->filled('motorizado_id')) $query->where('motorizado_id', $request->motorizado_id);

        $entregas = $query->paginate(25)->withQueryString();

        return view('tracking.entregas', compact('entregas', 'motorizados', 'sedes'));
    }

    public function storeEntrega(Request $request)
    {
        $this->checkPermiso();

        $data = $request->validate([
            'motorizado_id'    => 'required|exists:motorizados,id',
            'ruta_id'          => 'required|exists:gps_rutas,id',
            'cliente_nombre'   => 'required|string|max:255',
            'cliente_telefono' => 'nullable|string|max:50',
            'referencia'       => 'nullable|string|max:100',
            'direccion'        => 'required|string',
            'latitud'          => 'nullable|numeric',
            'longitud'         => 'nullable|numeric',
            'orden_secuencia'  => 'required|integer|min:1',
            'sede'             => 'required|string',
            'notas'            => 'nullable|string',
        ]);

        $entrega = Entrega::create($data);

        return response()->json(['success' => true, 'entrega' => $entrega]);
    }
}
