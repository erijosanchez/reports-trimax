<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpBlacklist extends Model
{
    protected $table = 'ip_blacklist';

    protected $fillable = [
        'ip_address',
        'reason',
        'blocked_until',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->blocked_until === null || $this->blocked_until->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('blocked_until')->orWhere('blocked_until', '>', now());
        });
    }

    /**
     * Indica si una IP está en la whitelist (nunca debe bloquearse).
     */
    public static function isWhitelisted(?string $ip): bool
    {
        if (!$ip) {
            return false;
        }

        return in_array($ip, config('security.ip_blacklist.whitelist', []), true);
    }

    public static function blockIp(string $ip, string $reason, $duration = null): ?self
    {
        // Nunca bloquear IPs de confianza (localhost / oficina).
        if (self::isWhitelisted($ip)) {
            return null;
        }

        return self::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason' => $reason,
                'blocked_until' => $duration ? now()->addMinutes($duration) : null,
            ]
        );
    }

    public static function unblockIp(string $ip): bool
    {
        return self::where('ip_address', $ip)->delete();
    }
}
