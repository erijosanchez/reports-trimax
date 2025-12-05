<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AcuerdoComercial;

class AcuerdoCreado extends Notification
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo Acuerdo Comercial - ' . $this->acuerdo->numero_acuerdo)
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Se ha creado un nuevo acuerdo comercial que requiere tu atención.')
            ->line('**Número de Acuerdo:** ' . $this->acuerdo->numero_acuerdo)
            ->line('**Cliente:** ' . $this->acuerdo->razon_social)
            ->line('**Sede:** ' . $this->acuerdo->sede)
            ->line('**Tipo de Promoción:** ' . $this->acuerdo->tipo_promocion)
            ->line('**Vigencia:** ' . $this->acuerdo->fecha_inicio->format('d/m/Y') . ' - ' . $this->acuerdo->fecha_fin->format('d/m/Y'))
            ->action('Ver Acuerdo', url('/comercial/acuerdos'))
            ->line('Gracias por tu atención.');
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
            'tipo' => 'acuerdo_creado'
        ];
    }
}
