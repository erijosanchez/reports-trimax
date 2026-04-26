<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\Motorizado;
use App\Models\RutaGps;
use App\Services\ActivityLogService;
use App\Services\RutaGpsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RutaGpsController extends Controller
{
    public function __construct(protected RutaGpsService $service) {}

    private function sedeFiltro(): ?string
    {
        $user = Auth::user();
        return $user->isSede() ? $user->sede : null;
    }

    // ─── VISTA PRINCIPAL (admin) ──────────────────────────────

    public function index(Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $sedeFiltro = $this->sedeFiltro();

        $motorizadosQuery = Motorizado::where('estado', 'activo')->orderBy('sede')->orderBy('nombre');
        if ($sedeFiltro) $motorizadosQuery->where('sede', $sedeFiltro);
        $motorizados = $motorizadosQuery->get();

        // Rutas activas indexadas por motorizado_id para mostrar estado por conductor
        $rutasActivasQuery = RutaGps::where('status', RutaGps::STATUS_ACTIVE);
        if ($sedeFiltro) {
            $rutasActivasQuery->whereHas('motorizado', fn($q) => $q->where('sede', $sedeFiltro));
        }
        $rutasActivas = $rutasActivasQuery->get()->keyBy('motorizado_id');

        $historialQuery = RutaGps::with('motorizado')->orderByDesc('started_at');
        if ($sedeFiltro) {
            $historialQuery->whereHas('motorizado', fn($q) => $q->where('sede', $sedeFiltro));
        }
        if ($request->filled('motorizado_id')) {
            $historialQuery->where('motorizado_id', $request->motorizado_id);
        }
        if ($request->filled('fecha')) {
            $historialQuery->whereDate('started_at', $request->fecha);
        }
        $historial = $historialQuery->paginate(30)->withQueryString();

        return view('tracking.rutas-gps', compact('motorizados', 'rutasActivas', 'historial'));
    }

    // ─── GENERAR TOKEN GPS ────────────────────────────────────

    public function generarToken(int $id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $motorizado = Motorizado::findOrFail($id);

        if (!$motorizado->token_gps) {
            $token = substr(
                hash('sha256', $id . $motorizado->nombre . config('app.key') . microtime()),
                0, 40
            );
            $motorizado->update(['token_gps' => $token]);
        }

        $url = url("/gps/{$motorizado->token_gps}");

        $whatsapp = 'https://wa.me/'
            . preg_replace('/\D/', '', $motorizado->telefono ?? '')
            . '?text=' . urlencode(
                "Hola {$motorizado->nombre} 👋\n"
                . "Este es tu enlace personal para registrar tus rutas GPS:\n\n"
                . $url . "\n\n"
                . "Ábrelo desde tu celular y guárdalo en favoritos. ⭐"
            );

        ActivityLogService::log(
            Auth::id(), 'generar_token_gps', 'Motorizado', $motorizado->id,
            "Generó enlace GPS para {$motorizado->nombre}"
        );

        return response()->json([
            'success'  => true,
            'url'      => $url,
            'whatsapp' => $whatsapp,
            'token'    => $motorizado->token_gps,
        ]);
    }

    // ─── FORZAR CIERRE (admin — emergencia) ──────────────────

    public function forzarCierre(int $id)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);
        abort_if(Auth::user()->isSede(), 403);

        $ruta = RutaGps::with('motorizado')->findOrFail($id);

        if ($ruta->status === RutaGps::STATUS_COMPLETED) {
            return response()->json(['success' => false, 'message' => 'La ruta ya está completada.'], 422);
        }

        $resultado = $this->service->finalizar($ruta);

        ActivityLogService::log(
            Auth::id(), 'forzar_cierre_ruta_gps', 'RutaGps', $ruta->id,
            "Forzó el cierre de ruta GPS de {$ruta->motorizado->nombre}: {$resultado['distance_km']} km"
        );

        return response()->json(['success' => true] + $resultado);
    }

    // ─── API JSON ─────────────────────────────────────────────

    public function apiRutas(Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $sedeFiltro = $this->sedeFiltro();

        $query = RutaGps::with('motorizado')
            ->where('status', RutaGps::STATUS_COMPLETED)
            ->whereNotNull('polyline');

        if ($sedeFiltro) {
            $query->whereHas('motorizado', fn($q) => $q->where('sede', $sedeFiltro));
        }
        if ($request->filled('motorizado_id')) {
            $query->where('motorizado_id', $request->motorizado_id);
        }
        if ($request->filled('fecha')) {
            $query->whereDate('started_at', $request->fecha);
        }

        $rutas = $query->orderByDesc('started_at')->limit(50)->get();

        return response()->json($rutas->map(fn($r) => [
            'id'          => $r->id,
            'motorizado'  => $r->motorizado->nombre,
            'sede'        => $r->motorizado->sede,
            'started_at'  => $r->started_at->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'ended_at'    => $r->ended_at?->setTimezone('America/Lima')->format('d/m/Y H:i'),
            'distance_km' => (float) $r->distance_km,
            'polyline'    => $r->polyline ?? [],
        ])->values());
    }

    public function resumenDiario(Request $request)
    {
        abort_unless(Auth::user()->puedeVerMotorizados(), 403);

        $sedeFiltro = $this->sedeFiltro();
        $fecha      = $request->get('fecha', today()->toDateString());

        $query = RutaGps::with('motorizado')
            ->whereDate('started_at', $fecha)
            ->where('status', RutaGps::STATUS_COMPLETED);

        if ($sedeFiltro) {
            $query->whereHas('motorizado', fn($q) => $q->where('sede', $sedeFiltro));
        }
        if ($request->filled('motorizado_id')) {
            $query->where('motorizado_id', $request->motorizado_id);
        }

        $rutas = $query->get();

        $resumen = $rutas->groupBy('motorizado_id')->map(function ($grupo) {
            $m = $grupo->first()->motorizado;
            return [
                'motorizado' => $m->nombre,
                'sede'       => $m->sede,
                'rutas'      => $grupo->count(),
                'total_km'   => round($grupo->sum(fn($r) => (float) $r->distance_km), 2),
            ];
        })->sortBy('motorizado')->values();

        return response()->json(['fecha' => $fecha, 'resumen' => $resumen]);
    }
}
