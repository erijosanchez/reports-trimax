<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GpsPosicion extends Model
{

    protected $table = 'gps_posiciones';

    protected $fillable = [
        'motorizado_id', 'ruta_id', 'latitud',
        'longitud', 'velocidad', 'precicion', 'capturado_en',
    ];

    protected $casts = [
        'capturado_en' => 'datetime',
        'latitud'      => 'float',
        'longitud'     => 'float',
        'velocidad'    => 'float',
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
