<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Motorizado extends Model
{
    use SoftDeletes;

    protected $table = 'motorizados';

    protected $fillable = [
        'nombre', 'sede', 'telefono', 'traccar_device_id', 'estado', 'token_gps',
    ];

    protected $casts = [
        'traccar_device_id' => 'integer',
    ];

    const ESTADO_ACTIVO   = 'activo';
    const ESTADO_INACTIVO = 'inactivo';

    const SEDES = ['ICA', 'LIMA', 'AREQUIPA', 'TRUJILLO', 'CHICLAYO', 'PIURA'];

    public function rutas()
    {
        return $this->hasMany(RutaTracking::class);
    }

    public function posiciones()
    {
        return $this->hasMany(TrackingPosition::class);
    }

    public function ultimaPosicion()
    {
        return $this->hasOne(TrackingPosition::class)->latestOfMany('registrado_en');
    }
}
