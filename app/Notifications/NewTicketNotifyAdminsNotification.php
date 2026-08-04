<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotifyAdminsNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $category = $this->ticket->category?->name ?? 'Sin categoría';
        if ($this->ticket->subcategory) {
            $category .= ' / '.$this->ticket->subcategory->name;
        }

        // $notifiable->name no existe cuando se notifica a un notifiable
        // anónimo (Notification::route('mail', ...), ej. la copia fija de
        // ADMIN_NOTIFICATION_EMAIL en TicketController::store()): saludo
        // genérico en ese caso, en vez de "¡Hola !".
        $greetingName = $notifiable->name ?? null;

        $message = (new MailMessage)
            ->subject('Nuevo ticket #'.$this->ticket->id.': '.$this->ticket->title)
            ->greeting($greetingName ? '¡Hola '.$greetingName.'!' : '¡Hola!')
            ->line('Se ha creado un nuevo ticket.')
            ->line('Ticket: #'.$this->ticket->id)
            ->line('Título: '.$this->ticket->title)
            ->line('Categoría: '.$category)
            ->line('Solicitante: '.$this->ticket->user->name)
            ->line('Prioridad: '.$this->ticket->priority);

        if ($this->ticket->status === 'pending_approval') {
            $message->line('Este ticket requiere aprobación antes de poder asignarse a un agente.');
        }

        return $message
            ->action('Ver ticket', route('tickets.show', $this->ticket))
            ->line('Puedes asignar un agente en cuanto corresponda.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_ticket_admin',
            'icon' => 'ti-ticket-plus',
            'title' => 'Nuevo ticket #'.$this->ticket->id,
            'message' => $this->ticket->title.' — solicitado por '.$this->ticket->user->name.'.',
            'ticket_id' => $this->ticket->id,
            'url' => route('tickets.show', $this->ticket),
        ];
    }
}
