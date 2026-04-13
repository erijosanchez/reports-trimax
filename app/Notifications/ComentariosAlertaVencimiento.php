<?php

namespace App\Notifications;

use App\Models\ReporteComentarios;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ComentariosAlertaVencimiento extends Notification
{
    use Queueable;

    public function __construct(public ReporteComentarios $reporte) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⏰ ALERTA — Tu reporte de Comentarios vence HOY a las 11:59 PM | Semana {$this->reporte->semana_numero}/{$this->reporte->anio}")
            ->view('emails.productividad.comentarios_alerta', [
                'reporte'    => $this->reporte,
                'notifiable' => $notifiable,
                'url'        => route('productividad.cobranza-sedes.comentarios.index'),
            ]);
    }
}
