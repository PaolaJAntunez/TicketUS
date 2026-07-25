<!-- DASHBOARD DEL USUARIO -->

<div style="margin:45px 0;">

    <h2 style="
        color:#1e293b;
        font-size:24px;
        font-weight:700;
        margin-bottom:25px;">

        Mis Solicitudes

    </h2>

    <!-- GRÁFICOS -->

    <div style="
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(450px,1fr));
        gap:30px;
        margin-bottom:35px;">

        <!-- Estado -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:#ffffff;
                margin-bottom:20px;">

                Estado de Mis Tickets

            </h3>

            <div style="height:330px;">

                <canvas id="myTicketsStatusChart"></canvas>

            </div>

        </div>

        <!-- Mes -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:#ffffff;
                margin-bottom:20px;">

                Historial de Tickets

            </h3>

            <div style="height:330px;">

                <canvas id="myTicketsMonthChart"></canvas>

            </div>

        </div>

    </div>

    <!-- TABLA -->

    <div style="
        background:#1e293b;
        border:1px solid #334155;
        border-radius:10px;
        padding:25px;">

        <h3 style="
            color:#ffffff;
            margin-bottom:20px;">

            Mis Tickets Activos

        </h3>

        <div style="overflow-x:auto;">

            <table style="
                width:100%;
                border-collapse:collapse;
                color:white;">

                <thead>

                    <tr style="border-bottom:1px solid #334155;">

                        <th style="padding:12px;text-align:left;">
                            #
                        </th>

                        <th style="padding:12px;text-align:left;">
                            Título
                        </th>

                        <th style="padding:12px;text-align:left;">
                            Categoría
                        </th>

                        <th style="padding:12px;text-align:left;">
                            Estado
                        </th>

                        <th style="padding:12px;text-align:left;">
                            Creado
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($activeTickets as $ticket)

                        <tr style="border-bottom:1px solid #334155;">

                            <td style="padding:12px;">
                                #{{ $ticket->id }}
                            </td>

                            <td style="padding:12px;">
                                {{ $ticket->title }}
                            </td>

                            <td style="padding:12px;">
                                {{ $ticket->category->name ?? '-' }}
                            </td>

                            <td style="padding:12px;">

                                @php
                                    $estado = str_replace('_',' ',$ticket->status);
                                @endphp

                                {{ ucfirst($estado) }}

                            </td>

                            <td style="padding:12px;">
                                {{ $ticket->created_at->format('d/m/Y') }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                style="
                                padding:20px;
                                text-align:center;
                                color:#94a3b8;">

                                No tienes tickets activos.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>