<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AcuerdoComercial;

class AcuerdoVigente extends Notification
{
    use Queueable;

    protected $acuerdo;
    /**
     * Create a new notification instance.
     */
    public function __construct(AcuerdoComercial $acuerdo)
    {
        $this->acuerdo = $acuerdo;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('✅ Acuerdo Comercial Vigente - ' . $this->acuerdo->numero_acuerdo)
            ->greeting('¡Acuerdo Comercial Vigente!')
            ->line('El acuerdo comercial **' . $this->acuerdo->numero_acuerdo . '** ahora está **VIGENTE**.')
            ->line('**Cliente:** ' . $this->acuerdo->razon_social)
            ->line('**RUC:** ' . $this->acuerdo->ruc)
            ->line('**Sede:** ' . $this->acuerdo->sede)
            ->line('**Fecha Inicio:** ' . \Carbon\Carbon::parse($this->acuerdo->fecha_inicio)->format('d/m/Y'))
            ->line('**Fecha Fin:** ' . \Carbon\Carbon::parse($this->acuerdo->fecha_fin)->format('d/m/Y'))
            ->line('**Acuerdo Comercial:** ' . $this->acuerdo->acuerdo_comercial)
            ->action('Ver Acuerdo', url('/comercial/acuerdos'))
            ->line('Gracias por su atención.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'acuerdo_id' => $this->acuerdo->id,
            'numero_acuerdo' => $this->acuerdo->numero_acuerdo,
            'razon_social' => $this->acuerdo->razon_social,
            'estado' => 'Vigente',
            'mensaje' => 'El acuerdo comercial ' . $this->acuerdo->numero_acuerdo . ' está ahora vigente'
        ];
    }
}
