<!-- DASHBOARD ADMINISTRADOR -->

<div style="margin:45px 0;">

    <h2 style="
        color:#ffffff;
        font-size:24px;
        font-weight:700;
        margin-bottom:25px;">

        Dashboard Administrativo

    </h2>

    <!-- PRIMERA FILA -->

    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(450px,1fr));
        gap:30px;
        margin-bottom:30px;">

        <!-- Tickets por Agente -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:#ffffff;
                margin-bottom:20px;
                font-size:18px;">

                Tickets por Agente

            </h3>

            <div style="height:340px;">
                <canvas id="ticketsByAgentChart"></canvas>
            </div>

        </div>

        <!-- Estado Global -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:#ffffff;
                margin-bottom:20px;
                font-size:18px;">

                Estado Global de Tickets

            </h3>

            <div style="height:340px;">
                <canvas id="ticketsByStatusChart"></canvas>
            </div>

        </div>

    </div>

    <!-- SEGUNDA FILA -->

    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(450px,1fr));
        gap:30px;">

        <!-- Tickets por Categoría -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:#ffffff;
                margin-bottom:20px;
                font-size:18px;">

                Tickets por Categoría

            </h3>

            <div style="height:340px;">
                <canvas id="ticketsByCategoryChart"></canvas>
            </div>

        </div>

        <!-- Tickets por Mes -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:#ffffff;
                margin-bottom:20px;
                font-size:18px;">

                Tickets creados por Mes

            </h3>

            <div style="height:340px;">
                <canvas id="ticketsByMonthChart"></canvas>
            </div>

        </div>

    </div>

    <!-- TABLA -->

    <div style="
        margin-top:35px;
        background:#1e293b;
        border:1px solid #334155;
        border-radius:10px;
        padding:25px;">

        <h3 style="
            color:#ffffff;
            margin-bottom:20px;
            font-size:18px;">

            Últimos Tickets Registrados

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

                        <th style="padding:12px;text-align:left;">Usuario</th>

                        <th style="padding:12px;text-align:left;">Estado</th>

                        <th style="padding:12px;text-align:left;">Categoría</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($recentTickets as $ticket)

                        <tr style="border-bottom:1px solid #334155;">

                            <td style="padding:12px;">
                                {{ $ticket->id }}
                            </td>

                            <td style="padding:12px;">
                                {{ $ticket->title }}
                            </td>

                            <td style="padding:12px;">
                                {{ $ticket->user->name }}
                            </td>

                            <td style="padding:12px;">
                                {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
                            </td>

                            <td style="padding:12px;">
                                {{ $ticket->category->name ?? '-' }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                style="padding:20px;text-align:center;color:#94a3b8;">

                                No hay tickets registrados.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>