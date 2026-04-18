<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenTracking extends Model
{
    protected $table = 'ordenes_tracking';

    protected $fillable = [
        'cliente_nombre', 'cliente_telefono', 'referencia',
        'direccion', 'latitud', 'longitud', 'estado', 'sede', 'notas',
    ];

    protected $casts = [
        'latitud'  => 'float',
        'longitud' => 'float',
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_RUTA   = 'en_ruta';
    const ESTADO_ENTREGADO = 'entregado';
    const ESTADO_FALLIDO   = 'fallido';

    public function paradas()
    {
        return $this->hasMany(RutaParada::class, 'orden_id');
    }
}
