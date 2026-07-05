<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Editar Rol de Usuario
        </h2>
    </x-slot>

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
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">{{ $user->name }}</span>
                </div>

                <div style="padding: 24px;">
                    <div style="margin-bottom: 20px;">
                        <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Email</p>
                        <p style="font-weight: 500; color: #1e293b; margin: 0;">{{ $user->email }}</p>
                    </div>

                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div style="margin-bottom: 24px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Rol</label>
                            <select name="role" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>Usuario</option>
                                <option value="agent" {{ $user->role == 'agent' ? 'selected' : '' }}>Agente</option>
                                <option value="approver" {{ $user->role == 'approver' ? 'selected' : '' }}>Aprobador</option>
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                            <a href="{{ route('admin.users.index') }}"
                               style="padding: 10px 18px; background-color: #e5e7eb; color: #374151; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                Cancelar
                            </a>
                            <button type="submit"
                                    style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
