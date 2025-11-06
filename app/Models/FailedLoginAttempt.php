<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class FailedLoginAttempt extends Model
{
    protected $table = 'failed_login_attempts';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'attempted_at',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
    ];

    /**
     * Obtener intentos recientes por IP
     */
    public static function recentAttemptsByIp(string $ip, int $minutes = 15): int
    {
        return self::where('ip_address', $ip)
            ->where('attempted_at', '>', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Obtener intentos recientes por email
     */
    public static function recentAttemptsByEmail(string $email, int $minutes = 15): int
    {
        return self::where('email', $email)
            ->where('attempted_at', '>', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Limpiar intentos antiguos
     */
    public static function cleanup(int $days = 30): int
    {
        return self::where('attempted_at', '<', now()->subDays($days))->delete();
    }
}
