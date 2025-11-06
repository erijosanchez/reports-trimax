<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public static function getUserStats(int $userId): array
    {
        $user = User::find($userId);

        if (!$user) {
            return [];
        }

        return [
            'total_sessions' => $user->sessions()->count(),
            'total_time' => $user->totalUsageTime(),
            'total_time_formatted' => self::formatSeconds($user->totalUsageTime()),
            'last_login' => $user->last_login_at,
            'is_online' => $user->isOnline(),
            'total_files' => $user->uploadedFiles()->count(),
            'total_activities' => $user->activityLogs()->count(),
        ];
    }

    public static function getTopUsersByUsage(int $limit = 10): array
    {
        return User::withCount('sessions')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'total_time' => $user->totalUsageTime(),
                    'sessions_count' => $user->sessions_count,
                    'avg_session_time' => $user->sessions_count > 0
                        ? $user->totalUsageTime() / $user->sessions_count
                        : 0,
                ];
            })
            ->sortByDesc('total_time')
            ->take($limit)
            ->values()
            ->all();
    }

    public static function getActivityStats(int $days = 7): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_logins' => UserActivityLog::where('action', 'login')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'total_views' => UserActivityLog::where('action', 'view_dashboard')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'total_uploads' => UserActivityLog::where('action', 'upload_file')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'active_users' => UserActivityLog::where('created_at', '>=', $startDate)
                ->distinct('user_id')
                ->count(),
        ];
    }

    protected static function formatSeconds(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%d horas, %d minutos', $hours, $minutes);
    }

    public static function getDailyActivityChart(int $days = 30): array
    {
        $data = UserActivityLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }
}
