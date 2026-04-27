<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entrega extends Model
{
    protected $fillable = [
        'motorizado_id', 'ruta_id', 'cliente_nombre',
        'cliente_telefono', 'referencia', 'direccion',
        'latitud', 'longitud', 'orden_secuencia', 'estado',
        'entrega_latitud', 'entrega_longitud',
        'entregado_en', 'notas', 'sede',
    ];

    protected $casts = [
        'entregado_en' => 'datetime',
        'latitud'      => 'float',
        'longitud'     => 'float',
        'entrega_latitud'  => 'float',
        'entrega_longitud' => 'float',
    ];

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }

    public function ruta()
    {
        return $this->belongsTo(GpsRuta::class, 'ruta_id');
    }
}
