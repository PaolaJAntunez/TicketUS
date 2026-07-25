<x-app-layout>

    @include('dashboard.partials.app')

        @include('dashboard.partials.header')

        @include('dashboard.partials.date-filter')

        @include('dashboard.partials.report-buttons')

        @include('dashboard.partials.pending-approvals-banner')

        @include('dashboard.partials.cards')

        @include('dashboard.partials.admin-charts')

        @include('dashboard.partials.admin-charts-extra')

        @include('dashboard.partials.admin-tables')

        @include('dashboard.partials.admin-tables-extra')

        @include('dashboard.partials.quick-lists')

        @include('dashboard.partials.manuals')

    </div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const idioma = localStorage.getItem('ticketus_lang') || 'es';

    const agentes = @json($ticketsByAgent);
    const estados = @json($ticketsByStatus);
    const categorias = @json($ticketsByCategory);
    const meses = @json($ticketsByMonth);

    //==========================
    // Traducciones
    //==========================

    const traducciones = {

        open: idioma === 'es' ? 'Abierto' : 'Open',

        assigned: idioma === 'es' ? 'Asignado' : 'Assigned',

        in_progress: idioma === 'es' ? 'En Progreso' : 'In Progress',

        on_hold: idioma === 'es' ? 'En Espera' : 'On Hold',

        pending_approval: idioma === 'es'
            ? 'Pendiente de aprobación'
            : 'Pending Approval',

        resolved: idioma === 'es' ? 'Resuelto' : 'Resolved',

        closed: idioma === 'es' ? 'Cerrado' : 'Closed',

        rejected: idioma === 'es' ? 'Rechazado' : 'Rejected',

        cancelled: idioma === 'es' ? 'Cancelado' : 'Cancelled'

    };

    const coloresEstado = {

        open:'#f59e0b',

        assigned:'#2563eb',

        in_progress:'#14b8a6',

        on_hold:'#f97316',

        pending_approval:'#8b5cf6',

        resolved:'#22c55e',

        closed:'#64748b',

        rejected:'#f87171',

        cancelled:'#a78bfa'

    };

    //==========================
    // Tickets por Agente
    //==========================

    const ctxBar = document.getElementById('ticketsByAgentChart');

    if(ctxBar){

        new Chart(ctxBar,{

            type:'bar',

            data:{

                labels:agentes.map(a=>a.name),

                datasets:[{

                    label: idioma === 'es'
                        ? 'Tickets asignados'
                        : 'Assigned Tickets',

                    data:agentes.map(a=>a.assigned_tickets_count),

                    backgroundColor:'#2563eb',

                    hoverBackgroundColor:'#1d4ed8',

                    borderColor:'#60a5fa',

                    borderWidth:1,

                    borderRadius:8

                }]

            },

            options:{

                responsive:true,

                maintainAspectRatio:false,

                plugins:{

                    legend:{
                        labels:{
                            color:'#fff'
                        }
                    }

                },

                scales:{

                    x:{
                        ticks:{color:'#fff'},
                        grid:{color:'#334155'}
                    },

                    y:{
                        beginAtZero:true,
                        ticks:{color:'#fff'},
                        grid:{color:'#334155'}
                    }

                }

            }

        });

    }

    //==========================
    // Estado Global
    //==========================

    const ctxPie = document.getElementById('ticketsByStatusChart');

    if(ctxPie){

        new Chart(ctxPie,{

            type:'doughnut',

            data:{

                labels:estados.map(
                    s=>traducciones[s.status] ?? s.status
                ),

                datasets:[{

                    data:estados.map(s=>s.total),

                    backgroundColor:estados.map(
                        s=>coloresEstado[s.status] || '#94a3b8'
                    )

                }]

            },

            options:{

                responsive:true,

                maintainAspectRatio:false,

                plugins:{

                    legend:{

                        position:'bottom',

                        labels:{
                            color:'#fff'
                        }

                    }

                }

            }

        });

    }

    //==========================
    // Tickets por Categoría
    //==========================

    const ctxCategory =
        document.getElementById('ticketsByCategoryChart');

    if(ctxCategory){

        new Chart(ctxCategory,{

            type:'bar',

            data:{

                labels:categorias.map(c=>c.name),

                datasets:[{

                    label: idioma === 'es'
                        ? 'Tickets'
                        : 'Tickets',

                    data:categorias.map(c=>c.tickets_count),

                    backgroundColor:'#8b5cf6',

                    borderRadius:8

                }]

            },

            options:{

                indexAxis:'y',

                responsive:true,

                maintainAspectRatio:false,

                plugins:{

                    legend:{
                        labels:{color:'#fff'}
                    }

                },

                scales:{

                    x:{
                        beginAtZero:true,
                        ticks:{color:'#fff'},
                        grid:{color:'#334155'}
                    },

                    y:{
                        ticks:{color:'#fff'},
                        grid:{color:'#334155'}
                    }

                }

            }

        });

    }

    //==========================
    // Tickets por Mes
    //==========================

    const ctxMonth =
        document.getElementById('ticketsByMonthChart');

    if(ctxMonth){

        const nombresMeses = {

            "01": idioma === 'es' ? "Ene":"Jan",

            "02": idioma === 'es' ? "Feb":"Feb",

            "03": idioma === 'es' ? "Mar":"Mar",

            "04": idioma === 'es' ? "Abr":"Apr",

            "05": idioma === 'es' ? "May":"May",

            "06": idioma === 'es' ? "Jun":"Jun",

            "07": idioma === 'es' ? "Jul":"Jul",

            "08": idioma === 'es' ? "Ago":"Aug",

            "09": idioma === 'es' ? "Sep":"Sep",

            "10": idioma === 'es' ? "Oct":"Oct",

            "11": idioma === 'es' ? "Nov":"Nov",

            "12": idioma === 'es' ? "Dic":"Dec"

        };

        new Chart(ctxMonth,{

            type:'line',

            data:{

                labels:meses.map(
                    m=>nombresMeses[m.month]
                ),

                datasets:[{

                    label: idioma === 'es'
                        ? 'Tickets'
                        : 'Tickets',

                    data:meses.map(m=>m.total),

                    borderColor:'#22c55e',

                    backgroundColor:'#22c55e',

                    fill:false,

                    tension:.35

                }]

            },

            options:{

                responsive:true,

                maintainAspectRatio:false,

                plugins:{

                    legend:{
                        labels:{color:'#fff'}
                    }

                },

                scales:{

                    x:{
                        ticks:{color:'#fff'},
                        grid:{color:'#334155'}
                    },

                    y:{
                        beginAtZero:true,
                        ticks:{color:'#fff'},
                        grid:{color:'#334155'}
                    }

                }

            }

        });

    }

    //==========================
    // Tickets por Prioridad y Mes (apiladas)
    //==========================

    const ctxPriorityMonth = document.getElementById('ticketsByPriorityMonthChart');

    if (ctxPriorityMonth) {

        const prioridadMes = @json($ticketsByPriorityMonth);

        const nombresMesesPM = {
            "01": idioma === 'es' ? "Ene":"Jan",
            "02": idioma === 'es' ? "Feb":"Feb",
            "03": idioma === 'es' ? "Mar":"Mar",
            "04": idioma === 'es' ? "Abr":"Apr",
            "05": idioma === 'es' ? "May":"May",
            "06": idioma === 'es' ? "Jun":"Jun",
            "07": idioma === 'es' ? "Jul":"Jul",
            "08": idioma === 'es' ? "Ago":"Aug",
            "09": idioma === 'es' ? "Sep":"Sep",
            "10": idioma === 'es' ? "Oct":"Oct",
            "11": idioma === 'es' ? "Nov":"Nov",
            "12": idioma === 'es' ? "Dic":"Dec"
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

        const mesesUnicos = [...new Set(prioridadMes.map(r => r.month))].sort();
        const prioridadesOrden = ['low', 'medium', 'high', 'urgent'];

        const datasetsPM = prioridadesOrden.map(function (p) {
            return {
                label: traduccionesPrioridad[p],
                data: mesesUnicos.map(function (m) {
                    const fila = prioridadMes.find(function (r) { return r.month === m && r.priority === p; });
                    return fila ? fila.total : 0;
                }),
                backgroundColor: coloresPrioridad[p],
                borderRadius: 4
            };
        });

        new Chart(ctxPriorityMonth, {

            type: 'bar',

            data: {
                labels: mesesUnicos.map(function (m) { return nombresMesesPM[m]; }),
                datasets: datasetsPM
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#fff' } }
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: { color: '#fff' },
                        grid: { color: '#334155' }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { color: '#fff' },
                        grid: { color: '#334155' }
                    }
                }
            }

        });

    }

});

</script>

</x-app-layout>