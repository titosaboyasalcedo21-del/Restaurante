<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Recupera tu acceso - RestaurantChain')
            ->greeting("Hola, {$notifiable->name}")
            ->line('Recibimos una solicitud para restablecer la contrasena de tu cuenta.')
            ->action('Restablecer mi contrasena', $url)
            ->line('Este enlace expira en 60 minutos y solo puede usarse una vez.')
            ->line('Si no solicitaste esto, ignora este mensaje. Tu contrasena no cambiara.')
            ->salutation('- Equipo RestaurantChain');
    }
}
