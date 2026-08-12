<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Survey;
use App\Models\UsersMarketing;

class SurveyAlertNotification extends Notification
{
    use Queueable;

    protected Survey $survey;
    protected UsersMarketing $evaluado;

    public function __construct(Survey $survey, UsersMarketing $evaluado)
    {
        $this->survey   = $survey;
        $this->evaluado = $evaluado;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $preguntas = collect([
            ['label' => 'Experiencia general', 'valor' => $this->survey->experience_rating, 'texto' => $this->survey->experience_rating_text],
            ['label' => 'Sede', 'valor' => $this->survey->sede_rating, 'texto' => $this->survey->sede_rating_text],
            ['label' => $this->survey->consultor ? "Consultor ({$this->survey->consultor->name})" : 'Consultor', 'valor' => $this->survey->consultor_rating, 'texto' => $this->survey->consultor_rating_text],
            ['label' => 'Tiempos de entrega', 'valor' => $this->survey->tiempos_entrega_rating, 'texto' => $this->survey->tiempos_entrega_rating_text],
            ['label' => 'Promociones', 'valor' => $this->survey->promociones_rating, 'texto' => $this->survey->promociones_rating_text],
        ])->filter(fn($p) => !is_null($p['valor']))->values();

        $peorRating = $preguntas->min('valor');
        $nivel = $peorRating === 1 ? '🔴 MUY INSATISFECHO' : '🟡 INSATISFECHO';

        return (new MailMessage)
            ->subject("[TRIMAX CRM] Alerta encuesta negativa — {$nivel}")
            ->view('emails.marketing.survey-alert', [
                'survey'     => $this->survey,
                'evaluado'   => $this->evaluado,
                'notifiable' => $notifiable,
                'preguntas'  => $preguntas,
                'promedio'   => Survey::promedioConsolidado([$this->survey]),
                'peorRating' => $peorRating,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'survey_id'              => $this->survey->id,
            'evaluado_name'          => $this->evaluado->name,
            'experience_rating'      => $this->survey->experience_rating,
            'sede_rating'            => $this->survey->sede_rating,
            'consultor_rating'       => $this->survey->consultor_rating,
            'tiempos_entrega_rating' => $this->survey->tiempos_entrega_rating,
            'promociones_rating'     => $this->survey->promociones_rating,
            'tipo'                   => 'survey_alert',
        ];
    }
}
