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
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin']);
    }

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

        $recentActivity = UserActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'usersOnline', 'recentActivity'));
    }

    public function users()
    {
        $users = User::with('roles')
            ->withCount('sessions')
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function usersOnline()
    {
        $usersOnline = User::online()
            ->with(['activeSessions' => function ($query) {
                $query->latest('last_activity');
            }])
            ->get();

        return view('admin.users-online', compact('usersOnline'));
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

        return view('admin.security', compact('blockedIps', 'recentFailedAttempts'));
    }

    public function analytics()
    {
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

        return view('admin.analytics', compact('usageStats'));
    }
}
