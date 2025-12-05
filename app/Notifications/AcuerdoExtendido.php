<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\AcuerdoComercial;

class AcuerdoExtendido extends Notification
{
    use Queueable;

    protected $acuerdo;
    protected $motivo;
    protected $nuevaFecha;

    public function __construct(AcuerdoComercial $acuerdo, $motivo, $nuevaFecha)
    {
        $this->acuerdo = $acuerdo;
        $this->motivo = $motivo;
        $this->nuevaFecha = $nuevaFecha;
    }

    public function via($notifiable)
    {
        return ['mail',];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('📅 Acuerdo Comercial Extendido: ' . $this->acuerdo->numero_acuerdo)
            ->greeting('¡Hola!')
            ->line('El acuerdo comercial **' . $this->acuerdo->numero_acuerdo . '** ha sido extendido.')
            ->line('**Cliente:** ' . $this->acuerdo->razon_social)
            ->line('**Nueva Fecha de Fin:** ' . $this->nuevaFecha)
            ->line('**Motivo:** ' . $this->motivo)
            ->line('**Extendido por:** ' . $this->acuerdo->extensor->name)
            ->action('Ver Detalles', url('/comercial/acuerdos'))
            ->line('Gracias por usar nuestro sistema.');
    }

    public function toArray($notifiable)
    {
        return [
            'tipo' => 'acuerdo_extendido',
            'acuerdo_id' => $this->acuerdo->id,
            'numero_acuerdo' => $this->acuerdo->numero_acuerdo,
            'nueva_fecha' => $this->nuevaFecha,
            'motivo' => $this->motivo
        ];
    }
}