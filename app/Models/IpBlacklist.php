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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Verificar si el bloqueo sigue activo
     */
    public function isActive(): bool
    {
        if ($this->blocked_until === null) {
            return true; // Bloqueo permanente
        }

        return $this->blocked_until->isFuture();
    }

    /**
     * Scope para obtener solo bloqueos activos
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('blocked_until')
              ->orWhere('blocked_until', '>', now());
        });
    }

    /**
     * Bloquear una IP
     */
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

    /**
     * Desbloquear una IP
     */
    public static function unblockIp(string $ip): bool
    {
        return self::where('ip_address', $ip)->delete();
    }
}

