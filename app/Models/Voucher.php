<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'codigo',
        'sede',
        'status',
        'archivos',
        'total',
        'solicitado_at',
        'aplicado_at',
        'created_by',
        'applied_by',
    ];

    protected $casts = [
        'archivos'      => 'array',
        'total'         => 'decimal:2',
        'solicitado_at' => 'date',
        'aplicado_at'   => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function facturas(): HasMany
    {
        return $this->hasMany(VoucherFactura::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function aplicador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
