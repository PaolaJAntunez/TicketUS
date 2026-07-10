<x-app-layout>

    @include('dashboard.partials.app')

        @include('dashboard.partials.header')

        @include('dashboard.partials.cards')

        @include('dashboard.partials.approver-charts')

        @include('dashboard.partials.services')

    </div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const idioma = localStorage.getItem('ticketus_lang') || 'es';

    const estados = @json($approvalsByStatus);

    //=========================================
    // Traducciones
    //=========================================

    const traduccionesEstado = {

        pending: idioma === 'es'
            ? 'Pendiente'
            : 'Pending',

        approved: idioma === 'es'
            ? 'Aprobado'
            : 'Approved',

        rejected: idioma === 'es'
            ? 'Rechazado'
            : 'Rejected'

    };

    const coloresEstado = {

        pending:'#f59e0b',

        approved:'#22c55e',

        rejected:'#ef4444'

    };

    //=========================================
    // PIE CHART
    //=========================================

    const ctxPie =
        document.getElementById('approvalStatusChart');

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
                        s => coloresEstado[s.status] || '#64748b'
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

});

</script>

</x-app-layout>