<!-- TABLAS ADICIONALES: resolución promedio por categoría + top 5 categorías -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(min(420px,100%),1fr)); gap:30px; margin-top:30px;">

    <!-- Resolución promedio por categoría -->
    <div style="background:#1e293b; border:1px solid #334155; border-radius:10px; padding:25px;">
        <h3 x-text="textosDashboard[idioma].resolucionCategoriaTitulo" style="color:#ffffff; margin-bottom:20px; font-size:18px;"></h3>

        @if($avgResolutionByCategory->isEmpty())
            <p x-text="textosDashboard[idioma].sinDatos" style="color:#94a3b8; margin:0;"></p>
        @else
            <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; color:white;">
                <thead>
                    <tr style="border-bottom:1px solid #334155;">
                        <th style="text-align:left; padding:8px 4px; color:#94a3b8; font-size:11px; text-transform:uppercase; white-space:nowrap;" x-text="textosDashboard[idioma].colCategoria"></th>
                        <th style="text-align:right; padding:8px 4px; color:#94a3b8; font-size:11px; text-transform:uppercase; white-space:nowrap;" x-text="textosDashboard[idioma].colPromedio"></th>
                        <th style="text-align:right; padding:8px 4px; color:#94a3b8; font-size:11px; text-transform:uppercase; white-space:nowrap;" x-text="textosDashboard[idioma].colResueltos"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($avgResolutionByCategory as $row)
                        <tr style="border-bottom:1px solid #334155;">
                            <td style="padding:10px 4px; font-weight:600;">{{ $row->name }}</td>
                            <td style="padding:10px 4px; text-align:right;">{{ number_format($row->avg_hours, 1) }}h</td>
                            <td style="padding:10px 4px; text-align:right;">{{ $row->resolved_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

    <!-- Top 5 categorías con más incidentes -->
    <div style="background:#1e293b; border:1px solid #334155; border-radius:10px; padding:25px;">
        <h3 x-text="textosDashboard[idioma].topCategoriasTitulo" style="color:#ffffff; margin-bottom:20px; font-size:18px;"></h3>

        @if($topCategories->isEmpty())
            <p x-text="textosDashboard[idioma].sinDatos" style="color:#94a3b8; margin:0;"></p>
        @else
            <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; color:white;">
                <tbody>
                    @foreach($topCategories as $i => $cat)
                        <tr style="border-bottom:1px solid #334155;">
                            <td style="padding:10px 4px; font-weight:600;">{{ $i + 1 }}. {{ $cat->name }}</td>
                            <td style="padding:10px 4px; text-align:right;">
                                <span style="background:#4c1d95; color:#e9d5ff; padding:3px 10px; border-radius:9999px; font-size:12px; font-weight:700;">
                                    {{ $cat->tickets_count }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>

</div>
