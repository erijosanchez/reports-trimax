<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\AcuerdoComercial;
use App\Notifications\Concerns\RespetaPreferenciaCorreo;

class AcuerdoRehabilitado extends Notification
{
    use Queueable, RespetaPreferenciaCorreo;

    protected $acuerdo;
    protected $motivo;

    public function __construct(AcuerdoComercial $acuerdo, $motivo)
    {
        $this->acuerdo = $acuerdo;
        $this->motivo = $motivo;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('✅ Acuerdo Comercial Rehabilitado: ' . $this->acuerdo->numero_acuerdo)
            ->greeting('¡Hola!')
            ->line('El acuerdo comercial **' . $this->acuerdo->numero_acuerdo . '** ha sido rehabilitado y está nuevamente activo.')
            ->line('**Cliente:** ' . $this->acuerdo->razon_social)
            ->line('**Detalle del Acuerdo:** ' . $this->acuerdo->acuerdo_comercial)
            ->line('**Motivo:** ' . $this->motivo)
            ->line('**Rehabilitado por:** ' . $this->acuerdo->rehabilitador->name)
            ->action('Ver Detalles', url('/comercial/acuerdos'))
            ->line('Gracias por usar nuestro sistema.');
    }

    public function toArray($notifiable)
    {
        return [
            'tipo' => 'acuerdo_rehabilitado',
            'titulo' => 'Acuerdo Comercial Rehabilitado',
            'mensaje' => "Acuerdo {$this->acuerdo->numero_acuerdo} — {$this->acuerdo->razon_social}",
            'url' => url('/comercial/acuerdos'),
            'acuerdo_id' => $this->acuerdo->id,
            'numero_acuerdo' => $this->acuerdo->numero_acuerdo,
            'motivo' => $this->motivo
        ];
    }
}