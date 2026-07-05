<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
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
            ->subject('Ticket creado: '.$this->ticket->title)
            ->greeting('¡Hola '.$notifiable->name.'!')
            ->line('Tu ticket ha sido creado exitosamente.')
            ->line('Título: '.$this->ticket->title)
            ->line('Prioridad: '.$this->ticket->priority)
            ->line('Estado: '.$this->ticket->status)
            ->action('Ver ticket', route('tickets.show', $this->ticket))
            ->line('Te notificaremos cuando haya novedades en tu ticket.');
    }
}
