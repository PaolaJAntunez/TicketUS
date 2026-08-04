<!-- PANEL DEL AGENTE -->

<style>
    @media (max-width: 767px) {
        .ticketus-agent-charts-grid { grid-template-columns: 1fr !important; }
    }
</style>

<div style="margin:45px 0;">

    <h2 style="
        color:#1e293b;
        font-size:24px;
        font-weight:700;
        margin-bottom:25px;">

        Mi Panel de Trabajo

    </h2>

    <div class="ticketus-agent-charts-grid" style="
        display:grid;
        grid-template-columns:2fr 1fr;
        gap:30px;
        margin-bottom:30px;">

        <!-- PRIORIDADES -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:white;
                margin-bottom:20px;">

                Mis Tickets por Prioridad

            </h3>

            <div style="height:330px;">
                <canvas id="ticketsByPriorityChart"></canvas>
            </div>

        </div>

        <!-- RESUMEN -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:white;
                margin-bottom:20px;">

                Mi Estado

            </h3>

            <div style="height:330px;">
                <canvas id="ticketsStatusChart"></canvas>
            </div>

        </div>

    </div>

    <!-- TICKETS -->

    <div style="
        background:#1e293b;
        border:1px solid #334155;
        border-radius:10px;
        padding:25px;">

        <h3 style="
            color:white;
            margin-bottom:20px;">

            Tickets Asignados Recientemente

        </h3>

        <div style="overflow-x:auto;">

            <table style="
                width:100%;
                border-collapse:collapse;
                color:white;">

                <thead>

                    <tr style="border-bottom:1px solid #334155;">

                        <th style="padding:12px;text-align:left;">#</th>

                        <th style="padding:12px;text-align:left;">Título</th>

                        <th style="padding:12px;text-align:left;">Prioridad</th>

                        <th style="padding:12px;text-align:left;">Estado</th>

                        <th style="padding:12px;text-align:left;">Usuario</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($assignedTickets->take(8) as $ticket)

                        <tr style="border-bottom:1px solid #334155;">

                            <td style="padding:12px;">
                                {{ $ticket->id }}
                            </td>

                            <td style="padding:12px;">
                                {{ $ticket->title }}
                            </td>

                            <td style="padding:12px;">
                                {{ ucfirst($ticket->priority) }}
                            </td>

                            <td style="padding:12px;">
                                {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                            </td>

                            <td style="padding:12px;">
                                {{ $ticket->user->name }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                style="
                                padding:20px;
                                text-align:center;
                                color:#94a3b8;">

                                No tienes tickets asignados.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>