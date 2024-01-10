<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;


class SupplierNotification extends Notification
{
    use Queueable;
    public $type;
    public $partno;  
    public $name;  

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($type,$partno,$name)
    {
        $this->type = $type;
        $this->partno = $partno;
        $this->name = $name;
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
            $alert='¡Alerta!, cantidad minima alcanzada.';
            $message = '¡El numero de parte '.$this->partno.'-'.$this->name. ', ha alcanzado la cantidad minima!,
             recuerda que si el producto se agota no aparecerá dentro de nuestro catalogo, no olvides mantener todos tus productos al día. ';
            $action = 'Actualizar producto';     
            //url pendiente     
            $url = 'https://epno-app.com/login';
        }elseif ($this->type == 2) {
            $alert='¡Alerta!, producto por agotarse.';
            $message = '¡El numero de parte '.$this->partno.'-'.$this->name. ', esta por agotarse!, no permitas que esto pase,
             recuerda que  de ser asi no aparecerá dentro de nuestro catalogo, no olvides mantener todos tus productos al día. ';
            $action = 'Actualizar producto';     
            //url pendiente     
            $url = 'https://epno-app.com/login';
        } elseif ($this->type == 3) {
            $alert='¡Alerta!, producto agotado.';
            $message = '¡El numero de parte '.$this->partno.'-'.$this->name. ', esta agotado!, actualiza la información ahora,
            recuerda que no aparecerá dentro de nuestro catalogo, no olvides mantener todos tus productos al día. ';
           $action = 'Actualizar producto';     
           //url pendiente     
           $url = 'https://epno-app.com/login';
        }
      return (new MailMessage)
            ->subject(Lang::get($alert))
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
