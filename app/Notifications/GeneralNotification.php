<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class GeneralNotification extends Notification
{
    use Queueable;
    public $type;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
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

        if ($this->type == 1) {
            $message = '¡En hora buena!, se ha aceptado tu solicitud para accesar a nuestra plataforma, no pierdas tiempo y accesa ahora.';
            $action = 'Iniciar sesión';
            $url = env('APP_URL'). '/login';
            // $url = 'https://epno-app.com/login';
        } elseif ($this->type == 0) {
            $message = '¡Lo sentimos!,por el momento no hemos podido aceptar tu solicitud de registro, 
            para mas información puedes ponerte en contacto con nosotros mediante los telefonos:
                (656) 703-85-86
                    (656) 703-85-88, o bien via correo:
                    contacto@epno.com.mx';
            $action = '¡No olvides visitarnos!';
            $url = 'https://epno.com.mx/';
        } elseif ($this->type == 14) {
            $message = 'Tu cuenta ha sido dada de baja temporalmente, para mas aclaraciones ponte en contacto con nosotros, mediante los telefonos:
            (656) 703-85-86
                (656) 703-85-88, o bien via correo:
                contacto@epno.com.mx';
            $action = '¡No olvides visitarnos!';
            $url = 'https://epno.com.mx/';
        } elseif ($this->type == 18) {
            $message = 'Tu cuenta ha sido reestablecida, no pierdas tiempo y accesa ahora.';
            $action = 'Iniciar sesión';
            $url = env('APP_URL'). 'login';
        } elseif ($this->type == 3) {
            $message = 'Hemos recibido tu solicitud de registro, recibirás un correo con mas información, cuando se reciben tus datos.';
            $action = '¡No olvides visitarnos!';
            $url = 'https://epno.com.mx/';
        }
        return (new MailMessage)
            ->subject(Lang::get('EP&O Notifica'))
            ->line($message)
            ->action($action, url($url))
            ->line('¡Gracias por usar nuestra aplicación!');
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
