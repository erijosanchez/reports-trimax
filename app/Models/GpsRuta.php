<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsRuta extends Model
{
    protected $fillable = [
        'motorizado_id', 'fecha', 'started_at',
        'ended_at', 'distance_km', 'polyline', 'status',
    ];

    protected $casts = [
        'fecha'      => 'date',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'polyline'   => 'array',
    ];

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }

    public function posiciones()
    {
        return $this->hasMany(GpsPosicion::class, 'ruta_id')
            ->orderBy('capturado_en');
    }

    public function entregas()
    {
        return $this->hasMany(Entrega::class, 'ruta_id')
            ->orderBy('orden_secuencia');
    }

    // Duración formateada
    public function getDuracionAttribute(): string
    {
        if (!$this->started_at || !$this->ended_at) return '—';
        $diff = $this->started_at->diff($this->ended_at);
        return sprintf('%02d:%02d h', $diff->h + ($diff->days * 24), $diff->i);
    }
}
