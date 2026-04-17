<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaMotorizado extends Model
{
    protected $table = 'rutas_motorizado';

    protected $fillable = [
        'motorizado_id',
        'creado_por',
        'nombre',
        'sede',
        'estado',
        'inicio_at',
        'fin_at',
        'notas',
    ];

    protected $casts = [
        'inicio_at' => 'datetime',
        'fin_at'    => 'datetime',
    ];

    public function motorizado()
    {
        return $this->belongsTo(Motorizado::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function paradas()
    {
        return $this->hasMany(ParadaRuta::class, 'ruta_id')->orderBy('orden');
    }

    public function ubicaciones()
    {
        return $this->hasMany(UbicacionMotorizado::class, 'ruta_id')->orderBy('registrado_at');
    }

    public function duracionMinutos(): ?int
    {
        if (!$this->inicio_at || !$this->fin_at) return null;
        return (int) $this->inicio_at->diffInMinutes($this->fin_at);
    }

    public function paradasCompletadas(): int
    {
        return $this->paradas()->where('estado', 'entregado')->count();
    }

    public function paradasFallidas(): int
    {
        return $this->paradas()->where('estado', 'fallido')->count();
    }
}
