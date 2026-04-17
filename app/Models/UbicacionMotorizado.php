<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UbicacionMotorizado extends Model
{
    protected $table = 'ubicaciones_motorizado';

    protected $fillable = [
        'motorizado_id',
        'ruta_id',
        'latitud',
        'longitud',
        'velocidad',
        'precision_metros',
        'fuente',
        'registrado_at',
    ];

    protected $casts = [
        'latitud'          => 'decimal:8',
        'longitud'         => 'decimal:8',
        'velocidad'        => 'decimal:2',
        'precision_metros' => 'decimal:2',
        'registrado_at'    => 'datetime',
    ];

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }

    public function ruta()
    {
        return $this->belongsTo(RutaMotorizado::class, 'ruta_id');
    }
}
