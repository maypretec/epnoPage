<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ComplaintDetailsNotification extends Notification
{
    use Queueable;
    public $ntf_type;
    public $role;
    public $id;
    public $complaint_num;
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
    public function __construct($ntf_type, $role, $id, $complaint_num, $title, $name, $phone, $email, $org)
    {
        $this->ntf_type = $ntf_type;
        $this->role = $role;
        $this->id = $id;
        $this->complaint_num = $complaint_num;
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
        $url = env('APP_URL') . '@c-@d/' . $this->id;

        if ($this->ntf_type == 24) {

            if ($this->role == 4) {
                $message = 'Tu queja esta en revisión, se te avisara cualquier cambio.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num;
            } elseif ($this->role == 5 || $this->role == 3) {
                $message = 'Hay una nueva queja agregada, revisala ahora.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        } else if ($this->ntf_type == 25) {
            $message = 'Tienes una nueva queja asignada, por favor revisala lo antes posible.';
            $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num;
        } else if ($this->ntf_type == 26) {
            if ($this->role == 4) {
                $message = 'Ha habido una nueva respuesta a tus evidencias.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num;
            } elseif ($this->role == 5 || $this->role == 3) {
                $message = 'Ha habido una nueva respuesta a tus evidencias.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        } else if ($this->ntf_type == 27) {
            if ($this->role == 4) {
                $message = 'Te informamos que tu queja ha sido rechazada.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num . ' Motivo: ' . $this->org;
            }
        } else if ($this->ntf_type == 28) {
            if ($this->role == 4 || $this->role == 6) {
                $message = 'La queja ha sido enviada a disputa,se te avisará cualquier cambio.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            } else if ($this->role == 10) {
                $message = 'La queja ha sido enviada a disputa.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        } else if ($this->ntf_type == 5) {
            if ($this->role == 4 ) {
                $message = 'La queja esta en pendiente de aprobación, no pierdas tiempo y apruebala subiendo la nueva PO.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            } else if ($this->role == 6) {
                $message = 'La queja ha sido enviada a pendiente de aprobación,se te informará cualquier cambio.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            } else if ($this->role == 10) {
                $message = 'La queja ha sido enviada a pendiente de aprobación.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        } else if ($this->ntf_type == 6) {
            if ($this->role == 4 ) {
                $message = 'La queja ha sido enviada a construcción,se te avisará cualquier cambio.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            } else if ($this->role == 6) {
                $message = 'La queja ha sido enviada a construcción,no olvides solicitar tu inspección.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            } else if ($this->role == 10) {
                $message = 'La queja ha sido enviada a construcción.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        } else if ($this->ntf_type == 8) {
            if ($this->role == 4 || $this->role == 6) {
                $message = 'La orden se encuentra en inspección,se te avisará cualquier cambio.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            }
        } else if ($this->ntf_type == 12) {
            if ($this->role == 10) {
                $message = 'Se ha solicitado una inspección.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        } else if ($this->ntf_type == 7) {
            if ($this->role == 4 || $this->role == 6) {
                $message = 'La orden esta en camino,se te avisará cualquier cambio.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            } else if ($this->role == 10) {
                $message = 'La orden esta en camino.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        } else if ($this->ntf_type == 9) {
            if ($this->role == 4 || $this->role == 6) {
                $message = 'La orden ha sido entregada.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            } else if ($this->role == 10) {
                $message = 'La orden ha sido entregada.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        } else if ($this->ntf_type == 22) {
            if ($this->role == 6) {
                $message = '¡Solicita tu inspección ahora!,¡No esperes más!.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            }
        } else if ($this->ntf_type == 29) {
            if ($this->role == 4 || $this->role == 6) {
                $message = 'La queja ha sido cerrada.';
                $content = 'Servicio principal: ' . $this->title . ', Queja: ' . $this->complaint_num;
            } else if ($this->role == 10) {
                $message = 'La queja ha sido cerrada.';
                $content = 'Servicio principal: ' . $this->title . ', Queja:' . $this->complaint_num .
                    ', Generada por: ' . $this->name . ', Telefono: ' . $this->phone .
                    ', Correo: ' . $this->email . ', Organización: ' . $this->org;
            }
        }

        return (new MailMessage)
            ->subject(Lang::get('EP&O ' . $this->complaint_num))
            ->line($message)
            ->line($content)
            ->action('Ver todos los detalles', url($url))
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
