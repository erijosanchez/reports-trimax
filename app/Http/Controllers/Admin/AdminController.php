<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserSession;
use App\Models\UserActivityLog;
use App\Models\IpBlacklist;
use App\Models\FailedLoginAttempt;

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

    public function activityLogs()
    {
        $logs = UserActivityLog::with('user')
            ->latest()
            ->paginate(50);

        return view('admin.activity-logs', compact('logs'));
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
