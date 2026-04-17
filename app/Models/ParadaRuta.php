<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParadaRuta extends Model
{
    protected $table = 'paradas_ruta';

    protected $fillable = [
        'ruta_id',
        'orden',
        'cliente',
        'direccion',
        'latitud',
        'longitud',
        'referencia',
        'estado',
        'motivo_fallo',
        'llegada_at',
        'completado_at',
        'notas',
    ];

    protected $casts = [
        'latitud'      => 'decimal:8',
        'longitud'     => 'decimal:8',
        'llegada_at'   => 'datetime',
        'completado_at' => 'datetime',
    ];

    public function ruta()
    {
        return $this->belongsTo(RutaMotorizado::class, 'ruta_id');
    }

    public function tiempoAtencionMinutos(): ?int
    {
        if (!$this->llegada_at || !$this->completado_at) return null;
        return (int) $this->llegada_at->diffInMinutes($this->completado_at);
    }
}
