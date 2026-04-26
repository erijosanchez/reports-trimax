<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaGps extends Model
{
    protected $table = 'rutas_gps';

    protected $fillable = [
        'motorizado_id', 'started_at', 'ended_at', 'distance_km', 'polyline', 'status',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'ended_at'    => 'datetime',
        'distance_km' => 'decimal:3',
        'polyline'    => 'array',
    ];

    const STATUS_ACTIVE    = 'active';
    const STATUS_COMPLETED = 'completed';

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }

    public function getDuracionAttribute(): ?string
    {
        if (!$this->ended_at) return null;
        $diff  = $this->started_at->diff($this->ended_at);
        $horas = $diff->h + ($diff->days * 24);
        return sprintf('%02d:%02d', $horas, $diff->i);
    }
}
