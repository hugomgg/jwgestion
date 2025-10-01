<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $appName = config('app.name');
        $expirationMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Recuperación de Contraseña - ' . $appName)
            ->greeting('¡Hola!')
            ->line('Recibiste este correo porque solicitaste restablecer la contraseña de tu cuenta.')
            ->line('Para continuar, haz clic en el siguiente botón:')
            ->action('Restablecer Contraseña', $url)
            ->line('Este enlace de recuperación expirará en ' . $expirationMinutes . ' minutos.')
            ->line('Si no solicitaste restablecer tu contraseña, puedes ignorar este mensaje. Tu contraseña no será modificada.')
            ->salutation('Saludos,')
            ->salutation('El equipo de ' . $appName);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
