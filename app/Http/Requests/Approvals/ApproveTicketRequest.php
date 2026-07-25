<?php

namespace App\Http\Requests\Approvals;

use App\Models\TicketApproval;
use Illuminate\Foundation\Http\FormRequest;

class ApproveTicketRequest extends FormRequest
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
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
