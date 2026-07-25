<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }

        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #1e3a5f; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #1e3a5f; }
        .header .meta { font-size: 10px; color: #64748b; margin-top: 4px; }

        .section-title { font-size: 14px; font-weight: bold; color: #1e3a5f; margin: 16px 0 8px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; }

        table.cards { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.cards td { border: 1px solid #cbd5e1; padding: 8px; width: 25%; vertical-align: top; }
        table.cards .label { font-size: 9px; color: #64748b; text-transform: uppercase; }
        table.cards .value { font-size: 15px; font-weight: bold; color: #1e3a5f; }

        table.data { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.data th { background: #1e3a5f; color: #ffffff; padding: 5px 8px; text-align: left; font-size: 10px; }
        table.data td { border: 1px solid #ccc; padding: 5px 8px; font-size: 10px; }
        table.data td.num { text-align: right; }
        table.data tr:nth-child(even) { background: #f5f5f5; }

        .empty { color: #94a3b8; font-size: 10px; font-style: italic; margin: 0 0 14px; }
    </style>
</head>
<body>

@php
    $statusLabels = [
        'open' => 'Abierto', 'assigned' => 'Asignado', 'in_progress' => 'En Progreso',
        'on_hold' => 'En Espera', 'pending_approval' => 'Pendiente de aprobación', 'resolved' => 'Resuelto',
        'closed' => 'Cerrado', 'rejected' => 'Rechazado', 'cancelled' => 'Cancelado',
    ];
    $priorityLabels = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'];
    $monthLabels = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
        '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
    ];
@endphp

<div class="header">
    <h1>TicketUS &mdash; Reporte de Dashboard</h1>
    <p class="meta">
        Generado el {{ now()->format('d/m/Y H:i') }} por {{ $generatedBy->name }} ({{ ucfirst($generatedBy->role) }})<br>
        Rango de fechas: {{ $from->format('d/m/Y') }} &ndash; {{ $to->format('d/m/Y') }}
    </p>
</div>

{{-- ==================== TARJETAS RESUMEN ==================== --}}
<div class="section-title">Resumen</div>
<table class="cards">
    <tr>
        @isset($totalCount)
            <td><div class="label">Total de Tickets</div><div class="value">{{ $totalCount }}</div></td>
        @endisset
        @isset($openCount)
            <td><div class="label">Tickets Abiertos</div><div class="value">{{ $openCount }}</div></td>
        @endisset
        @isset($inProgressCount)
            <td><div class="label">En Progreso</div><div class="value">{{ $inProgressCount }}</div></td>
        @endisset
        @isset($resolvedCount)
            <td><div class="label">Resueltos</div><div class="value">{{ $resolvedCount }}</div></td>
        @endisset
    </tr>
    <tr>
        @isset($assignedCount)
            <td><div class="label">Asignados</div><div class="value">{{ $assignedCount }}</div></td>
        @endisset
        @isset($resolvedTodayCount)
            <td><div class="label">Resueltos Hoy</div><div class="value">{{ $resolvedTodayCount }}</div></td>
        @endisset
        @isset($overdueCount)
            <td><div class="label">Tickets Vencidos</div><div class="value">{{ $overdueCount }}</div></td>
        @endisset
        @isset($avgResolutionHours)
            <td><div class="label">Tiempo Prom. Resolución</div><div class="value">{{ $avgResolutionHours ? number_format($avgResolutionHours, 1).'h' : '—' }}</div></td>
        @endisset
    </tr>
    <tr>
        @isset($pendingApprovalsSystemCount)
            <td><div class="label">Aprobaciones Pendientes (sistema)</div><div class="value">{{ $pendingApprovalsSystemCount }}</div></td>
        @endisset
        @isset($myPendingApprovals)
            <td><div class="label">Mis Aprobaciones Pendientes</div><div class="value">{{ $myPendingApprovals }}</div></td>
        @endisset
        @isset($reopenedCount)
            <td><div class="label">Tickets Reabiertos</div><div class="value">{{ $reopenedCount }}</div></td>
        @endisset
        @isset($thisWeekCount)
            <td>
                <div class="label">Tendencia Semanal</div>
                <div class="value">{{ $weekTrendDirection === 'up' ? '↑' : '↓' }} {{ number_format(abs($weekTrendPercent), 1) }}%</div>
                <div style="font-size:9px; color:#64748b;">{{ $thisWeekCount }} esta semana vs. {{ $lastWeekCount }}</div>
            </td>
        @endisset
    </tr>
</table>

{{-- ==================== GRÁFICAS (como tablas de datos) ==================== --}}

@isset($ticketsByAgent)
    <div class="section-title">Tickets por Agente</div>
    @if($ticketsByAgent->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>Agente</th><th>Tickets Asignados</th></tr></thead>
            <tbody>
                @foreach($ticketsByAgent as $agent)
                    <tr><td>{{ $agent->name }}</td><td class="num">{{ $agent->assigned_tickets_count }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($ticketsByStatus)
    <div class="section-title">Tickets por Estado</div>
    @if($ticketsByStatus->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>Estado</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($ticketsByStatus as $row)
                    <tr><td>{{ $statusLabels[$row->status] ?? $row->status }}</td><td class="num">{{ $row->total }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($ticketsByPriority)
    <div class="section-title">Tickets por Prioridad</div>
    @if($ticketsByPriority->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>Prioridad</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($ticketsByPriority as $row)
                    <tr><td>{{ $priorityLabels[$row->priority] ?? $row->priority }}</td><td class="num">{{ $row->total }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($ticketsByCategory)
    <div class="section-title">Tickets por Categoría</div>
    @if($ticketsByCategory->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>Categoría</th><th>Tickets</th></tr></thead>
            <tbody>
                @foreach($ticketsByCategory as $cat)
                    <tr><td>{{ $cat->name }}</td><td class="num">{{ $cat->tickets_count }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($ticketsByMonth)
    <div class="section-title">{{ isset($ticketsByAgent) ? 'Tickets Creados por Mes' : 'Mis Tickets por Mes' }}</div>
    @if($ticketsByMonth->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>Mes</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($ticketsByMonth as $row)
                    <tr><td>{{ $monthLabels[$row->month] ?? $row->month }}</td><td class="num">{{ $row->total }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

{{-- Solo admin: resolución promedio por categoría, prioridad por mes, top 5 --}}
@isset($avgResolutionByCategory)
    <div class="section-title">Resolución Promedio por Categoría</div>
    @if($avgResolutionByCategory->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>Categoría</th><th>Promedio</th><th>Resueltos</th></tr></thead>
            <tbody>
                @foreach($avgResolutionByCategory as $row)
                    <tr>
                        <td>{{ $row->name }}</td>
                        <td class="num">{{ number_format($row->avg_hours, 1) }}h</td>
                        <td class="num">{{ $row->resolved_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($ticketsByPriorityMonth)
    <div class="section-title">Tickets por Prioridad y Mes</div>
    @if($ticketsByPriorityMonth->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>Mes</th><th>Prioridad</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($ticketsByPriorityMonth as $row)
                    <tr>
                        <td>{{ $monthLabels[$row->month] ?? $row->month }}</td>
                        <td>{{ $priorityLabels[$row->priority] ?? $row->priority }}</td>
                        <td class="num">{{ $row->total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($topCategories)
    <div class="section-title">Top 5 Categorías con Más Incidentes</div>
    @if($topCategories->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>#</th><th>Categoría</th><th>Tickets</th></tr></thead>
            <tbody>
                @foreach($topCategories as $i => $cat)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $cat->name }}</td><td class="num">{{ $cat->tickets_count }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

{{-- ==================== TABLAS / LISTAS ==================== --}}

@isset($topAgentsThisMonth)
    <div class="section-title">Top 5 Agentes (resueltos este mes)</div>
    @if($topAgentsThisMonth->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>Agente</th><th>Resueltos</th></tr></thead>
            <tbody>
                @foreach($topAgentsThisMonth as $row)
                    <tr><td>{{ $row->agent?->name ?? '—' }}</td><td class="num">{{ $row->total }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($unassignedStaleTickets)
    <div class="section-title">Tickets Sin Asignar (+24h)</div>
    @if($unassignedStaleTickets->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>#</th><th>Título</th><th>Categoría</th><th>Antigüedad</th></tr></thead>
            <tbody>
                @foreach($unassignedStaleTickets as $ticket)
                    <tr>
                        <td>#{{ $ticket->id }}</td>
                        <td>{{ $ticket->title }}</td>
                        <td>{{ $ticket->category->name ?? '—' }}</td>
                        <td class="num">{{ (int) round($ticket->created_at->diffInHours(now())) }}h</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($assignedTickets)
    <div class="section-title">Mis Tickets Asignados</div>
    @if($assignedTickets->isEmpty())
        <p class="empty">No tienes tickets asignados.</p>
    @else
        <table class="data">
            <thead><tr><th>#</th><th>Título</th><th>Prioridad</th><th>Estado</th><th>Solicitante</th></tr></thead>
            <tbody>
                @foreach($assignedTickets as $ticket)
                    <tr>
                        <td>#{{ $ticket->id }}</td>
                        <td>{{ $ticket->title }}</td>
                        <td>{{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}</td>
                        <td>{{ $statusLabels[$ticket->status] ?? $ticket->status }}</td>
                        <td>{{ $ticket->user->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($dueSoon)
    <div class="section-title">Próximos a Vencer (24h)</div>
    @if($dueSoon->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>#</th><th>Título</th><th>Vence</th></tr></thead>
            <tbody>
                @foreach($dueSoon as $ticket)
                    <tr><td>#{{ $ticket->id }}</td><td>{{ $ticket->title }}</td><td class="num">{{ $ticket->due_date->format('d/m H:i') }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($activeTickets)
    <div class="section-title">Mis Tickets Activos</div>
    @if($activeTickets->isEmpty())
        <p class="empty">No tienes tickets activos.</p>
    @else
        <table class="data">
            <thead><tr><th>#</th><th>Título</th><th>Categoría</th><th>Estado</th></tr></thead>
            <tbody>
                @foreach($activeTickets as $ticket)
                    <tr>
                        <td>#{{ $ticket->id }}</td>
                        <td>{{ $ticket->title }}</td>
                        <td>{{ $ticket->category->name ?? '—' }}</td>
                        <td>{{ $statusLabels[$ticket->status] ?? $ticket->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($recentCreated)
    <div class="section-title">Últimos Tickets Creados</div>
    @if($recentCreated->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>#</th><th>Título</th><th>Categoría</th><th>Creado</th></tr></thead>
            <tbody>
                @foreach($recentCreated as $ticket)
                    <tr>
                        <td>#{{ $ticket->id }}</td>
                        <td>{{ $ticket->title }}</td>
                        <td>{{ $ticket->category->name ?? '—' }}</td>
                        <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

@isset($recentResolved)
    <div class="section-title">Últimos Tickets Resueltos</div>
    @if($recentResolved->isEmpty())
        <p class="empty">Sin datos.</p>
    @else
        <table class="data">
            <thead><tr><th>#</th><th>Título</th><th>Categoría</th><th>Resuelto</th></tr></thead>
            <tbody>
                @foreach($recentResolved as $ticket)
                    <tr>
                        <td>#{{ $ticket->id }}</td>
                        <td>{{ $ticket->title }}</td>
                        <td>{{ $ticket->category->name ?? '—' }}</td>
                        <td>{{ $ticket->resolved_at?->format('d/m/Y H:i') ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endisset

</body>
</html>
