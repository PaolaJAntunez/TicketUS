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

    @isset($totalCount)
        <div style="background:#1e293b; border:1px solid #334155; border-radius:8px; padding:24px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <h3 x-text="textosDashboard[idioma].cardTotalTitulo" style="color:#cbd5e1;"></h3>
                <span style="font-size:24px; color:#e2e8f0; font-weight:bold;">{{ $totalCount }}</span>
            </div>
            <p style="color:#64748b; margin:0;">
                <span x-text="textosDashboard[idioma].cardTotalDesc"></span> {{ $from->format('d/m/Y') }}–{{ $to->format('d/m/Y') }}
            </p>
        </div>
    @endisset

    @isset($overdueCount)
        <div style="background:#1e293b; border:1px solid #7f1d1d; border-radius:8px; padding:24px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <h3 x-text="textosDashboard[idioma].cardVencidosTitulo" style="color:#cbd5e1;"></h3>
                <span style="font-size:24px; color:#ef4444; font-weight:bold;">{{ $overdueCount }}</span>
            </div>
            <p x-text="textosDashboard[idioma].cardVencidosDesc" style="color:#64748b;"></p>
        </div>
    @endisset

    @isset($avgResolutionHours)
        <div style="background:#1e293b; border:1px solid #334155; border-radius:8px; padding:24px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <h3 x-text="textosDashboard[idioma].cardPromedioTitulo" style="color:#cbd5e1;"></h3>
                <span style="font-size:24px; color:#38bdf8; font-weight:bold;">
                    {{ $avgResolutionHours ? number_format($avgResolutionHours, 1) : '—' }}{{ $avgResolutionHours ? 'h' : '' }}
                </span>
            </div>
            <p x-text="textosDashboard[idioma].cardPromedioDesc" style="color:#64748b;"></p>
        </div>
    @endisset

    @isset($pendingApprovalsSystemCount)
        <div style="background:#1e293b; border:1px solid #334155; border-radius:8px; padding:24px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <h3 x-text="textosDashboard[idioma].cardAprobSistemaTitulo" style="color:#cbd5e1;"></h3>
                <span style="font-size:24px; color:#a78bfa; font-weight:bold;">{{ $pendingApprovalsSystemCount }}</span>
            </div>
            <p x-text="textosDashboard[idioma].cardAprobSistemaDesc" style="color:#64748b;"></p>
        </div>
    @endisset

    @isset($reopenedCount)
        <div style="background:#1e293b; border:1px solid #334155; border-radius:8px; padding:24px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
                <h3 x-text="textosDashboard[idioma].cardReabiertosTitulo" style="color:#cbd5e1;"></h3>
                <span style="font-size:24px; color:#fb923c; font-weight:bold;">{{ $reopenedCount }}</span>
            </div>
            <p x-text="textosDashboard[idioma].cardReabiertosDesc" style="color:#64748b;"></p>
        </div>
    @endisset

    @isset($thisWeekCount)
        <div style="background:#1e293b; border:1px solid #334155; border-radius:8px; padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h3 x-text="textosDashboard[idioma].cardTendenciaTitulo" style="color:#cbd5e1;"></h3>
                <span style="font-size:22px; font-weight:bold; color: {{ $weekTrendDirection === 'up' ? '#22c55e' : '#ef4444' }};">
                    {{ $weekTrendDirection === 'up' ? '↑' : '↓' }} {{ number_format(abs($weekTrendPercent), 1) }}%
                </span>
            </div>
            <p style="color:#64748b; margin:0;">
                {{ $thisWeekCount }} <span x-text="textosDashboard[idioma].cardTendenciaVs"></span> {{ $lastWeekCount }}
            </p>
        </div>
    @endisset

</div>