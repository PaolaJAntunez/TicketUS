<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Flujos de Aprobación
        </h2>
    </x-slot>

    <div style="padding: 32px 0;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">

            @if(session('success'))
                <div style="margin-bottom: 16px; padding: 16px; background-color: #dcfce7; color: #166534; border-radius: 6px;">
                    {{ session('success') }}
                </div>
            @endif

            <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('admin.users.index') }}"
                       style="background-color: #ffffff; color: #1e3a5f; border: 1px solid #1e3a5f; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        Usuarios
                    </a>
                    <a href="{{ route('admin.categories.index') }}"
                       style="background-color: #ffffff; color: #1e3a5f; border: 1px solid #1e3a5f; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        Categorías
                    </a>
                    <a href="{{ route('admin.approval-flows.index') }}"
                       style="background-color: #1e3a5f; color: #ffffff; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                        Flujos de Aprobación
                    </a>
                </div>

                <a href="{{ route('admin.approval-flows.create') }}"
                   style="background-color: #2563eb; color: #ffffff; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                    Nuevo Flujo
                </a>
            </div>

            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background-color: #f8fafc;">
                        <tr>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Categoría</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Nombre del Flujo</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Niveles</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($approvalFlows as $flow)
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 16px 24px; font-size: 14px; color: #1e293b; font-weight: 500;">{{ $flow->category->name }}</td>
                                <td style="padding: 16px 24px; font-size: 14px; color: #64748b;">{{ $flow->name }}</td>
                                <td style="padding: 16px 24px; font-size: 14px;">
                                    <span style="background-color: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                        {{ $flow->levels->count() }} {{ $flow->levels->count() === 1 ? 'nivel' : 'niveles' }}
                                    </span>
                                </td>
                                <td style="padding: 16px 24px; font-size: 14px;">
                                    <div style="display: flex; gap: 8px;">
                                        <a href="{{ route('admin.approval-flows.edit', $flow) }}"
                                           style="background-color: #2563eb; color: #ffffff; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 500;">
                                            Gestionar
                                        </a>
                                        <form action="{{ route('admin.approval-flows.destroy', $flow) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar este flujo de aprobación?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    style="background-color: #ffffff; color: #991b1b; border: 1px solid #991b1b; padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer;">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 24px; text-align: center; color: #64748b; font-size: 14px;">
                                    No hay flujos de aprobación configurados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
