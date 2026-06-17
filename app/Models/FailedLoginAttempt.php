<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedLoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'reason',
        'attempted_at',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
        'user_id'      => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function recentAttemptsByIp(string $ip, int $minutes = 15): int
    {
        return self::where('ip_address', $ip)
            ->where('attempted_at', '>', now()->subMinutes($minutes))
            ->count();
    }

    public static function recentAttemptsByEmail(string $email, int $minutes = 15): int
    {
        return self::where('email', $email)
            ->where('attempted_at', '>', now()->subMinutes($minutes))
            ->count();
    }

    public static function cleanup(int $days = 90): int
    {
        return self::where('attempted_at', '<', now()->subDays($days))->delete();
    }
}
