<?php

namespace App\Notifications;

use App\Models\ReporteCajaChica;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CajaChicaAlertaVencimiento extends Notification
{
    use Queueable;

    public function __construct(public ReporteCajaChica $reporte) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⏰ ALERTA — Tu reporte de Caja Chica vence en 1 hora | Semana {$this->reporte->semana_numero}/{$this->reporte->anio}")
            ->view('emails.productividad.caja_chica_alerta', [
                'reporte'    => $this->reporte,
                'notifiable' => $notifiable,
                'url'        => route('productividad.cobranza-sedes.caja-chica.index'),
            ]);
    }
}
