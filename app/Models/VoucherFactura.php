<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherFactura extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'voucher_id',
        'factura',
        'monto',
    ];

    protected $casts = [
        'monto'      => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }
}
