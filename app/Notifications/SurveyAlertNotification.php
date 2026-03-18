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
        $nivel = ($this->survey->experience_rating === 1 || $this->survey->service_quality_rating === 1)
            ? '🔴 MUY INSATISFECHO'
            : '🟡 INSATISFECHO';

        return (new MailMessage)
            ->subject("[TRIMAX CRM] Alerta encuesta negativa — {$nivel}")
            ->view('emails.marketing.survey-alert', [
                'survey'     => $this->survey,
                'evaluado'   => $this->evaluado,
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'survey_id'              => $this->survey->id,
            'evaluado_name'          => $this->evaluado->name,
            'experience_rating'      => $this->survey->experience_rating,
            'service_quality_rating' => $this->survey->service_quality_rating,
            'tipo'                   => 'survey_alert',
        ];
    }
}
