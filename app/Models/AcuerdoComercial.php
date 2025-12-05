<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Models\User;

class AcuerdoComercial extends Model
{
    use SoftDeletes;

    protected $table = 'acuerdos_comerciales';

    protected $fillable = [
        'numero_acuerdo',
        'user_id',
        'sede',
        'ruc',
        'razon_social',
        'consultor',
        'ciudad',
        'acuerdo_comercial',
        'tipo_promocion',
        'marca',
        'ar',
        'disenos',
        'material',
        'comentarios',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'validado',
        'aprobado',
        'validado_por',
        'validado_at',
        'aprobado_por',
        'aprobado_at',
        'archivos_adjuntos',
        'habilitado',
        'motivo_deshabilitacion',
        'deshabilitado_at',
        'deshabilitado_por',
        'motivo_rehabilitacion',
        'rehabilitado_at',
        'rehabilitado_por',
        'motivo_extension',
        'extendido_at',
        'extendido_por'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'validado_at' => 'datetime',
        'aprobado_at' => 'datetime',
        'deshabilitado_at' => 'datetime',
        'rehabilitado_at' => 'datetime',
        'extendido_at' => 'datetime',
        'archivos_adjuntos' => 'array',
        'habilitado' => 'boolean'
    ];

    protected $appends = ['estado_calculado'];

    // Agregar relación
    public function rehabilitador()
    {
        return $this->belongsTo(User::class, 'rehabilitado_por');
    }

    // Relaciones
    public function creador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function validador()
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function deshabilitador()
    {
        return $this->belongsTo(User::class, 'deshabilitado_por');
    }

    public function extensor()
    {
        return $this->belongsTo(User::class, 'extendido_por');
    }

    // Accesor para calcular el estado automático
    public function getEstadoCalculadoAttribute()
    {
        // Si está deshabilitado
        if (!$this->habilitado) {
            return 'Deshabilitado';
        }

        // Si está rechazado en cualquier etapa
        if ($this->validado === 'Rechazado' || $this->aprobado === 'Rechazado') {
            return 'Rechazado';
        }

        // Si está aprobado completamente
        if ($this->validado === 'Aprobado' && $this->aprobado === 'Aprobado') {
            // Verificar si está vencido
            if (Carbon::parse($this->fecha_fin)->lt(Carbon::today())) {
                return 'Vencido';
            }
            return 'Vigente';
        }

        // Por defecto, solicitado
        return 'Solicitado';
    }

    // Método para actualizar el estado
    public function actualizarEstado()
    {
        $estadoCalculado = $this->estado_calculado;
        if ($this->estado !== $estadoCalculado) {
            $this->update(['estado' => $estadoCalculado]);
        }
    }

    // Generar número de acuerdo automático
    public static function generarNumeroAcuerdo()
    {
        $year = date('Y');
        $lastAcuerdo = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastAcuerdo) {
            $lastNumber = intval(substr($lastAcuerdo->numero_acuerdo, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'AC-' . $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
