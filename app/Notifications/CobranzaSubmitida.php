<?php

namespace App\Notifications;

use App\Models\ReporteCobranza;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Storage;

class CobranzaSubmitida extends Notification
{
    use Queueable;

    public function __construct(
        public ReporteCobranza $reporte,
        public bool $esEdicion = false
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $accion  = $this->esEdicion ? 'Actualización' : 'Envío';
        $subject = "📊 {$accion} Reporte de Cobranza — Sede {$this->reporte->sede} | Semana {$this->reporte->semana_numero}/{$this->reporte->anio}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->view('emails.productividad.cobranza_submitida', [
                'reporte'    => $this->reporte,
                'esEdicion'  => $this->esEdicion,
                'notifiable' => $notifiable,
                'url'        => route('productividad.cobranza-sedes.cobranza.index'),
            ]);

        // Adjuntar archivos (máx 8 MB por archivo para no saturar el correo)
        foreach ($this->reporte->archivos ?? [] as $archivo) {
            $path = Storage::disk('local')->path($archivo['path']);
            if (Storage::disk('local')->exists($archivo['path']) && ($archivo['size'] ?? 0) <= 8 * 1024 * 1024) {
                $mail->attach($path, [
                    'as'   => $archivo['name'],
                    'mime' => $archivo['mime'] ?? 'application/octet-stream',
                ]);
            }
        }

        return $mail;
    }
}
