<?php

namespace App\Notifications;

use App\Models\ReporteComentarios;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Storage;

class ComentariosSubmitida extends Notification
{
    use Queueable;

    public function __construct(
        public ReporteComentarios $reporte,
        public bool $esEdicion = false
    ) {}

    public function via($notifiable): array { return ['mail']; }

    public function toMail($notifiable): MailMessage
    {
        $accion  = $this->esEdicion ? 'Actualización' : 'Envío';
        $subject = "💬 {$accion} Reporte de Comentarios — Sede {$this->reporte->sede} | Semana {$this->reporte->semana_numero}/{$this->reporte->anio}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->view('emails.productividad.comentarios_submitida', [
                'reporte'    => $this->reporte,
                'esEdicion'  => $this->esEdicion,
                'notifiable' => $notifiable,
                'url'        => route('productividad.cobranza-sedes.comentarios.index'),
            ]);

        foreach ($this->reporte->archivos ?? [] as $archivo) {
            if (Storage::disk('local')->exists($archivo['path']) && ($archivo['size'] ?? 0) <= 8 * 1024 * 1024) {
                $mail->attach(Storage::disk('local')->path($archivo['path']), [
                    'as'   => $archivo['name'],
                    'mime' => $archivo['mime'] ?? 'application/octet-stream',
                ]);
            }
        }

        return $mail;
    }
}
