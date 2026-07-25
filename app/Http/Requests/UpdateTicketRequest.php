<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:open,assigned,in_progress,on_hold,resolved,closed,pending_approval,rejected,cancelled'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            // Regla #6: solo usuarios con rol "agent" pueden quedar asignados a un ticket.
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('role', 'agent')],
            'resolved_at' => ['nullable', 'date'],
            // Regla #10: si el ticket maneja un monto, debe ser positivo.
            'amount' => ['nullable', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $ticket = $this->route('ticket');
            $user = $this->user();

            $effectiveAssignedTo = $this->input('assigned_to') ?: $ticket->assigned_to;

            // Regla: un agente no puede asignar el ticket a otro usuario, solo
            // puede asignárselo a sí mismo. No aplica si no está cambiando el
            // valor actual (ej. editar prioridad/estatus de un ticket ya
            // asignado a otro, dejando "Asignar a" tal cual estaba).
            $requestedAssignedTo = $this->input('assigned_to');
            $assignedToChanged = (int) $requestedAssignedTo !== (int) $ticket->assigned_to;
            if ($user->role === 'agent' && $assignedToChanged && $requestedAssignedTo && (int) $requestedAssignedTo !== (int) $user->id) {
                $validator->errors()->add('assigned_to', 'No puedes asignar este ticket a otro usuario. Solo puedes asignártelo a ti mismo.');
            }

            // Regla #4: un ticket ya en un estado final solo puede salir de ahí mediante
            // la acción explícita de reapertura (tickets.reopen), no por esta edición genérica.
            $terminalStates = ['rejected', 'resolved', 'closed', 'cancelled'];
            if (in_array($ticket->status, $terminalStates, true) && $this->input('status') !== $ticket->status) {
                $validator->errors()->add('status', 'Este ticket está en un estado final. Usa la opción de reapertura para reactivarlo.');
            }

            // Regla #7: un ticket no puede marcarse "resuelto" sin agente asignado.
            if ($this->input('status') === 'resolved' && ! $effectiveAssignedTo) {
                $validator->errors()->add('status', 'No puedes marcar el ticket como resuelto sin un agente asignado.');
            }

            // Un ticket con una aprobación (de flujo o ad-hoc) sin votar todavía
            // no puede marcarse "resuelto", aunque su status actual no sea
            // "pending_approval" (ver Ticket::hasPendingApproval()).
            if ($this->input('status') === 'resolved' && $ticket->hasPendingApproval()) {
                $validator->errors()->add('status', 'Este ticket tiene una aprobación pendiente. No puede marcarse como resuelto hasta que se resuelva.');
            }

            // Regla #9: resolved_at nunca puede ser anterior a created_at.
            if ($this->filled('resolved_at') && $ticket->created_at && $this->date('resolved_at')->lt($ticket->created_at)) {
                $validator->errors()->add('resolved_at', 'La fecha de resolución no puede ser anterior a la fecha de creación del ticket.');
            }
        });
    }
}
