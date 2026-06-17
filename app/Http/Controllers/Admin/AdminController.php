<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserSession;
use App\Models\UserActivityLog;
use App\Models\IpBlacklist;
use App\Models\FailedLoginAttempt;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'users_online' => User::online()->count(),
            'total_sessions_today' => UserSession::whereDate('login_at', today())->count(),
            'failed_logins_today' => FailedLoginAttempt::whereDate('attempted_at', today())->count(),
            'blocked_ips' => IpBlacklist::active()->count(),
        ];

        $usersOnline = User::online()
            ->with('activeSessions')
            ->get();

        // All users with their latest session for "last seen" info
        $allUsersStatus = User::with(['sessions' => function ($q) {
            $q->orderByDesc('last_activity')->limit(1);
        }])->get()->map(function ($user) {
            return [
                'id'           => $user->id,
                'name'         => $user->name,
                'is_online'    => $user->isOnline(),
                'last_seen'    => $user->lastSeenText(),
                'last_activity'=> $user->lastActivityAt()?->toISOString(),
            ];
        })->sortByDesc('is_online')->values();

        $recentActivity = UserActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'usersOnline', 'allUsersStatus', 'recentActivity'));
    }

    public function onlineStatusApi()
    {
        $users = User::with(['sessions' => function ($q) {
            $q->orderByDesc('last_activity')->limit(1);
        }])->get();

        $mapped = $users->map(function ($user) {
            return [
                'id'            => $user->id,
                'name'          => $user->name,
                'is_online'     => $user->isOnline(),
                'last_seen'     => $user->lastSeenText(),
                'last_activity' => $user->lastActivityAt()?->toISOString(),
            ];
        })->sortByDesc('is_online')->values();

        return response()->json([
            'users'        => $mapped,
            'online_count' => $users->filter(fn($u) => $u->isOnline())->count(),
            'updated_at'   => now('America/Lima')->format('H:i:s'),
        ]);
    }

    public function users()
    {
        $users = User::with('roles')
            ->withCount('sessions')
            ->paginate(10);

        // Collect online IDs for quick lookup in view
        $onlineIds = User::online()->pluck('id')->flip();

        // All users with last seen info for sidebar panel
        $allUsersStatus = User::with(['sessions' => function ($q) {
            $q->orderByDesc('last_activity')->limit(1);
        }])->get()->map(function ($user) {
            return [
                'id'        => $user->id,
                'name'      => $user->name,
                'is_online' => $user->isOnline(),
                'last_seen' => $user->lastSeenText(),
            ];
        })->sortByDesc('is_online')->values();

        return view('admin.users', compact('users', 'onlineIds', 'allUsersStatus'));
    }

    public function activityLogs(Request $request)
    {
        $logs = $this->buildActivityLogQuery($request)
            ->with('user')
            ->latest()
            ->paginate(50)
            ->withQueryString();

        // Usuarios para el filtro (dropdown).
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        // Estadísticas reales (no calculadas en JS sobre la página actual).
        $stats = [
            'logins_today'  => UserActivityLog::where('action', 'login')
                ->whereDate('created_at', today())->count(),
            'deletes_today' => UserActivityLog::where('action', 'like', 'delete%')
                ->whereDate('created_at', today())->count(),
            'active_users'  => UserActivityLog::whereDate('created_at', today())
                ->distinct('user_id')->count('user_id'),
            'failed_today'  => UserActivityLog::where('action', 'login_failed')
                ->whereDate('created_at', today())->count(),
        ];

        return view('admin.activity-logs', compact('logs', 'users', 'stats'));
    }

    /**
     * Exporta los logs de actividad a CSV respetando los filtros activos.
     */
    public function exportActivityLogs(Request $request): StreamedResponse
    {
        $filename = 'logs_actividad_' . now('America/Lima')->format('Y-m-d_His') . '.csv';

        $query = $this->buildActivityLogQuery($request)->with('user')->latest();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            // BOM para que Excel reconozca UTF-8 (tildes/ñ).
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Fecha/Hora', 'Usuario', 'Email', 'Acción', 'Recurso', 'Descripción', 'IP', 'Método', 'URL', 'Estado']);

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $log) {
                    fputcsv($out, [
                        optional($log->created_at)->format('Y-m-d H:i:s'),
                        $log->user->name ?? 'N/D',
                        $log->user->email ?? '',
                        $log->action,
                        trim(($log->resource_type ?? '') . ' ' . ($log->resource_id ?? '')),
                        $log->description,
                        $log->ip_address,
                        $log->request_method,
                        $log->request_url,
                        $log->response_status,
                    ]);
                }
            });

            fclose($out);
        }, $filename, $headers);
    }

    /**
     * Construye la query de logs aplicando los filtros del request.
     * Compartido entre la vista paginada y la exportación CSV.
     */
    private function buildActivityLogQuery(Request $request)
    {
        return UserActivityLog::query()
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('action'), function ($q) use ($request) {
                // Acciones completas → coincidencia exacta; grupos por verbo
                // (create/update/delete) → prefijo (ej. create_user, delete_dashboard).
                $exactas = ['login', 'logout', 'login_failed', 'validation_failed', 'access_denied', 'csrf_expired', 'server_error', 'action_failed'];
                in_array($request->action, $exactas, true)
                    ? $q->where('action', $request->action)
                    : $q->where('action', 'like', $request->action . '%');
            })
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('description', 'like', $term)
                        ->orWhere('ip_address', 'like', $term)
                        ->orWhere('action', 'like', $term);
                });
            });
    }

    public function security()
    {
        $blockedIps = IpBlacklist::active()
            ->latest()
            ->paginate(20);

        $recentFailedAttempts = FailedLoginAttempt::latest('attempted_at')
            ->limit(50)
            ->get();

        $usageStats = User::with('sessions')
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'total_time' => $user->totalUsageTime(),
                    'sessions_count' => $user->sessions->count(),
                ];
            })
            ->sortByDesc('total_time')
            ->take(10);

        return view('admin.security', compact('blockedIps', 'recentFailedAttempts', 'usageStats'));
    }
}
