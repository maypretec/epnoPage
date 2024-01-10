<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class OrderDetailsNotification extends Notification
{
    use Queueable;
    public $ntf_type;
    public $role;
    public $id;
    public $purchase;
    public $title;
    public $name;
    public $phone;
    public $email;
    public $org;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($ntf_type, $role, $id, $purchase, $title, $name, $phone, $email, $org)
    {
        $this->ntf_type = $ntf_type;
        $this->role = $role;
        $this->id = $id;
        $this->purchase = $purchase;
        $this->title = $title;
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->org = $org;
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
        $url = env('APP_URL') . '@o-@d/' . $this->id;
        if ($this->ntf_type == 1) {
            $message = '¡Que no se te pase!,se te permitio cotizar la orden nuevamente.';
            $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
        } elseif ($this->ntf_type == 2) {

            $message = 'Tienes una nueva orden agregada, revisala ahora.';
            $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
        } elseif ($this->ntf_type == 3) {

            if ($this->role == 5 || $this->role == 3) {
                $message = 'La orden esta en cotización, revisala ahora.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            } elseif ($this->role == 4 ) {
                $message = 'Tu orden esta en cotización, se te informará cualquier cambio.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }

        } elseif ($this->ntf_type == 4) {
            $message = 'La orden ha sido cotizada, revisala ahora.';
            $content = 'Subservicio: ' . $this->title . '-' . $this->purchase .
                ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                ', Correo: ' . $this->email . ', Organización: ' . $this->org;

        } elseif ($this->ntf_type == 5 ) {
            $message = 'Tu orden esta pendiente de aprobación, apruebala ahora.';
            $content = 'Titulo: ' . $this->title . '-' . $this->purchase ;
               
        } elseif ($this->ntf_type == 6) {

            if ($this->role == 5 || $this->role == 3) {
                $message = 'La orden paso a estado de construcción, no olvides subir la PO al proveedor.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
            if ($this->role == 6) {
                $message = 'La orden paso a estado de construcción.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            } elseif ($this->role == 4 ) {
                $message = 'Tu orden esta en construcción, se te informará cualquier cambio.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            }
        } elseif ($this->ntf_type == 7) {

            if ($this->role == 5 || $this->role == 3) {
                $message = 'La orden esta en camino';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            } elseif ($this->role == 4 ) {
                $message = 'Tu orden esta en camino, no olvides marcarla como recibida.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            } elseif ($this->role == 6) {
                $message = 'La orden esta en camino';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        } elseif ($this->ntf_type == 8) {

            if ($this->role == 5 || $this->role == 3) {
                $message = 'La orden esta en auditoría';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            } elseif ($this->role == 4 ) {
                $message = 'Tu orden esta en auditoría,se te informará cualquier cambio.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            } elseif ($this->role == 6) {
                $message = 'La orden esta en auditoría';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            }
        } elseif ($this->ntf_type == 9) {

            if ($this->role == 5 || $this->role == 3) {
                $message = 'Se ha recibido la orden';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            } elseif ($this->role == 4 ) {
                $message = 'Se ha recibido la orden';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            } elseif ($this->role == 6) {
                $message = 'Se ha recibido la orden';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            }
        } elseif ($this->ntf_type == 10) {

            if ($this->role == 5 || $this->role == 3) {
                // En esta ocasion se utilizo el parametro de telefono para mostrar el motivo del rechazo.
                $message = 'La orden ha sido rechazada';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->org . 
                    ', Correo: ' . $this->email .', MOTIVOS DEL RECHAZO: ' .$this->name;
            } elseif ($this->role == 4 ) {
                $message = 'Has rechazado la orden.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase
                .', por favor permitenos ponernos en contacto contigo, para revisar la situación.';
            }
        } elseif ($this->ntf_type == 11) {   
                // En esta ocasion se utilizo el parametro de name para mostrar el motivo de la cancelacion.

                $message = 'La orden ha sido cancelada de manera definitiva, 
                en la sección de comentarios puedes encontrar más información.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase. ', MOTIVOS DE LA CANCELACIÓN: ' . $this->name;           
        } elseif ($this->ntf_type == 12) {

            $message = 'El proveedor ha solicitado una auditoría para la orden.';
            $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                ', Correo: ' . $this->email . ', Organización: ' . $this->org;
        } elseif ($this->ntf_type == 16) {

            $message = '¡Lo sentimos has perdido la orden!';
            $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            $url = env('APP_URL') . 'ordenes/' . $this->role;
        } elseif ($this->ntf_type == 20) {
            $message = 'Has sido seleccionado para licitar una orden.';
            $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
           
        } elseif ($this->ntf_type == 21) {

            if ($this->role == 5 || $this->role == 3) {
                $message = 'Tienes una nueva orden en revisión, entra ahora.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            } elseif ($this->role == 4 ) {
                $message = 'Tu orden esta en revisión, se te informará cualquier cambio.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            }
        } elseif ($this->ntf_type == 22) {

            $message = '¿Que esperas para solicitar tu auditoria?,esta todo listo, ¡Hazlo ahora!, revisa la orden: ';
            $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
        } elseif ($this->ntf_type == 23) {
            // En esta ocasion se utilizo el parametro de name para mostrar el motivo de la cancelacion.
            if ($this->role == 5 || $this->role == 3) {
                $message = 'Tienes una nueva solicitud de cancelacion de orden.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase .
                    ', Generada por: ' . $this->org . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', MOTIVOS DE LA CANCELACIÓN: ' . $this->name;
            } elseif ($this->role == 4 ) {
                $message = 'Tu solicitud de cancelación ha sido enviada, por favor
                 por antención a tu bandeja de correo o bien a la seccion de chat de la orden.';
                $content = 'Titulo: ' . $this->title . '-' . $this->purchase;
            }
        }

        return (new MailMessage)
            ->subject(Lang::get('EP&O ' . $this->purchase))
            ->line($message)
            ->line($content)
            ->action('Ver orden', url($url))
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
