<?php

namespace App\Http\Requests\Approvals;

use Illuminate\Foundation\Http\FormRequest;

class ReopenTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('reopen', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            // Regla #4: una reapertura siempre debe registrar el motivo.
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Debes indicar el motivo de la reapertura.',
        ];
    }
}
