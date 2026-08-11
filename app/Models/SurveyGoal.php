<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyGoal extends Model
{
    protected $fillable = [
        'sede_id',
        'meta_semanal',
        'vigente_desde',
        'created_by',
    ];

    protected $casts = [
        'vigente_desde' => 'date',
        'meta_semanal'  => 'integer',
    ];

    public function sede()
    {
        return $this->belongsTo(UsersMarketing::class, 'sede_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Meta vigente para una sede en una fecha dada (la última con vigente_desde <= $fecha). */
    public static function vigentePara(int $sedeId, $fecha = null): ?self
    {
        $fecha = $fecha ?: now();

        return static::where('sede_id', $sedeId)
            ->whereDate('vigente_desde', '<=', $fecha)
            ->orderByDesc('vigente_desde')
            ->first();
    }
}
