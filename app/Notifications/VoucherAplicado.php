<?php

namespace App\Notifications;

use App\Models\Voucher;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VoucherAplicado extends Notification
{
    public function __construct(protected Voucher $voucher) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Voucher Aplicado — {$this->voucher->codigo}")
            ->greeting("Hola {$notifiable->name},")
            ->line("Tu voucher ha sido aplicado exitosamente.")
            ->line("**Código:** {$this->voucher->codigo}")
            ->line("**Sede:** {$this->voucher->sede}")
            ->line("**Total:** S/ " . number_format($this->voucher->total, 2))
            ->line("**Fecha de aplicación:** " . $this->voucher->aplicado_at?->format('d/m/Y'))
            ->action('Ver en el sistema', url('/vouchers'))
            ->line("Gracias por usar el sistema Trimax.");
    }
}
