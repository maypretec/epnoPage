<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class RegisterRequestNotification extends Notification
{
    use Queueable;

    public $org;
    public $rfc;
    public $calle;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($org, $rfc, $planta, $calle)
    {

        $this->org = $org;
        $this->rfc = $rfc;
        $this->planta = $planta;
        $this->calle = $calle;
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
        $url = env('APP_URL') . "solicitudes";
        return (new MailMessage)
            ->subject(Lang::get('Nueva solicitud de  ' . $this->org))
            ->line('Tienes una nueva solicitud para accesar a la plataforma.')
            ->line('Empresa: ' . $this->org . ', rfc: ' . $this->rfc)
            ->line('Calle: ' . $this->calle . ' Planta:' . $this->planta)
            ->action(Lang::get('Ver solicitudes'), url($url))
            ->line('Recuerda siempre revisar tus solicitudes en la aplicación.');
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
