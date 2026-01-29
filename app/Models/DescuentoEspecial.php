<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class DescuentoEspecial extends Model
{
    use SoftDeletes;

    protected $table = 'descuentos_especiales';

    protected $fillable = [
        'numero_descuento',
        'user_id',
        'numero_factura',
        'numero_orden',
        'sede',
        'ruc',
        'razon_social',
        'consultor',
        'ciudad',
        'descuento_especial',
        'tipo',
        'marca',
        'ar',
        'disenos',
        'material',
        'comentarios',
        'aplicado',        
        'aprobado',
        'aplicado_por',    
        'aplicado_at',     
        'aprobado_por',
        'aprobado_at',
        'archivos_adjuntos',
        'habilitado',
        'motivo_deshabilitacion',
        'deshabilitado_at',
        'deshabilitado_por',
        'motivo_rehabilitacion',
        'rehabilitado_at',
        'rehabilitado_por'
    ];

    protected $casts = [
        'aplicado_at' => 'datetime',    
        'aprobado_at' => 'datetime',
        'deshabilitado_at' => 'datetime',
        'rehabilitado_at' => 'datetime',
        'archivos_adjuntos' => 'array',
        'habilitado' => 'boolean'
    ];

    // Relaciones
    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function aplicador()
    {
        return $this->belongsTo(User::class, 'aplicado_por');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function deshabilitador()
    {
        return $this->belongsTo(User::class, 'deshabilitado_por');
    }

    public function rehabilitador()
    {
        return $this->belongsTo(User::class, 'rehabilitado_por');
    }

    // Generar número de descuento automático
    public static function generarNumeroDescuento()
    {
        $year = date('Y');
        $lastDescuento = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastDescuento) {
            $lastNumber = intval(substr($lastDescuento->numero_descuento, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'DE-' . $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Constantes para los tipos
    const TIPO_ANULACION = 'ANULACION';
    const TIPO_CORTESIA = 'CORTESIA';
    const TIPO_DESCUENTO_ADICIONAL = 'DESCUENTO ADICIONAL';
    const TIPO_DESCUENTO_TOTAL = 'DESCUENTO TOTAL';
    const TIPO_OTROS = 'OTROS';

    public static function getTiposDescuento()
    {
        return [
            self::TIPO_ANULACION,
            self::TIPO_CORTESIA,
            self::TIPO_DESCUENTO_ADICIONAL,
            self::TIPO_DESCUENTO_TOTAL,
            self::TIPO_OTROS
        ];
    }
}
