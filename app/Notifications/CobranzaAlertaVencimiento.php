<?php

namespace App\Notifications;

use App\Models\ReporteCobranza;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CobranzaAlertaVencimiento extends Notification
{
    use Queueable;

    public function __construct(public ReporteCobranza $reporte) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⏰ RECORDATORIO — Envía tu reporte de Depósito de Efectivo de HOY antes de las 12:00 PM")
            ->view('emails.productividad.cobranza_alerta', [
                'reporte'    => $this->reporte,
                'notifiable' => $notifiable,
                'url'        => route('productividad.cobranza-sedes.cobranza.index'),
            ]);
    }
}
