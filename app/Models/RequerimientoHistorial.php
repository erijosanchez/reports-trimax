<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequerimientoHistorial extends Model
{
    protected $table = 'requerimiento_historial';

    protected $fillable = [
        'requerimiento_id',
        'user_id',
        'tipo_evento',
        'titulo',
        'descripcion',
        'estado_anterior',
        'estado_nuevo',
    ];

    public function requerimiento()
    {
        return $this->belongsTo(RequerimientoPersonal::class, 'requerimiento_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Color del borde en el timeline según tipo de evento
     */
    public function getColorTimelineAttribute(): string
    {
        return match ($this->tipo_evento) {
            'creacion'              => 'blue',
            'cambio_estado'         => 'green',
            'asignacion_rh'         => 'purple',
            'publicacion_oferta'    => 'cyan',
            'revision_cvs'          => 'indigo',
            'entrevista_virtual'    => 'teal',
            'entrevista_presencial' => 'teal',
            'evaluacion'            => 'amber',
            'oferta_candidato'      => 'green',
            'alerta_sla'            => 'red',
            'nota'                  => 'gray',
            default                 => 'gray',
        };
    }

    /**
     * Ícono del timeline según tipo de evento
     */
    public function getIconoTimelineAttribute(): string
    {
        return match ($this->tipo_evento) {
            'creacion'              => 'fa-plus-circle',
            'cambio_estado'         => 'fa-exchange-alt',
            'asignacion_rh'         => 'fa-user-check',
            'publicacion_oferta'    => 'fa-bullhorn',
            'revision_cvs'          => 'fa-file-alt',
            'entrevista_virtual'    => 'fa-video',
            'entrevista_presencial' => 'fa-handshake',
            'evaluacion'            => 'fa-clipboard-check',
            'oferta_candidato'      => 'fa-check-circle',
            'alerta_sla'            => 'fa-exclamation-triangle',
            'nota'                  => 'fa-comment',
            default                 => 'fa-circle',
        };
    }

    /**
     * Etiqueta legible del tipo de evento
     */
    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo_evento) {
            'creacion'              => 'Creación',
            'cambio_estado'         => 'Cambio de estado',
            'asignacion_rh'         => 'Asignación RH',
            'publicacion_oferta'    => 'Publicación de oferta',
            'revision_cvs'          => 'Revisión de CVs',
            'entrevista_virtual'    => 'Entrevista virtual',
            'entrevista_presencial' => 'Entrevista presencial',
            'evaluacion'            => 'Evaluación',
            'oferta_candidato'      => 'Oferta al candidato',
            'alerta_sla'            => 'Alerta SLA',
            'nota'                  => 'Nota',
            default                 => $this->tipo_evento,
        };
    }
}
