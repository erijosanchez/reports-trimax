<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaTracking extends Model
{
    protected $table = 'rutas_tracking';

    protected $fillable = [
        'motorizado_id', 'fecha', 'estado', 'notas', 'token_acceso',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    const ESTADO_PENDIENTE  = 'pendiente';
    const ESTADO_EN_RUTA    = 'en_ruta';
    const ESTADO_COMPLETADO = 'completado';

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }

    public function paradas()
    {
        return $this->hasMany(RutaParada::class, 'ruta_id')->orderBy('orden_secuencia');
    }
}
