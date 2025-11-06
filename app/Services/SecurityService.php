<?php

namespace App\Services;

use App\Models\IpBlacklist;
use App\Models\FailedLoginAttempt;

class SecurityService
{
    public static function checkIpBlacklist(string $ip): bool
    {
        return IpBlacklist::where('ip_address', $ip)
            ->where(function ($query) {
                $query->whereNull('blocked_until')
                    ->orWhere('blocked_until', '>', now());
            })
            ->exists();
    }

    public static function blockIp(
        string $ip,
        string $reason,
        ?int $durationMinutes = null
    ): IpBlacklist {
        return IpBlacklist::blockIp($ip, $reason, $durationMinutes);
    }

    public static function unblockIp(string $ip): bool
    {
        return IpBlacklist::unblockIp($ip);
    }

    public static function getFailedAttempts(string $identifier, int $minutes = 15): int
    {
        // Verificar si es email o IP
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        return FailedLoginAttempt::where(
            $isEmail ? 'email' : 'ip_address',
            $identifier
        )
            ->where('attempted_at', '>', now()->subMinutes($minutes))
            ->count();
    }

    public static function shouldBlockIp(string $ip): bool
    {
        $maxAttempts = config('security.login.max_attempts', 5);
        $lockoutTime = config('security.login.lockout_duration', 15);

        $attempts = self::getFailedAttempts($ip, $lockoutTime);

        return $attempts >= $maxAttempts;
    }

    public static function getSecurityStats(): array
    {
        return [
            'blocked_ips_count' => IpBlacklist::active()->count(),
            'failed_attempts_today' => FailedLoginAttempt::whereDate('attempted_at', today())->count(),
            'failed_attempts_week' => FailedLoginAttempt::where('attempted_at', '>=', now()->subWeek())->count(),
        ];
    }
}
