<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaParada extends Model
{
    protected $table = 'ruta_paradas';

    protected $fillable = [
        'ruta_id', 'orden_id', 'orden_secuencia', 'estado',
        'hora_llegada', 'hora_salida', 'notas',
    ];

    protected $casts = [
        'hora_llegada' => 'datetime',
        'hora_salida'  => 'datetime',
    ];

    const ESTADO_PENDIENTE  = 'pendiente';
    const ESTADO_EN_CAMINO  = 'en_camino';
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_FALLIDO    = 'fallido';

    public function ruta()
    {
        return $this->belongsTo(RutaTracking::class, 'ruta_id');
    }

    public function orden()
    {
        return $this->belongsTo(OrdenTracking::class, 'orden_id');
    }

    public function getTiempoEnParadaAttribute(): ?int
    {
        if ($this->hora_llegada && $this->hora_salida) {
            return (int) $this->hora_llegada->diffInMinutes($this->hora_salida);
        }
        return null;
    }
}
