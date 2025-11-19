<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'accuracy',
        'street_name',
        'street_number',
        'district',
        'city',
        'region',
        'country',
        'country_code',
        'postal_code',
        'formatted_address',
        'location_type',
        'created_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'accuracy' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener texto de precisión
     */
    public function getAccuracyTextAttribute(): string
    {
        if (!$this->accuracy) return 'Desconocida';

        if ($this->accuracy < 50) return 'Muy precisa';
        if ($this->accuracy < 100) return 'Precisa';
        if ($this->accuracy < 500) return 'Moderada';
        return 'Baja';
    }

    /**
     * Obtener dirección completa formateada
     */
    public function getFullAddressAttribute(): string
    {
        if ($this->formatted_address) {
            return $this->formatted_address;
        }

        $parts = array_filter([
            $this->street_name ? $this->street_name . ' ' . $this->street_number : null,
            $this->district,
            $this->city,
            $this->region,
            $this->country,
        ]);

        return implode(', ', $parts) ?: 'Ubicación no disponible';
    }

    /**
     * Scope para ubicaciones GPS únicamente
     */
    public function scopeGpsOnly($query)
    {
        return $query->where('location_type', 'gps');
    }

    /**
     * Scope para ubicaciones recientes
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function session()
    {
        return $this->belongsTo(UserSession::class, 'session_id');
    }

    public function getFormattedLocationAttribute(): string
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);
        return implode(', ', $parts) ?: 'Ubicación desconocida';
    }

    public function isGpsLocation(): bool
    {
        return $this->location_type === 'gps';
    }
}
