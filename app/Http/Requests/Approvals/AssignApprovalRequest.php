<?php

namespace App\Http\Requests\Approvals;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    public function rules(): array
    {
        $ticket = $this->route('ticket');

        return [
            // Presente => se ajusta el aprobador de un nivel del flujo existente.
            // Ausente => se asigna/cambia/quita el aprobador ad-hoc (categoría sin flujo).
            'approval_id' => ['nullable', Rule::exists('ticket_approvals', 'id')->where('ticket_id', $ticket->id)],
            'approver_id' => ['nullable', Rule::exists('users', 'id')],
        ];
    }
}
