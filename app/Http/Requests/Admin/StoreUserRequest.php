<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-users') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/', // al menos una mayúscula
                'regex:/[0-9]/', // al menos un número
            ],
            // "aprobador1"/"aprobador2" ya no son roles: cualquier usuario puede
            // ser designado aprobador (approver_id en el flujo o por ticket).
            'role' => ['required', 'in:user,agent,admin'],
            // Regla #1: cargo y departamento son obligatorios para cualquier rol
            // (esto ya cubre la regla #2 -"obligatorios si el rol es aprobador"-
            // porque son un requisito universal, no condicional).
            'position' => ['required', 'string', 'max:255'],
            'department' => ['required', Rule::in(User::DEPARTMENTS)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'La contraseña debe incluir al menos una letra mayúscula y un número.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Regla #2: solo un admin puede crear otro admin. El acceso a esta
            // ruta ya está limitado a admins (Gate manage-users), pero se deja
            // explícito por si en el futuro se relaja quién puede acceder.
            if ($this->input('role') === 'admin' && $this->user()?->role !== 'admin') {
                $validator->errors()->add('role', 'Solo un administrador puede crear otro usuario con rol admin.');
            }
        });
    }
}
