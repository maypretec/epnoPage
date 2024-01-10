<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class RatingNotification extends Notification
{
    use Queueable;
    public $comentario;
    public $rate;
    public $purchase;
    public $order_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($comentario,$rate,$purchase,$order_id)
    {
        $this->comentario = $comentario;
        $this->rate = $rate;
        $this->purchase = $purchase;
        $this->order_id = $order_id;
        
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
        return (new MailMessage)
        ->subject(Lang::get('EP&O Notifica ' . $this->purchase))
            ->line("Se ha calificado tu desempeño en la orden " . $this->purchase)
            ->line("Calificación: " . $this->rate." estrellas")
            ->line("Notas: " . $this->comentario)
            ->action('Revisar ahora', url(env('APP_URL') . "/@o-@d" . "/" . $this->order_id))
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
