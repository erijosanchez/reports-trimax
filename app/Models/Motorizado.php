<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motorizado extends Model
{
    protected $fillable = [
        'nombre',
        'telefono',
        'sede',
        'traccar_device_id',
        'api_token',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function rutas()
    {
        return $this->hasMany(RutaMotorizado::class);
    }

    public function ubicaciones()
    {
        return $this->hasMany(UbicacionMotorizado::class);
    }

    public function rutaActiva()
    {
        return $this->hasOne(RutaMotorizado::class)->where('estado', 'en_ruta');
    }

    public function ultimaUbicacion()
    {
        return $this->hasOne(UbicacionMotorizado::class)->latestOfMany('registrado_at');
    }

    public function estaEnRuta(): bool
    {
        return $this->rutaActiva()->exists();
    }
}
