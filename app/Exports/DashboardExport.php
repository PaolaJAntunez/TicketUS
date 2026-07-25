<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Espejo del dashboard en Excel: una hoja por sección, usando exactamente
 * los mismos datos ($data) que ya calculó DashboardController para pantalla
 * y PDF — no vuelve a consultar la base de datos. Las secciones que no
 * apliquen al rol actual (ej. "Por Agente" para un agente) simplemente no
 * están en $data, así que su hoja no se genera.
 */
class DashboardExport implements WithMultipleSheets
{
    protected array $statusLabels = [
        'open' => 'Abierto', 'assigned' => 'Asignado', 'in_progress' => 'En Progreso',
        'on_hold' => 'En Espera', 'pending_approval' => 'Pendiente de aprobación', 'resolved' => 'Resuelto',
        'closed' => 'Cerrado', 'rejected' => 'Rechazado', 'cancelled' => 'Cancelado',
    ];

    protected array $priorityLabels = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'];

    protected array $monthLabels = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
        '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
    ];

    public function __construct(protected array $data)
    {
    }

    public function sheets(): array
    {
        $d = $this->data;
        $sheets = [];

        $sheets[] = new DashboardSheet('Resumen', $this->resumenRows($d));

        if (isset($d['ticketsByAgent'])) {
            $rows = [['Agente', 'Tickets Asignados']];
            foreach ($d['ticketsByAgent'] as $agent) {
                $rows[] = [$agent->name, $agent->assigned_tickets_count];
            }
            $sheets[] = new DashboardSheet('Por Agente', $rows);
        }

        if (isset($d['ticketsByStatus'])) {
            $rows = [['Estado', 'Total']];
            foreach ($d['ticketsByStatus'] as $row) {
                $rows[] = [$this->statusLabels[$row->status] ?? $row->status, $row->total];
            }
            $sheets[] = new DashboardSheet('Por Estado', $rows);
        }

        if (isset($d['ticketsByPriority'])) {
            $rows = [['Prioridad', 'Total']];
            foreach ($d['ticketsByPriority'] as $row) {
                $rows[] = [$this->priorityLabels[$row->priority] ?? $row->priority, $row->total];
            }
            $sheets[] = new DashboardSheet('Por Prioridad', $rows);
        }

        if (isset($d['ticketsByCategory'])) {
            $rows = [['Categoría', 'Tickets']];
            foreach ($d['ticketsByCategory'] as $cat) {
                $rows[] = [$cat->name, $cat->tickets_count];
            }
            $sheets[] = new DashboardSheet('Por Categoría', $rows);
        }

        if (isset($d['ticketsByMonth'])) {
            $rows = [['Mes', 'Total']];
            foreach ($d['ticketsByMonth'] as $row) {
                $rows[] = [$this->monthLabels[$row->month] ?? $row->month, $row->total];
            }
            $sheets[] = new DashboardSheet('Por Mes', $rows);
        }

        if (isset($d['avgResolutionByCategory'])) {
            $rows = [['Categoría', 'Promedio (horas)', 'Resueltos']];
            foreach ($d['avgResolutionByCategory'] as $row) {
                $rows[] = [$row->name, round($row->avg_hours, 1), $row->resolved_count];
            }
            $sheets[] = new DashboardSheet('Resolución x Categoría', $rows);
        }

        if (isset($d['ticketsByPriorityMonth'])) {
            $rows = [['Mes', 'Prioridad', 'Total']];
            foreach ($d['ticketsByPriorityMonth'] as $row) {
                $rows[] = [
                    $this->monthLabels[$row->month] ?? $row->month,
                    $this->priorityLabels[$row->priority] ?? $row->priority,
                    $row->total,
                ];
            }
            $sheets[] = new DashboardSheet('Prioridad x Mes', $rows);
        }

        if (isset($d['topCategories'])) {
            $rows = [['#', 'Categoría', 'Tickets']];
            foreach ($d['topCategories'] as $i => $cat) {
                $rows[] = [$i + 1, $cat->name, $cat->tickets_count];
            }
            $sheets[] = new DashboardSheet('Top Categorías', $rows);
        }

        if (isset($d['topAgentsThisMonth'])) {
            $rows = [['Agente', 'Resueltos (mes actual)']];
            foreach ($d['topAgentsThisMonth'] as $row) {
                $rows[] = [$row->agent?->name ?? '—', $row->total];
            }
            $sheets[] = new DashboardSheet('Top Agentes', $rows);
        }

        if (isset($d['unassignedStaleTickets'])) {
            $rows = [['#', 'Título', 'Categoría', 'Antigüedad (h)']];
            foreach ($d['unassignedStaleTickets'] as $ticket) {
                $rows[] = [$ticket->id, $ticket->title, $ticket->category->name ?? '—', (int) round($ticket->created_at->diffInHours(now()))];
            }
            $sheets[] = new DashboardSheet('Sin Asignar', $rows);
        }

        if (isset($d['assignedTickets'])) {
            $rows = [['#', 'Título', 'Prioridad', 'Estado', 'Solicitante']];
            foreach ($d['assignedTickets'] as $ticket) {
                $rows[] = [
                    $ticket->id, $ticket->title,
                    $this->priorityLabels[$ticket->priority] ?? $ticket->priority,
                    $this->statusLabels[$ticket->status] ?? $ticket->status,
                    $ticket->user->name ?? '—',
                ];
            }
            $sheets[] = new DashboardSheet('Mis Asignados', $rows);
        }

        if (isset($d['dueSoon'])) {
            $rows = [['#', 'Título', 'Vence']];
            foreach ($d['dueSoon'] as $ticket) {
                $rows[] = [$ticket->id, $ticket->title, $ticket->due_date->format('d/m/Y H:i')];
            }
            $sheets[] = new DashboardSheet('Próximos a Vencer', $rows);
        }

        if (isset($d['activeTickets'])) {
            $rows = [['#', 'Título', 'Categoría', 'Estado']];
            foreach ($d['activeTickets'] as $ticket) {
                $rows[] = [$ticket->id, $ticket->title, $ticket->category->name ?? '—', $this->statusLabels[$ticket->status] ?? $ticket->status];
            }
            $sheets[] = new DashboardSheet('Mis Tickets Activos', $rows);
        }

        if (isset($d['recentCreated'])) {
            $rows = [['#', 'Título', 'Categoría', 'Creado']];
            foreach ($d['recentCreated'] as $ticket) {
                $rows[] = [$ticket->id, $ticket->title, $ticket->category->name ?? '—', $ticket->created_at->format('d/m/Y H:i')];
            }
            $sheets[] = new DashboardSheet('Últimos Creados', $rows);
        }

        if (isset($d['recentResolved'])) {
            $rows = [['#', 'Título', 'Categoría', 'Resuelto']];
            foreach ($d['recentResolved'] as $ticket) {
                $rows[] = [$ticket->id, $ticket->title, $ticket->category->name ?? '—', $ticket->resolved_at?->format('d/m/Y H:i') ?? '—'];
            }
            $sheets[] = new DashboardSheet('Últimos Resueltos', $rows);
        }

        return $sheets;
    }

    protected function resumenRows(array $d): array
    {
        $rows = [
            ['TicketUS - Reporte de Dashboard'],
            ['Generado el '.now()->format('d/m/Y H:i').' por '.$d['generatedBy']->name.' ('.ucfirst($d['generatedBy']->role).')'],
            ['Rango de fechas: '.$d['from']->format('d/m/Y').' - '.$d['to']->format('d/m/Y')],
            [],
            ['Métrica', 'Valor'],
        ];

        $metrics = [
            'totalCount' => 'Total de Tickets',
            'openCount' => 'Tickets Abiertos',
            'inProgressCount' => 'En Progreso',
            'resolvedCount' => 'Resueltos',
            'assignedCount' => 'Asignados',
            'resolvedTodayCount' => 'Resueltos Hoy',
            'overdueCount' => 'Tickets Vencidos',
            'pendingApprovalsSystemCount' => 'Aprobaciones Pendientes (sistema)',
            'myPendingApprovals' => 'Mis Aprobaciones Pendientes',
            'reopenedCount' => 'Tickets Reabiertos',
        ];

        foreach ($metrics as $key => $label) {
            if (isset($d[$key])) {
                $rows[] = [$label, $d[$key]];
            }
        }

        if (isset($d['avgResolutionHours'])) {
            $rows[] = ['Tiempo Prom. Resolución (h)', $d['avgResolutionHours'] ? round($d['avgResolutionHours'], 1) : '—'];
        }

        if (isset($d['thisWeekCount'])) {
            $rows[] = ['Tickets Esta Semana', $d['thisWeekCount']];
            $rows[] = ['Tickets Semana Anterior', $d['lastWeekCount']];
            $rows[] = ['Tendencia Semanal (%)', ($d['weekTrendDirection'] === 'up' ? '+' : '-').number_format(abs($d['weekTrendPercent']), 1)];
        }

        return $rows;
    }
}
