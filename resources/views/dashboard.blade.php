<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 22px; color: #ffffff; margin: 0;">
            @if(Auth::user()->role === 'admin')
                Panel de Administración
            @elseif(Auth::user()->role === 'agent')
                Mis Tickets Asignados
            @elseif(Auth::user()->role === 'approver')
                Panel de Aprobaciones
            @else
                ¿Cómo podemos ayudarte?
            @endif
        </h2>
    </x-slot>

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
    @endphp

    <div style="padding: 32px 0;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 24px;">

            @if(Auth::user()->role === 'admin')

                <!-- Contadores -->
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 24px;">
                    @foreach([
                        ['label' => 'Total Tickets', 'value' => $totalCount, 'color' => '#1e3a5f'],
                        ['label' => 'Abiertos', 'value' => $openCount, 'color' => '#374151'],
                        ['label' => 'En Progreso', 'value' => $inProgressCount, 'color' => '#854d0e'],
                        ['label' => 'Resueltos', 'value' => $resolvedCount, 'color' => '#166534'],
                        ['label' => 'Pend. Aprobación', 'value' => $pendingApprovalCount, 'color' => '#3730a3'],
                    ] as $card)
                        <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px; border-top: 3px solid {{ $card['color'] }};">
                            <p style="margin: 0 0 6px 0; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600;">{{ $card['label'] }}</p>
                            <p style="margin: 0; font-size: 28px; font-weight: 700; color: {{ $card['color'] }};">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start; margin-bottom: 24px;">

                    <!-- Tickets recientes -->
                    <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                        <div style="background-color: #1e3a5f; padding: 16px 24px;">
                            <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Tickets Recientes</span>
                        </div>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead style="background-color: #f8fafc;">
                                <tr>
                                    <th style="padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Título</th>
                                    <th style="padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Prioridad</th>
                                    <th style="padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTickets as $ticket)
                                    @php
                                        $s = $statusColors[$ticket->status] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->status];
                                        $p = $priorityColors[$ticket->priority] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->priority];
                                    @endphp
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 12px 16px; font-size: 13px;">
                                            <a href="{{ route('tickets.show', $ticket) }}" style="color: #1e293b; font-weight: 500; text-decoration: none;">{{ $ticket->title }}</a>
                                        </td>
                                        <td style="padding: 12px 16px;">
                                            <span style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">{{ $p['label'] }}</span>
                                        </td>
                                        <td style="padding: 12px 16px;">
                                            <span style="background-color: {{ $s['bg'] }}; color: {{ $s['text'] }}; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">{{ $s['label'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="padding: 20px; text-align: center; color: #64748b; font-size: 13px;">No hay tickets registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Carga de trabajo de agentes -->
                    <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                        <div style="background-color: #1e3a5f; padding: 16px 24px;">
                            <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Carga de Agentes</span>
                        </div>
                        <div style="padding: 8px 20px;">
                            @forelse($agents as $agent)
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                                    <span style="font-size: 14px; color: #374151;">{{ $agent->name }}</span>
                                    <span style="background-color: #dbeafe; color: #1e40af; padding: 3px 10px; border-radius: 9999px; font-size: 12px; font-weight: 700;">{{ $agent->assigned_tickets_count }}</span>
                                </div>
                            @empty
                                <p style="padding: 12px 0; color: #64748b; font-size: 13px;">No hay agentes registrados.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Tickets por categoría -->
                <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                    <div style="background-color: #1e3a5f; padding: 16px 24px;">
                        <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Tickets por Categoría</span>
                    </div>
                    <div style="padding: 24px;">
                        @forelse($categoryBreakdown as $category)
                            @php $width = max(($category->tickets_count / $maxCategoryCount) * 100, 3); @endphp
                            <div style="margin-bottom: 16px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="font-size: 13px; color: #374151; font-weight: 500;">{{ $category->name }}</span>
                                    <span style="font-size: 13px; color: #1e3a5f; font-weight: 700;">{{ $category->tickets_count }}</span>
                                </div>
                                <div style="background-color: #e2e8f0; border-radius: 9999px; height: 10px; width: 100%; overflow: hidden;">
                                    <div style="background-color: #2563eb; height: 100%; border-radius: 9999px; width: {{ $width }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p style="color: #64748b; font-size: 13px; margin: 0;">No hay categorías registradas.</p>
                        @endforelse
                    </div>
                </div>

            @elseif(Auth::user()->role === 'agent')

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
                    @foreach([
                        ['label' => 'Asignados', 'value' => $assignedCount, 'color' => '#1e40af'],
                        ['label' => 'En Progreso', 'value' => $inProgressCount, 'color' => '#854d0e'],
                        ['label' => 'Resueltos Hoy', 'value' => $resolvedTodayCount, 'color' => '#166534'],
                    ] as $card)
                        <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px; border-top: 3px solid {{ $card['color'] }};">
                            <p style="margin: 0 0 6px 0; font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600;">{{ $card['label'] }}</p>
                            <p style="margin: 0; font-size: 28px; font-weight: 700; color: {{ $card['color'] }};">{{ $card['value'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                    <div style="background-color: #1e3a5f; padding: 16px 24px;">
                        <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Mis Tickets Pendientes</span>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Título</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Categoría</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Prioridad</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Estado</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignedTickets as $ticket)
                                @php
                                    $s = $statusColors[$ticket->status] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->status];
                                    $p = $priorityColors[$ticket->priority] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->priority];
                                @endphp
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 14px 24px; font-size: 14px; color: #1e293b; font-weight: 500;">{{ $ticket->title }}</td>
                                    <td style="padding: 14px 24px; font-size: 13px; color: #64748b;">{{ $ticket->category->name }}</td>
                                    <td style="padding: 14px 24px;">
                                        <span style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">{{ $p['label'] }}</span>
                                    </td>
                                    <td style="padding: 14px 24px;">
                                        <span style="background-color: {{ $s['bg'] }}; color: {{ $s['text'] }}; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">{{ $s['label'] }}</span>
                                    </td>
                                    <td style="padding: 14px 24px;">
                                        <a href="{{ route('tickets.show', $ticket) }}" style="color: #2563eb; font-weight: 600; text-decoration: none; font-size: 13px;">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 24px; text-align: center; color: #64748b; font-size: 14px;">No tienes tickets pendientes asignados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @elseif(Auth::user()->role === 'approver')

                <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 24px;">
                    <div style="background-color: #1e3a5f; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Tickets Pendientes de tu Aprobación</span>
                        <a href="{{ route('approvals.index') }}" style="background-color: #2563eb; color: #ffffff; padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600;">Ir a Aprobaciones</a>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Ticket</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Solicitante</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Nivel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingApprovals as $approval)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 14px 24px; font-size: 14px;">
                                        <a href="{{ route('tickets.show', $approval->ticket) }}" style="color: #1e293b; font-weight: 500; text-decoration: none;">{{ $approval->ticket->title }}</a>
                                    </td>
                                    <td style="padding: 14px 24px; font-size: 13px; color: #64748b;">{{ $approval->ticket->user->name }}</td>
                                    <td style="padding: 14px 24px;">
                                        <span style="background-color: #e0e7ff; color: #3730a3; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">{{ $approval->approvalLevel->name }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="padding: 24px; text-align: center; color: #64748b; font-size: 14px;">No tienes aprobaciones pendientes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                    <div style="background-color: #1e3a5f; padding: 16px 24px;">
                        <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Historial de Aprobaciones Recientes</span>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Ticket</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Nivel</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Decisión</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($approvalHistory as $approval)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 14px 24px; font-size: 14px;">
                                        <a href="{{ route('tickets.show', $approval->ticket) }}" style="color: #1e293b; font-weight: 500; text-decoration: none;">{{ $approval->ticket->title }}</a>
                                    </td>
                                    <td style="padding: 14px 24px; font-size: 13px; color: #64748b;">{{ $approval->approvalLevel->name }}</td>
                                    <td style="padding: 14px 24px;">
                                        @if($approval->status === 'approved')
                                            <span style="background-color: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">Aprobado</span>
                                        @else
                                            <span style="background-color: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">Rechazado</span>
                                        @endif
                                    </td>
                                    <td style="padding: 14px 24px; font-size: 13px; color: #64748b;">{{ $approval->approved_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="padding: 24px; text-align: center; color: #64748b; font-size: 14px;">Aún no has aprobado ni rechazado ningún ticket.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @else

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start; margin-bottom: 24px;">

                    <!-- Acciones rápidas -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <a href="{{ route('tickets.create') }}" style="text-decoration: none; background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #2563eb; padding: 24px; display: block;">
                            <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #1e3a5f;">Reportar un problema</h3>
                            <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Crea un nuevo ticket para que nuestro equipo lo atienda.</p>
                            <span style="font-size: 13px; font-weight: 600; color: #2563eb;">Crear ticket &rarr;</span>
                        </a>

                        <a href="{{ route('tickets.index') }}" style="text-decoration: none; background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #2563eb; padding: 24px; display: block;">
                            <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #1e3a5f;">Mis tickets</h3>
                            <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Revisa el estado de tus solicitudes anteriores.</p>
                            <span style="font-size: 13px; font-weight: 600; color: #2563eb;">Ver tickets &rarr;</span>
                        </a>

                        <a href="{{ route('solutions.index') }}" style="text-decoration: none; background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #2563eb; padding: 24px; display: block;">
                            <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #1e3a5f;">Ver soluciones</h3>
                            <p style="margin: 0 0 12px 0; font-size: 13px; color: #64748b;">Consulta respuestas a problemas comunes.</p>
                            <span style="font-size: 13px; font-weight: 600; color: #2563eb;">Explorar &rarr;</span>
                        </a>
                    </div>

                    <!-- Resumen lateral -->
                    <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                        <div style="background-color: #1e3a5f; padding: 16px 20px;">
                            <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Resumen de tickets</span>
                        </div>
                        <div style="padding: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #94a3b8; display: inline-block;"></span>
                                    <span style="font-size: 14px; color: #374151;">Abiertos</span>
                                </div>
                                <span style="font-size: 18px; font-weight: 700; color: #1e3a5f;">{{ $openCount }}</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #eab308; display: inline-block;"></span>
                                    <span style="font-size: 14px; color: #374151;">En progreso</span>
                                </div>
                                <span style="font-size: 18px; font-weight: 700; color: #1e3a5f;">{{ $inProgressCount }}</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background-color: #16a34a; display: inline-block;"></span>
                                    <span style="font-size: 14px; color: #374151;">Resueltos</span>
                                </div>
                                <span style="font-size: 18px; font-weight: 700; color: #1e3a5f;">{{ $resolvedCount }}</span>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Mis tickets activos -->
                <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                    <div style="background-color: #1e3a5f; padding: 16px 24px;">
                        <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Mis Tickets Activos</span>
                    </div>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead style="background-color: #f8fafc;">
                            <tr>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Título</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Categoría</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Estado</th>
                                <th style="padding: 10px 24px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e2e8f0;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeTickets as $ticket)
                                @php $s = $statusColors[$ticket->status] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->status]; @endphp
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 14px 24px; font-size: 14px; color: #1e293b; font-weight: 500;">{{ $ticket->title }}</td>
                                    <td style="padding: 14px 24px; font-size: 13px; color: #64748b;">{{ $ticket->category->name }}</td>
                                    <td style="padding: 14px 24px;">
                                        <span style="background-color: {{ $s['bg'] }}; color: {{ $s['text'] }}; padding: 3px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600;">{{ $s['label'] }}</span>
                                    </td>
                                    <td style="padding: 14px 24px;">
                                        <a href="{{ route('tickets.show', $ticket) }}" style="color: #2563eb; font-weight: 600; text-decoration: none; font-size: 13px;">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="padding: 24px; text-align: center; color: #64748b; font-size: 14px;">No tienes tickets activos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>
