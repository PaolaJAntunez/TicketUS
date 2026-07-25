<!-- DASHBOARD ADMINISTRADOR -->

<div style="margin:45px 0;">

    <h2 style="
        color:#1e293b;
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
    {{-- La tabla "Últimos Tickets Registrados" que vivía acá se consolidó en
         dashboard.partials.quick-lists (Últimos Creados / Últimos Resueltos),
         compartida entre los tres roles en vez de duplicada solo para admin. --}}

</div>