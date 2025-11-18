<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLocation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'session_id',
        'latitude',
        'longitude',
        'city',
        'region',
        'country',
        'country_code',
        'ip_address',
        'is_vpn',
        'street_name',
        'street_number',
        'district',
        'postal_code',
        'formatted_address',
        'location_type',
        'accuracy',
        'created_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_vpn' => 'boolean',
        'created_at' => 'datetime',
        'accuracy' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function getAccuracyTextAttribute(): string
    {
        if (!$this->accuracy) return 'N/A';
        if ($this->accuracy < 50) return 'Muy precisa';
        if ($this->accuracy < 100) return 'Precisa';
        if ($this->accuracy < 500) return 'Moderada';
        return 'Baja';
    }

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
        ]);

        return implode(', ', $parts) ?: $this->formatted_location;
    }
}
