<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenesXUsuarioController extends Controller
{
    /** Usuarios de prueba excluidos de todos los reportes */
    private const USUARIOS_EXCLUIDOS = [
        'JAIME RL AREQUIPA', 'JAIME RL ATE', 'JAIME RL CAILLOMA', 'JAIME RL COMAS',
        'JAIME RL HUANUCO', 'JAIME RL LINCE', 'JAIME RL NAPO', 'JAIME RL SJM',
        'Juan Loayza Pacheco', 'Juan  Loayza Pacheco',
        'Rafael Mendez', 'Rafael ML', 'RUTH VASQUEZ ALCÁNTARA',
    ];

    public function index()
    {
        $user = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            abort(403, 'Sin permiso para acceder a Órdenes x Usuario.');
        }

        // Lista de sedes disponibles según rol
        $sedesDisponibles = $this->sedesDisponibles($user);

        // Usuarios del sistema con rol sede, filtrados por sede según rol
        $usuariosSistema = $this->usuariosSistema($user);

        return view('productividad.ordenes-x-usuario.index', compact(
            'sedesDisponibles',
            'usuariosSistema',
        ));
    }

    // ── API data ──────────────────────────────────────────────────

    public function data(Request $request)
    {
        $user = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            return response()->json(['error' => 'Sin permiso.'], 403);
        }

        $modo          = $request->get('modo', 'dia'); // 'dia' | 'mes'
        $sede          = $request->get('sede');
        $usuarioNombre = $request->get('usuario');
        $page          = max(1, (int) $request->get('page', 1));
        $perPage       = 50;

        // Forzar sede para rol sede
        if ($user->isSede() && !$user->isAdmin() && !$user->isSuperAdmin()) {
            $sede = $user->sede;
        }

        // ── Consulta base ──────────────────────────────────────────
        $query = DB::table('ordenes_historico')
            ->whereNotNull('nombre_usuario')
            ->where('nombre_usuario', '!=', '')
            ->whereNotIn('nombre_usuario', self::USUARIOS_EXCLUIDOS);

        // Filtro de período
        if ($modo === 'mes') {
            $mes = $request->get('mes', date('Y-m'));
            if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
                $mes = date('Y-m');
            }
            [$year, $month] = explode('-', $mes);
            $query->whereYear('fecha_orden', $year)->whereMonth('fecha_orden', $month);
            $periodoLabel = $mes;
        } else {
            $fecha = $request->get('fecha', date('Y-m-d'));
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                $fecha = date('Y-m-d');
            }
            $query->where('fecha_orden', $fecha);
            $periodoLabel = $fecha;
        }

        if ($sede) {
            $query->where('descripcion_sede', $sede);
        }

        if ($usuarioNombre) {
            $query->where('nombre_usuario', $usuarioNombre);
        }

        // ── Órdenes por hora por usuario ───────────────────────────
        $porHora = (clone $query)
            ->selectRaw("
                nombre_usuario,
                LPAD(
                    CAST(SUBSTRING_INDEX(TRIM(hora_orden), ':', 1) AS UNSIGNED),
                    2, '0'
                ) as hora,
                COUNT(*) as total
            ")
            ->whereNotNull('hora_orden')
            ->where('hora_orden', '!=', '')
            ->whereRaw("TRIM(hora_orden) REGEXP '^[0-9]'")
            ->groupBy('nombre_usuario', 'hora')
            ->orderBy('hora')
            ->get();

        // ── Órdenes totales por hora (sin usuario) ─────────────────
        $porHoraTotal = (clone $query)
            ->selectRaw("
                LPAD(
                    CAST(SUBSTRING_INDEX(TRIM(hora_orden), ':', 1) AS UNSIGNED),
                    2, '0'
                ) as hora,
                COUNT(*) as total
            ")
            ->whereNotNull('hora_orden')
            ->where('hora_orden', '!=', '')
            ->whereRaw("TRIM(hora_orden) REGEXP '^[0-9]'")
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        // ── Órdenes totales por usuario ────────────────────────────
        $porUsuario = (clone $query)
            ->selectRaw('nombre_usuario, COUNT(*) as total')
            ->groupBy('nombre_usuario')
            ->orderByDesc('total')
            ->get();

        // ── Tabla detallada con paginación ─────────────────────────
        $tablaQuery = (clone $query)
            ->select('nombre_usuario', 'descripcion_sede', 'numero_orden', 'fecha_orden', 'hora_orden', 'estado_orden', 'tipo_orden', 'cliente')
            ->orderByRaw("CAST(SUBSTRING_INDEX(TRIM(COALESCE(hora_orden,'')), ':', 1) AS UNSIGNED)")
            ->orderBy('nombre_usuario');

        $totalFilas = (clone $tablaQuery)->count();
        $tabla      = $tablaQuery->offset(($page - 1) * $perPage)->limit($perPage)->get();
        $totalPages = (int) ceil($totalFilas / $perPage);

        // ── Órdenes por semana (todas hasta hoy, sin filtro de fecha)
        $querySemana = DB::table('ordenes_historico')
            ->whereNotNull('nombre_usuario')
            ->where('nombre_usuario', '!=', '')
            ->whereNotIn('nombre_usuario', self::USUARIOS_EXCLUIDOS)
            ->where('fecha_orden', '<=', date('Y-m-d'));

        if ($sede) {
            $querySemana->where('descripcion_sede', $sede);
        }
        if ($usuarioNombre) {
            $querySemana->where('nombre_usuario', $usuarioNombre);
        }

        $porSemanaTurnoRaw = $querySemana
            ->selectRaw("
                YEARWEEK(fecha_orden, 1) as yearweek,
                SUM(CASE WHEN CAST(SUBSTRING_INDEX(TRIM(COALESCE(hora_orden,'')), ':', 1) AS UNSIGNED) < 17 THEN 1 ELSE 0 END) as antes_5pm,
                SUM(CASE WHEN CAST(SUBSTRING_INDEX(TRIM(COALESCE(hora_orden,'')), ':', 1) AS UNSIGNED) >= 17 THEN 1 ELSE 0 END) as despues_5pm,
                COUNT(*) as total
            ")
            ->groupBy('yearweek')
            ->orderBy('yearweek')
            ->get();

        $porSemanaTurno = $porSemanaTurnoRaw->map(function ($row) {
            $yw     = str_pad((string) $row->yearweek, 6, '0', STR_PAD_LEFT);
            $year   = (int) substr($yw, 0, 4);
            $week   = (int) substr($yw, 4);
            $monday = new \DateTime();
            $monday->setISODate($year, $week, 1);
            $sunday = (clone $monday)->modify('+6 days');
            return [
                'label'      => 'Sem. ' . $monday->format('d/m') . '–' . $sunday->format('d/m'),
                'antes_5pm'  => (int) $row->antes_5pm,
                'despues_5pm'=> (int) $row->despues_5pm,
                'total'      => (int) $row->total,
            ];
        });

        // ── Órdenes por usuario divididas antes/después de 5pm ────
        $porUsuarioTurno = (clone $query)
            ->selectRaw("
                nombre_usuario,
                SUM(CASE WHEN CAST(SUBSTRING_INDEX(TRIM(COALESCE(hora_orden,'')), ':', 1) AS UNSIGNED) < 17 THEN 1 ELSE 0 END) as antes_5pm,
                SUM(CASE WHEN CAST(SUBSTRING_INDEX(TRIM(COALESCE(hora_orden,'')), ':', 1) AS UNSIGNED) >= 17 THEN 1 ELSE 0 END) as despues_5pm,
                COUNT(*) as total
            ")
            ->groupBy('nombre_usuario')
            ->orderByDesc('total')
            ->get();

        // ── Construir datasets chart por hora ──────────────────────
        $usuarios = $porHora->pluck('nombre_usuario')->unique()->values();

        // Rango de horas real en los datos (mínimo 07-19)
        $horasNumericas = $porHora->pluck('hora')
            ->merge($porHoraTotal->pluck('hora'))
            ->map(fn($h) => (int) $h)
            ->filter(fn($h) => $h >= 0 && $h <= 23)
            ->sort()->values();

        $horaMin = $horasNumericas->isNotEmpty() ? max(0,  $horasNumericas->first() - 1) : 7;
        $horaMax = $horasNumericas->isNotEmpty() ? min(23, $horasNumericas->last()  + 1) : 19;
        $horas   = collect(range($horaMin, $horaMax))
            ->map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT))
            ->toArray();

        $colores = [
            '#2563eb','#10b981','#f59e0b','#ef4444','#8b5cf6',
            '#06b6d4','#ec4899','#84cc16','#f97316','#6366f1',
            '#14b8a6','#f43f5e','#a855f7','#0ea5e9','#22c55e',
        ];

        $datasetsHora = [];
        foreach ($usuarios as $idx => $uNombre) {
            $datos = [];
            foreach ($horas as $h) {
                $found = $porHora->first(fn($r) => $r->nombre_usuario === $uNombre && $r->hora === $h);
                $datos[] = $found ? (int) $found->total : 0;
            }
            $color = $colores[$idx % count($colores)];
            $datasetsHora[] = [
                'label'           => $uNombre,
                'data'            => $datos,
                'backgroundColor' => $color . 'cc',
                'borderColor'     => $color,
                'borderWidth'     => 1,
            ];
        }

        // ── Dataset chart por hora total ───────────────────────────
        $dataTotalHora = array_map(function ($h) use ($porHoraTotal) {
            $found = $porHoraTotal->first(fn($r) => $r->hora === $h);
            return $found ? (int) $found->total : 0;
        }, $horas);

        return response()->json([
            'porHora' => [
                'labels'   => array_map(fn($h) => "{$h}:00", $horas),
                'datasets' => $datasetsHora,
            ],
            'porHoraTotal' => [
                'labels' => array_map(fn($h) => "{$h}:00", $horas),
                'data'   => $dataTotalHora,
            ],
            'porUsuario' => [
                'labels' => $porUsuario->pluck('nombre_usuario')->toArray(),
                'data'   => $porUsuario->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            ],
            'porUsuarioTurno' => [
                'labels'      => $porUsuarioTurno->pluck('nombre_usuario')->toArray(),
                'antes_5pm'   => $porUsuarioTurno->pluck('antes_5pm')->map(fn($v) => (int) $v)->toArray(),
                'despues_5pm' => $porUsuarioTurno->pluck('despues_5pm')->map(fn($v) => (int) $v)->toArray(),
            ],
            'porSemanaTurno' => [
                'labels'      => $porSemanaTurno->pluck('label')->toArray(),
                'antes_5pm'   => $porSemanaTurno->pluck('antes_5pm')->toArray(),
                'despues_5pm' => $porSemanaTurno->pluck('despues_5pm')->toArray(),
                'totales'     => $porSemanaTurno->pluck('total')->toArray(),
            ],
            'tabla'      => $tabla,
            'paginacion' => [
                'page'       => $page,
                'per_page'   => $perPage,
                'total'      => $totalFilas,
                'total_pages'=> $totalPages,
            ],
            'totales'    => [
                'ordenes'  => $porUsuario->sum('total'),
                'usuarios' => $porUsuario->count(),
                'periodo'  => $periodoLabel,
                'modo'     => $modo,
            ],
        ]);
    }

    // ── Usuarios para el filtro (AJAX) ─────────────────────────────

    public function usuarios(Request $request)
    {
        $user = auth()->user();

        if (!$user->puedeVerCobranzaSedes()) {
            return response()->json([], 403);
        }

        $sede  = $request->get('sede');
        $modo  = $request->get('modo', 'dia');
        $fecha = $request->get('fecha');
        $mes   = $request->get('mes');

        if ($user->isSede() && !$user->isAdmin() && !$user->isSuperAdmin()) {
            $sede = $user->sede;
        }

        $query = DB::table('ordenes_historico')
            ->selectRaw('DISTINCT nombre_usuario')
            ->whereNotNull('nombre_usuario')
            ->where('nombre_usuario', '!=', '')
            ->whereNotIn('nombre_usuario', self::USUARIOS_EXCLUIDOS);

        if ($sede) {
            $query->where('descripcion_sede', $sede);
        }

        if ($modo === 'mes' && $mes && preg_match('/^\d{4}-\d{2}$/', $mes)) {
            [$year, $month] = explode('-', $mes);
            $query->whereYear('fecha_orden', $year)->whereMonth('fecha_orden', $month);
        } elseif ($fecha && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $query->where('fecha_orden', $fecha);
        }

        $lista = $query->orderBy('nombre_usuario')->pluck('nombre_usuario');

        return response()->json($lista);
    }

    // ── Privados ───────────────────────────────────────────────────

    private function sedesDisponibles(User $user): array
    {
        if ($user->isSede() && !$user->isAdmin() && !$user->isSuperAdmin()) {
            return $user->sede ? [$user->sede] : [];
        }

        return DB::table('ordenes_historico')
            ->selectRaw('DISTINCT descripcion_sede')
            ->whereNotNull('descripcion_sede')
            ->where('descripcion_sede', '!=', '')
            ->orderBy('descripcion_sede')
            ->pluck('descripcion_sede')
            ->toArray();
    }

    private function usuariosSistema(User $user): \Illuminate\Support\Collection
    {
        $q = User::where('is_active', true)->whereNotNull('sede');

        if ($user->isSede() && !$user->isAdmin() && !$user->isSuperAdmin()) {
            $q->where('sede', $user->sede);
        }

        return $q->orderBy('name')->get(['id', 'name', 'sede']);
    }
}
