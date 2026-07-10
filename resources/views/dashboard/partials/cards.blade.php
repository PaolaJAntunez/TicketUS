<!-- TARJETAS -->

<div style="display:grid;
            grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
            gap:24px;
            margin-bottom:32px;">

    <div style="background:#1e293b;
                border:1px solid #334155;
                border-radius:8px;
                padding:24px;">

        <div style="display:flex;
                    justify-content:space-between;
                    margin-bottom:12px;">

            <h3
                x-text="textosDashboard[idioma].tarjeta1Titulo"
                style="color:#cbd5e1;">
            </h3>

            <span style="font-size:24px;
                         color:#ef4444;
                         font-weight:bold;">

                {{ $openCount ?? $assignedCount ?? 0 }}

            </span>

        </div>

        <p
            x-text="textosDashboard[idioma].tarjeta1Desc"
            style="color:#64748b;">
        </p>

    </div>

    <div style="background:#1e293b;
                border:1px solid #334155;
                border-radius:8px;
                padding:24px;">

        <div style="display:flex;
                    justify-content:space-between;
                    margin-bottom:12px;">

            <h3
                x-text="textosDashboard[idioma].tarjeta2Titulo"
                style="color:#cbd5e1;">
            </h3>

            <span style="font-size:24px;
                         color:#3b82f6;
                         font-weight:bold;">

                {{ $inProgressCount ?? 0 }}

            </span>

        </div>

        <p
            x-text="textosDashboard[idioma].tarjeta2Desc"
            style="color:#64748b;">
        </p>

    </div>

    <div style="background:#1e293b;
                border:1px solid #334155;
                border-radius:8px;
                padding:24px;">

        <div style="display:flex;
                    justify-content:space-between;
                    margin-bottom:12px;">

            <h3
                x-text="textosDashboard[idioma].tarjeta3Titulo"
                style="color:#cbd5e1;">
            </h3>

            <span style="font-size:24px;
                         color:#10b981;
                         font-weight:bold;">

                {{ $resolvedCount ?? $resolvedTodayCount ?? 0 }}

            </span>

        </div>

        <p
            x-text="textosDashboard[idioma].tarjeta3Desc"
            style="color:#64748b;">
        </p>

    </div>

</div>