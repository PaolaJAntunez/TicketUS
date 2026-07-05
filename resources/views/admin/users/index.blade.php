<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Administración de Usuarios
        </h2>
    </x-slot>

    <div style="padding: 32px 0;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">

            @if(session('success'))
                <div style="margin-bottom: 16px; padding: 16px; background-color: #dcfce7; color: #166534; border-radius: 6px;">
                    {{ session('success') }}
                </div>
            @endif

            <div style="margin-bottom: 16px; display: flex; gap: 8px;">
                <a href="{{ route('admin.users.index') }}"
                   style="background-color: #1e3a5f; color: #ffffff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                    Usuarios
                </a>
                <a href="{{ route('admin.categories.index') }}"
                   style="background-color: #ffffff; color: #1e3a5f; border: 1px solid #1e3a5f; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                    Categorías
                </a>
                <a href="{{ route('admin.approval-flows.index') }}"
                   style="background-color: #ffffff; color: #1e3a5f; border: 1px solid #1e3a5f; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                    Flujos de Aprobación
                </a>
            </div>

            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background-color: #f8fafc;">
                        <tr>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Nombre</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Email</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Rol</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $roleColors = [
                                'admin'    => ['bg' => '#1e3a5f', 'text' => '#ffffff', 'label' => 'Admin'],
                                'agent'    => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'Agente'],
                                'approver' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'Aprobador'],
                                'user'     => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Usuario'],
                            ];
                        @endphp
                        @forelse($users as $user)
                            @php
                                $r = $roleColors[$user->role] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => ucfirst($user->role)];
                            @endphp
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 16px 24px; font-size: 14px; color: #1e293b; font-weight: 500;">{{ $user->name }}</td>
                                <td style="padding: 16px 24px; font-size: 14px; color: #64748b;">{{ $user->email }}</td>
                                <td style="padding: 16px 24px; font-size: 14px;">
                                    <span style="background-color: {{ $r['bg'] }}; color: {{ $r['text'] }}; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                        {{ $r['label'] }}
                                    </span>
                                </td>
                                <td style="padding: 16px 24px; font-size: 14px;">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       style="background-color: #2563eb; color: #ffffff; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 500;">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 24px; text-align: center; color: #64748b; font-size: 14px;">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
