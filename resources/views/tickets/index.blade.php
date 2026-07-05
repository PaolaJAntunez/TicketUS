<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Tickets
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
                <h3 style="font-size: 18px; font-weight: 600; color: #1e293b; margin: 0;">Lista de Tickets</h3>
                <a href="{{ route('tickets.create') }}"
                   style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                    Nuevo Ticket
                </a>
            </div>

            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background-color: #f8fafc;">
                        <tr>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">#</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Título</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Categoría</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Prioridad</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Estado</th>
                            <th style="padding: 12px 24px; text-align: left; font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            @php
                                $statusColors = [
                                    'open' => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Abierto'],
                                    'pending_approval' => ['bg' => '#e0e7ff', 'text' => '#3730a3', 'label' => 'Pendiente de Aprobación'],
                                    'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Rechazado'],
                                    'assigned' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'Asignado'],
                                    'in_progress' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'En Progreso'],
                                    'resolved' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Resuelto'],
                                    'closed' => ['bg' => '#1f2937', 'text' => '#ffffff', 'label' => 'Cerrado'],
                                ];
                                $priorityColors = [
                                    'low' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Baja'],
                                    'medium' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'Media'],
                                    'high' => ['bg' => '#ffedd5', 'text' => '#9a3412', 'label' => 'Alta'],
                                    'urgent' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Urgente'],
                                ];
                                $s = $statusColors[$ticket->status] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->status];
                                $p = $priorityColors[$ticket->priority] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->priority];
                            @endphp
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 16px 24px; font-size: 14px; color: #64748b;">{{ $ticket->id }}</td>
                                <td style="padding: 16px 24px; font-size: 14px; color: #1e293b; font-weight: 500;">{{ $ticket->title }}</td>
                                <td style="padding: 16px 24px; font-size: 14px; color: #64748b;">{{ $ticket->category->name }}</td>
                                <td style="padding: 16px 24px; font-size: 14px;">
                                    <span style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                        {{ $p['label'] }}
                                    </span>
                                </td>
                                <td style="padding: 16px 24px; font-size: 14px;">
                                    <span style="background-color: {{ $s['bg'] }}; color: {{ $s['text'] }}; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                        {{ $s['label'] }}
                                    </span>
                                </td>
                                <td style="padding: 16px 24px; font-size: 14px;">
                                    <a href="{{ route('tickets.show', $ticket) }}" style="color: #1e3a5f; font-weight: 600; text-decoration: none;">Ver</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 24px; text-align: center; color: #64748b; font-size: 14px;">
                                    No hay tickets registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
