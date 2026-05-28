<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetiroOrden extends Model
{
    protected $table = 'retiros_ordenes';

    protected $fillable = [
        'sede',
        'numero_orden',
        'motivo',
        'nombre_responsable',
        'observacion',
        'status',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
