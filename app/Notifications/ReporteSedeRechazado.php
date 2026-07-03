<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReporteSedeRechazado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $tipo,      // "Depósito de Efectivo", "Caja Chica", "Comentarios"
        public string $sede,
        public string $periodo,   // etiqueta del período (fecha o "S{n}/{anio}")
        public string $motivo,
        public ?string $revisor,
        public string $url,
        public string $estadoRevision = 'rechazado', // 'rechazado' | 'conforme_observado'
        public ?float $penalidad = null              // % restado al KPI (solo observado)
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $observado = $this->estadoRevision === 'conforme_observado';
        $subject = $observado
            ? "📝 Reporte de {$this->tipo} CONFORME OBSERVADO — Sede {$this->sede} | {$this->periodo}"
            : "⚠️ Reporte de {$this->tipo} RECHAZADO — Sede {$this->sede} | {$this->periodo}";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.productividad.reporte_rechazado', [
                'tipo'       => $this->tipo,
                'sede'       => $this->sede,
                'periodo'    => $this->periodo,
                'motivo'     => $this->motivo,
                'revisor'    => $this->revisor,
                'url'        => $this->url,
                'observado'  => $observado,
                'penalidad'  => $this->penalidad,
                'notifiable' => $notifiable,
            ]);
    }
}
