<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TicketCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketComment $comment,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo comentario en: '.$this->ticket->title)
            ->greeting('¡Hola '.$notifiable->name.'!')
            ->line('Se ha agregado un nuevo comentario a tu ticket.')
            ->line('Título: '.$this->ticket->title)
            ->line('Comentario: '.$this->comment->comment)
            ->action('Ver ticket', route('tickets.show', $this->ticket))
            ->line('Te notificaremos cuando haya más novedades en tu ticket.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ticket_comment',
            'icon' => 'ti-message',
            'title' => 'Nuevo comentario',
            'message' => 'Nuevo comentario en "'.$this->ticket->title.'": '.Str::limit($this->comment->comment, 80),
            'ticket_id' => $this->ticket->id,
            'url' => route('tickets.show', $this->ticket),
        ];
    }
}
