<?php

namespace App\Notifications;

use App\Models\ApprovalLevel;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * El ticket avanzó a un nivel de aprobación que no tiene aprobador asignado
 * (el flujo permite dejar niveles sin asignar a propósito, ver
 * TicketApprovalService::approve()). Sin este aviso el ticket queda
 * pendiente sin que nadie sepa que está atascado -- se notifica a todos los
 * admins con un link directo para asignar el aprobador que falta.
 */
class ApprovalLevelMissingApproverNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public ApprovalLevel $level,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Falta aprobador en el flujo: '.$this->ticket->title)
            ->greeting('¡Hola '.$notifiable->name.'!')
            ->line('El ticket #'.$this->ticket->id.' avanzó a un nivel de aprobación que no tiene aprobador asignado.')
            ->line('Ticket: '.$this->ticket->title)
            ->line('Nivel sin aprobador: '.$this->level->name)
            ->line('El ticket va a quedar pendiente hasta que se asigne un aprobador a este nivel.')
            ->action('Asignar aprobador', route('tickets.edit', $this->ticket))
            ->line('Revisa el flujo de aprobación de esta categoría para evitar que vuelva a pasar.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'approval_level_missing_approver',
            'icon' => 'ti-alert-triangle',
            'title' => 'Falta aprobador en el flujo',
            'message' => 'El ticket "'.$this->ticket->title.'" está atascado en "'.$this->level->name.'": ese nivel no tiene aprobador asignado.',
            'ticket_id' => $this->ticket->id,
            'url' => route('tickets.edit', $this->ticket),
        ];
    }
}
