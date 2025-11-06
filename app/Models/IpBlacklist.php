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

    public static function blockIp(string $ip, string $reason, $duration = null): self
    {
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
