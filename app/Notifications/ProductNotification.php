<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;


class ProductNotification extends Notification
{
    use Queueable;
    public $name;
    public $partno;
    public $id_partno;
    public $category;
    public $type;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($type,$name,$partno,$id_partno,$category)
    {
        $this->type = $type;
        $this->name = $name;
        $this->partno = $partno;
        $this->id_partno = $id_partno;
        $this->category = $category;
        
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
        $urlToResetForm = config('app.url') ."/"."@p-@d/" . $this->id_partno."/".$this->category;

        if ($this->type == 1) {
            $subjet='Nuevo comentario en ' . $this->name;
            $message = 'Hola, tienes un nuevo comentario en tu producto '.$this->partno.'-'.$this->name
            .', recuerda que es muy importante atender las dudas de los usuarios, no olvides responder.';
        }elseif ($this->type == 2) {
            $subjet='Respuesta a tu comentario en ' . $this->name;
            $message = 'Hola, han respondido a tu comentario en '.$this->name
            .',puedes revisarlo dando click al botón de abajo.';
        }

        return (new MailMessage)
        ->subject(Lang::get($subjet))
                    ->line($message)
                    ->action('Revisar ahora',$urlToResetForm)
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
