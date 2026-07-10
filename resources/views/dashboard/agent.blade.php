<x-app-layout>

    @include('dashboard.partials.app')

        @include('dashboard.partials.header')

        @include('dashboard.partials.cards')

        @include('dashboard.partials.agent-charts')

        @include('dashboard.partials.services')

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

        assigned: idioma === 'es' ? 'Asignado' : 'Assigned',

        in_progress: idioma === 'es' ? 'En progreso' : 'In Progress',

        resolved: idioma === 'es' ? 'Resuelto' : 'Resolved',

        closed: idioma === 'es' ? 'Cerrado' : 'Closed'

    };

    const traduccionesPrioridad = {

        low: idioma === 'es' ? 'Baja' : 'Low',

        medium: idioma === 'es' ? 'Media' : 'Medium',

        high: idioma === 'es' ? 'Alta' : 'High',

        critical: idioma === 'es' ? 'Crítica' : 'Critical'

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

                    backgroundColor: [

                        '#22c55e',

                        '#3b82f6',

                        '#f59e0b',

                        '#ef4444'

                    ],

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