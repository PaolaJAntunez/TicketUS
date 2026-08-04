<!-- PANEL DE APROBACIONES -->

<style>
    @media (max-width: 767px) {
        .ticketus-approver-charts-grid { grid-template-columns: 1fr !important; }
    }
</style>

<div style="margin:45px 0;">

    <h2 style="
        color:#1e293b;
        font-size:24px;
        font-weight:700;
        margin-bottom:25px;">

        Panel de Aprobaciones

    </h2>

    <!-- PRIMERA FILA -->

    <div class="ticketus-approver-charts-grid" style="
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:30px;
        margin-bottom:35px;">

        <!-- Pie Chart -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:#ffffff;
                margin-bottom:20px;">

                Estado de las Aprobaciones

            </h3>

            <div style="height:340px;">

                <canvas id="approvalStatusChart"></canvas>

            </div>

        </div>

        <!-- Pendientes -->

        <div style="
            background:#1e293b;
            border:1px solid #334155;
            border-radius:10px;
            padding:25px;">

            <h3 style="
                color:#ffffff;
                margin-bottom:20px;">

                Solicitudes Pendientes

            </h3>

            <div style="
                max-height:340px;
                overflow-y:auto;">

                @forelse($pendingApprovals as $approval)

                    <div style="
                        padding:15px;
                        border-bottom:1px solid #334155;">

                        <div style="
                            color:#ffffff;
                            font-weight:600;">

                            {{ $approval->ticket->title ?? 'Sin título' }}

                        </div>

                        <div style="
                            color:#94a3b8;
                            font-size:13px;
                            margin-top:5px;">

                            {{ $approval->approvalLevel->name ?? 'Nivel de aprobación' }}

                        </div>

                    </div>

                @empty

                    <div style="
                        text-align:center;
                        color:#94a3b8;
                        padding:40px;">

                        No existen solicitudes pendientes.

                    </div>

                @endforelse

            </div>

        </div>

    </div>

    <!-- HISTORIAL -->

    <div style="
        background:#1e293b;
        border:1px solid #334155;
        border-radius:10px;
        padding:25px;">

        <h3 style="
            color:#ffffff;
            margin-bottom:20px;">

            Historial de Aprobaciones

        </h3>

        <div style="overflow-x:auto;">

            <table style="
                width:100%;
                border-collapse:collapse;
                color:white;">

                <thead>

                    <tr style="border-bottom:1px solid #334155;">

                        <th style="padding:12px;text-align:left;">
                            Ticket
                        </th>

                        <th style="padding:12px;text-align:left;">
                            Nivel
                        </th>

                        <th style="padding:12px;text-align:left;">
                            Estado
                        </th>

                        <th style="padding:12px;text-align:left;">
                            Fecha
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($approvalHistory as $approval)

                        <tr style="border-bottom:1px solid #334155;">

                            <td style="padding:12px;">

                                {{ $approval->ticket->title ?? '-' }}

                            </td>

                            <td style="padding:12px;">

                                {{ $approval->approvalLevel->name ?? '-' }}

                            </td>

                            <td style="padding:12px;">

                                {{ ucfirst($approval->status) }}

                            </td>

                            <td style="padding:12px;">

                                {{ optional($approval->approved_at)->format('d/m/Y H:i') ?? '-' }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4"
                                style="
                                padding:25px;
                                text-align:center;
                                color:#94a3b8;">

                                No hay historial disponible.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>