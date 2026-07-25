<?php

namespace App\Http\Requests\Approvals;

use App\Models\TicketApproval;
use Illuminate\Foundation\Http\FormRequest;

class RejectTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->role === 'admin') {
            return true;
        }

        $ticket = $this->route('ticket');
        $level = $this->route('level');

        $approval = TicketApproval::where('ticket_id', $ticket->id)
            ->where('approval_level_id', $level->id)
            ->first();

        $effectiveApproverId = $approval?->approver_id ?? $level->approver_id;

        return $this->user()->id === $effectiveApproverId;
    }

    public function rules(): array
    {
        return [
            // Regla #8: un rechazo siempre debe registrar el motivo.
            'comments' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'comments.required' => 'Debes indicar el motivo del rechazo.',
        ];
    }
}
