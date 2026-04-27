<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Motorizado extends Authenticatable
{
    use HasApiTokens, SoftDeletes, Notifiable;

    protected $fillable = [
        'nombre', 'sede', 'telefono', 'email',
        'password', 'estado',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    const SEDES = [
        'LIMA', 'ICA', 'AREQUIPA', 'TRUJILLO', 'CHICLAYO', 'PIURA'
    ];

    public function rutas()
    {
        return $this->hasMany(GpsRuta::class);
    }

    public function posiciones()
    {
        return $this->hasMany(GpsPosicion::class);
    }

    public function rutaActivaHoy()
    {
        return $this->hasOne(GpsRuta::class)
            ->whereDate('fecha', today())
            ->where('status', 'activa');
    }

    public function ultimaPosicion()
    {
        return $this->hasOne(GpsPosicion::class)
            ->latestOfMany('capturado_en');
    }
}
