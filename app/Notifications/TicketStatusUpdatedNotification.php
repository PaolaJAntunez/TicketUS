<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $oldStatus,
        public string $newStatus,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Actualización de estado: '.$this->ticket->title)
            ->greeting('¡Hola '.$notifiable->name.'!')
            ->line('El estado de tu ticket ha cambiado.')
            ->line('Título: '.$this->ticket->title)
            ->line('Estado anterior: '.$this->oldStatus)
            ->line('Nuevo estado: '.$this->newStatus)
            ->action('Ver ticket', route('tickets.show', $this->ticket))
            ->line('Te notificaremos cuando haya más novedades en tu ticket.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_status_updated',
            'icon' => 'ti-refresh',
            'title' => 'Actualización de estado',
            'message' => 'Tu ticket "'.$this->ticket->title.'" pasó de '.$this->oldStatus.' a '.$this->newStatus.'.',
            'ticket_id' => $this->ticket->id,
            'url' => route('tickets.show', $this->ticket),
        ];
    }
}
