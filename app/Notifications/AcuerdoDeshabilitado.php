<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\AcuerdoComercial;

class AcuerdoDeshabilitado extends Notification
{
    use Queueable;

    protected $acuerdo;
    protected $motivo;

    public function __construct(AcuerdoComercial $acuerdo, $motivo)
    {
        $this->acuerdo = $acuerdo;
        $this->motivo = $motivo;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('⚠️ Acuerdo Comercial Deshabilitado: ' . $this->acuerdo->numero_acuerdo)
            ->greeting('¡Hola!')
            ->line('El acuerdo comercial **' . $this->acuerdo->numero_acuerdo . '** ha sido deshabilitado.')
            ->line('**Cliente:** ' . $this->acuerdo->razon_social)
            ->line('**Motivo:** ' . $this->motivo)
            ->line('**Deshabilitado por:** ' . $this->acuerdo->deshabilitador->name)
            ->action('Ver Detalles', url('/comercial/acuerdos'))
            ->line('Gracias por usar nuestro sistema.');
    }

    public function toArray($notifiable)
    {
        return [
            'tipo' => 'acuerdo_deshabilitado',
            'acuerdo_id' => $this->acuerdo->id,
            'numero_acuerdo' => $this->acuerdo->numero_acuerdo,
            'motivo' => $this->motivo
        ];
    }
}