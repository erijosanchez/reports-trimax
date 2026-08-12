<?php

namespace App\Notifications\Concerns;

/**
 * Canales estándar para notificaciones dirigidas a un usuario del CRM: correo
 * + bandeja in-app, salvo que el usuario haya apagado el correo
 * (User::wantsEmailNotifications()) — en ese caso solo queda la bandeja
 * in-app, nunca se deja de notificar del todo.
 */
trait RespetaPreferenciaCorreo
{
    public function via(object $notifiable): array
    {
        if (method_exists($notifiable, 'wantsEmailNotifications') && !$notifiable->wantsEmailNotifications()) {
            return ['database'];
        }

        return ['mail', 'database'];
    }
}
