<x-app-layout>

    @include('dashboard.partials.app')

        @include('dashboard.partials.header')

        @include('dashboard.partials.date-filter')

        @include('dashboard.partials.report-buttons')

        @include('dashboard.partials.pending-approvals-banner')

        @include('dashboard.partials.cards')

        @include('dashboard.partials.agent-actionable')

        @include('dashboard.partials.agent-charts')

        @include('dashboard.partials.quick-lists')

        @include('dashboard.partials.manuals')

    </div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const idioma = localStorage.getItem('ticketus_lang') || 'es';

    const prioridades = @json($ticketsByPriority);

    const estados = @json($ticketsByStatus);

    // ==========================
    // Traducciones
    // ==========================

    const traduccionesEstado = {

        open: idioma === 'es' ? 'Abierto' : 'Open',

        assigned: idioma === 'es' ? 'Asignado' : 'Assigned',

        in_progress: idioma === 'es' ? 'En progreso' : 'In Progress',

        on_hold: idioma === 'es' ? 'En Espera' : 'On Hold',

        pending_approval: idioma === 'es' ? 'Pendiente de aprobación' : 'Pending Approval',

        resolved: idioma === 'es' ? 'Resuelto' : 'Resolved',

        closed: idioma === 'es' ? 'Cerrado' : 'Closed',

        rejected: idioma === 'es' ? 'Rechazado' : 'Rejected',

        cancelled: idioma === 'es' ? 'Cancelado' : 'Cancelled'

    };

    const traduccionesPrioridad = {

        low: idioma === 'es' ? 'Baja' : 'Low',

        medium: idioma === 'es' ? 'Media' : 'Medium',

        high: idioma === 'es' ? 'Alta' : 'High',

        urgent: idioma === 'es' ? 'Urgente' : 'Urgent'

    };

    const coloresPrioridad = {

        low:'#22c55e',
        medium:'#3b82f6',
        high:'#f59e0b',
        urgent:'#ef4444'

    };

    // ==========================
    // Gráfico por Prioridad
    // ==========================

    const ctxPriority = document.getElementById('ticketsByPriorityChart');

    if (ctxPriority) {

        new Chart(ctxPriority, {

            type: 'bar',

            data: {

                labels: prioridades.map(p => traduccionesPrioridad[p.priority] ?? p.priority),

                datasets: [{

                    label: idioma === 'es'
                        ? 'Cantidad de Tickets'
                        : 'Ticket Count',

                    data: prioridades.map(p => p.total),

                    backgroundColor: prioridades.map(p => coloresPrioridad[p.priority] || '#94a3b8'),

                    borderRadius: 8

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        labels: {

                            color: '#ffffff'

                        }

                    }

                },

                scales: {

                    x: {

                        ticks: {

                            color: '#ffffff'

                        },

                        grid: {

                            color: '#334155'

                        }

                    },

                    y: {

                        beginAtZero: true,

                        ticks: {

                            color: '#ffffff'

                        },

                        grid: {

                            color: '#334155'

                        }

                    }

                }

            }

        });

    }

    // ==========================
    // Pie Chart Estado
    // ==========================

    const ctxStatus = document.getElementById('ticketsStatusChart');

    if (ctxStatus) {

        new Chart(ctxStatus, {

            type: 'pie',

            data: {

                labels: estados.map(s => traduccionesEstado[s.status] ?? s.status),

                datasets: [{

                    data: estados.map(s => s.total),

                    backgroundColor: [

                        '#2563eb',

                        '#14b8a6',

                        '#22c55e',

                        '#64748b'

                    ]

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        position: 'bottom',

                        labels: {

                            color: '#ffffff'

                        }

                    }

                }

            }

        });

    }

});

</script>

</x-app-layout>