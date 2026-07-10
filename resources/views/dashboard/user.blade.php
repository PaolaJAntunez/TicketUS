<x-app-layout>

    @include('dashboard.partials.app')

        @include('dashboard.partials.header')

        @include('dashboard.partials.cards')

        @include('dashboard.partials.user-charts')

        @include('dashboard.partials.manuals')

        @include('dashboard.partials.services')

    </div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const idioma = localStorage.getItem('ticketus_lang') || 'es';

    const estados = @json($myTicketsByStatus);

    const meses = @json($myTicketsByMonth);

    //==========================
    // Traducciones
    //==========================

    const traduccionesEstado = {

        open: idioma === 'es' ? 'Abierto' : 'Open',

        assigned: idioma === 'es' ? 'Asignado' : 'Assigned',

        in_progress: idioma === 'es' ? 'En progreso' : 'In Progress',

        resolved: idioma === 'es' ? 'Resuelto' : 'Resolved',

        closed: idioma === 'es' ? 'Cerrado' : 'Closed'

    };

    const coloresEstado = {

        open:'#f59e0b',

        assigned:'#2563eb',

        in_progress:'#14b8a6',

        resolved:'#22c55e',

        closed:'#64748b'

    };

    //==========================
    // PIE CHART
    //==========================

    const ctxPie = document.getElementById('myTicketsStatusChart');

    if(ctxPie){

        new Chart(ctxPie,{

            type:'pie',

            data:{

                labels: estados.map(
                    s => traduccionesEstado[s.status] ?? s.status
                ),

                datasets:[{

                    data: estados.map(s => s.total),

                    backgroundColor: estados.map(
                        s => coloresEstado[s.status] || '#94a3b8'
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

                            color:'#ffffff'

                        }

                    }

                }

            }

        });

    }

    //==========================
    // LINE CHART
    //==========================

    const ctxLine = document.getElementById('myTicketsMonthChart');

    if(ctxLine){

        const nombresMeses = {

            "01": idioma === 'es' ? "Ene" : "Jan",
            "02": idioma === 'es' ? "Feb" : "Feb",
            "03": idioma === 'es' ? "Mar" : "Mar",
            "04": idioma === 'es' ? "Abr" : "Apr",
            "05": idioma === 'es' ? "May" : "May",
            "06": idioma === 'es' ? "Jun" : "Jun",
            "07": idioma === 'es' ? "Jul" : "Jul",
            "08": idioma === 'es' ? "Ago" : "Aug",
            "09": idioma === 'es' ? "Sep" : "Sep",
            "10": idioma === 'es' ? "Oct" : "Oct",
            "11": idioma === 'es' ? "Nov" : "Nov",
            "12": idioma === 'es' ? "Dic" : "Dec"

        };

        new Chart(ctxLine,{

            type:'line',

            data:{

                labels: meses.map(
                    m => nombresMeses[m.month]
                ),

                datasets:[{

                    label: idioma === 'es'
                        ? 'Mis Tickets'
                        : 'My Tickets',

                    data: meses.map(m => m.total),

                    borderColor:'#3b82f6',

                    backgroundColor:'#3b82f6',

                    borderWidth:3,

                    tension:.35,

                    fill:false

                }]

            },

            options:{

                responsive:true,

                maintainAspectRatio:false,

                plugins:{

                    legend:{

                        labels:{

                            color:'#ffffff'

                        }

                    }

                },

                scales:{

                    x:{

                        ticks:{color:'#ffffff'},

                        grid:{color:'#334155'}

                    },

                    y:{

                        beginAtZero:true,

                        ticks:{color:'#ffffff'},

                        grid:{color:'#334155'}

                    }

                }

            }

        });

    }

});

</script>

</x-app-layout>