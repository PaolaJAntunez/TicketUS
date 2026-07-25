<?php

namespace App\Http\Requests\Approvals;

use App\Models\TicketApproval;
use Illuminate\Foundation\Http\FormRequest;

class RejectAdhocTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user()->role === 'admin') {
            return true;
        }

        $ticket = $this->route('ticket');

        $approval = TicketApproval::where('ticket_id', $ticket->id)
            ->whereNull('approval_level_id')
            ->first();

        return $approval && $this->user()->id === $approval->approver_id;
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
