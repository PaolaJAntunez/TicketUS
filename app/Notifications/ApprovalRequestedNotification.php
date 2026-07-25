<?php

namespace App\Notifications;

use App\Models\ApprovalLevel;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public ?ApprovalLevel $level = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Aprobación requerida: '.$this->ticket->title)
            ->greeting('¡Hola '.$notifiable->name.'!')
            ->line('Se requiere tu aprobación para el siguiente ticket.')
            ->line('Título: '.$this->ticket->title);

        if ($this->level) {
            $message->line('Nivel de aprobación: '.$this->level->name);
        }

        return $message
            ->line('Solicitante: '.$this->ticket->user->name)
            ->action('Revisar aprobación', route('approvals.index'))
            ->line('Por favor revisa esta solicitud lo antes posible.');
    }
}
