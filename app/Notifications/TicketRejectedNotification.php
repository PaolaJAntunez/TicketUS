<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public ?string $comments = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ticket rechazado: '.$this->ticket->title)
            ->greeting('¡Hola '.$notifiable->name.'!')
            ->line('Tu ticket ha sido rechazado durante el proceso de aprobación.')
            ->line('Título: '.$this->ticket->title)
            ->line('Motivo: '.($this->comments ?: 'Sin comentarios adicionales.'))
            ->action('Ver ticket', route('tickets.show', $this->ticket))
            ->line('Si tienes dudas, contacta al equipo de soporte.');
    }
}
