<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class OrderComment extends Notification
{
    use Queueable;
    public $comentario;
    public $adjunto;
    public $purchase;
    public $user_comment;
    public $order_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($comentario, $adjunto, $purchase, $user_comment, $order_id)
    {
        $this->comentario = $comentario;
        $this->adjunto = $adjunto;
        $this->purchase = $purchase;
        $this->user_comment = $user_comment;
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
        if ($this->adjunto !== [] && $this->comentario !== "Archivo adjunto") {
            $email = (new MailMessage)
                ->subject(Lang::get('EP&O Mensaje ' . $this->purchase))
                ->line("Tienes un nuevo mensaje en la orden  " . $this->purchase)
                ->line($this->user_comment . " dice: " . $this->comentario)
                ->line("Archivo adjunto en el correo.")
                ->action('Revisar ahora', url(env('APP_URL') . "@o-@d/"  . $this->order_id))
                ->line('¡Gracias por usar nuestra aplicación!');

            foreach ($this->adjunto as $archivo) {
                $email->attach(public_path($archivo));
            }

            return $email;
        } else if ($this->adjunto !== [] && $this->comentario == "Archivo adjunto") {
            $email = (new MailMessage)
                ->subject(Lang::get('EP&O Mensaje ' . $this->purchase))
                ->line("Tienes un nuevo mensaje en la orden  " . $this->purchase)
                ->line($this->user_comment . " dice: " . $this->comentario . " en el correo.")
                ->action('Revisar ahora', url(env('APP_URL') . "@o-@d/" . $this->order_id))
                ->line('¡Gracias por usar nuestra aplicación!');

            foreach ($this->adjunto as $archivo) {
                $email->attach(public_path($archivo));
            }

            return $email;
        } else if ($this->adjunto === [] && $this->comentario !== "Archivo adjunto") {
            return (new MailMessage)
                ->subject(Lang::get('EP&O Mensaje ' . $this->purchase))
                ->line("Tienes un nuevo mensaje en la orden  " . $this->purchase)
                ->line($this->user_comment . " dice: " . $this->comentario)
                ->action('Revisar ahora', url(env('APP_URL') . "@o-@d" . $this->order_id))
                ->line('¡Gracias por usar nuestra aplicación!');
        }
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
