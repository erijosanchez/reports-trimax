<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingPosition extends Model
{
    protected $table = 'tracking_positions';

    protected $fillable = [
        'motorizado_id', 'latitud', 'longitud', 'velocidad',
        'rumbo', 'altitud', 'traccar_position_id', 'registrado_en',
    ];

    protected $casts = [
        'latitud'       => 'float',
        'longitud'      => 'float',
        'velocidad'     => 'float',
        'registrado_en' => 'datetime',
    ];

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }
}
