<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class PasswordResetNotification extends Notification
{
    use Queueable;
    public $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $urlToResetForm = config('app.url').'/@r-@p/'. $this->token;
        // $urlToResetForm = config('app.url') . "reset/password/" . $this->token;
        return (new MailMessage)
            ->subject(Lang::get('Solicitud de restablecimiento de contraseña'))
            ->greeting(Lang::get('Hola ' . $notifiable->name))
            ->line(Lang::get('Recibes este email porque se solicitó un restablecimiento de contraseña para tu cuenta.'))
            ->action(Lang::get('Restablecer contraseña'), $urlToResetForm)
            ->line(Lang::get('Este enlace de restablecimiento de contraseña caducará en :count minutos.', ['count' => config('auth.passwords.users.expire')]))
            ->line(Lang::get('Si no reconoces este movimiento, puedes ignorar este correo.'))
            ->salutation(Lang::get('¡Saludos!'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
