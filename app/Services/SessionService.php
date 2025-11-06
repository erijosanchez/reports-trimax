<?php

namespace App\Services;

use App\Models\UserSession;

class SessionService
{
    public static function createSession(int $userId, string $sessionId): UserSession
    {
        return UserSession::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'is_online' => true,
            'last_activity' => now(),
            'login_at' => now(),
        ]);
    }

    public static function closeSession(int $userId, string $sessionId): void
    {
        $session = UserSession::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('is_online', true)
            ->first();

        if ($session) {
            $session->closeSession();
        }
    }

    public static function updateActivity(int $userId, string $sessionId): void
    {
        UserSession::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('is_online', true)
            ->update(['last_activity' => now()]);
    }

    public static function getUsersOnline(): int
    {
        return UserSession::where('is_online', true)
            ->where('last_activity', '>=', now()->subMinutes(5))
            ->distinct('user_id')
            ->count();
    }

    public static function closeInactiveSessions(int $inactiveMinutes = 30): int
    {
        $sessions = UserSession::where('is_online', true)
            ->where('last_activity', '<', now()->subMinutes($inactiveMinutes))
            ->get();

        foreach ($sessions as $session) {
            $session->closeSession();
        }

        return $sessions->count();
    }
}
