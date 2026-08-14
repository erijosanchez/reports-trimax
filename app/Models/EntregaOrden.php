<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntregaOrden extends Model
{
    protected $table = 'entrega_ordenes';

    protected $fillable = [
        'entrega_id', 'numero_orden', 'cliente', 'ruc', 'fecha_orden',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
    ];

    public function entrega()
    {
        return $this->belongsTo(Entrega::class);
    }
}
