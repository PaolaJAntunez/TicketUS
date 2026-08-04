<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Nuevo Usuario
        </h2>
    </x-slot>

    <style>
        .form-grid-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        @media (max-width: 640px) {
            .form-grid-2col { grid-template-columns: 1fr; }
        }
    </style>

    <div style="padding: 32px 0;">
        <div style="max-width: 640px; margin: 0 auto; padding: 0 24px;">

            @if($errors->any())
                <div style="margin-bottom: 16px; padding: 16px; background-color: #fee2e2; color: #991b1b; border-radius: 6px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Datos del usuario</span>
                </div>

                <div style="padding: 24px;">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Nombre completo</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Correo electrónico</label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                        </div>

                        <div class="form-grid-2col">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Contraseña</label>
                                <input type="password" name="password" required
                                       style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                <p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b;">Mínimo 8 caracteres, con mayúscula y número.</p>
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Confirmar contraseña</label>
                                <input type="password" name="password_confirmation" required
                                       style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                            </div>
                        </div>

                        <div class="form-grid-2col">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Rol</label>
                                <select name="role" required
                                        style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                    <option value="">Selecciona un rol</option>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Usuario</option>
                                    <option value="agent" {{ old('role') == 'agent' ? 'selected' : '' }}>Agente</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Estado</label>
                                <select name="is_active"
                                        style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-grid-2col" style="margin-bottom: 24px;">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Cargo / Puesto</label>
                                <input type="text" name="position" value="{{ old('position') }}" required placeholder="Ej. Analista de Soporte"
                                       style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Departamento / Área</label>
                                <select name="department" required
                                        style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                    <option value="">Selecciona un área</option>
                                    @foreach(\App\Models\User::DEPARTMENTS as $area)
                                        <option value="{{ $area }}" {{ old('department') == $area ? 'selected' : '' }}>{{ $area }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 12px;">
                            <a href="{{ route('admin.users.index') }}"
                               style="padding: 12px 20px; background-color: #e5e7eb; color: #374151; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                Cancelar
                            </a>
                            <button type="submit"
                                    style="background-color: #1e3a5f; color: #ffffff; padding: 12px 20px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                Crear usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
