<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Se te ha asignado un ticket: '.$this->ticket->title)
            ->greeting('¡Hola '.$notifiable->name.'!')
            ->line('Se te ha asignado un nuevo ticket.')
            ->line('Título: '.$this->ticket->title)
            ->line('Solicitante: '.$this->ticket->user->name)
            ->action('Ver ticket', route('tickets.show', $this->ticket))
            ->line('Por favor da seguimiento a este ticket lo antes posible.');
    }
}
