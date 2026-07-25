<!-- TABLAS ADMIN: top agentes + sin asignar -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(420px,1fr)); gap:30px; margin-top:30px;">

    <!-- Top 5 agentes -->
    <div style="background:#1e293b; border:1px solid #334155; border-radius:10px; padding:25px;">
        <h3 x-text="textosDashboard[idioma].topAgentesTitulo" style="color:#ffffff; margin-bottom:20px; font-size:18px;"></h3>

        @if($topAgentsThisMonth->isEmpty())
            <p x-text="textosDashboard[idioma].sinDatos" style="color:#94a3b8; margin:0;"></p>
        @else
            <table style="width:100%; border-collapse:collapse; color:white;">
                <tbody>
                    @foreach($topAgentsThisMonth as $row)
                        <tr style="border-bottom:1px solid #334155;">
                            <td style="padding:10px 4px; font-weight:600;">{{ $row->agent?->name ?? '—' }}</td>
                            <td style="padding:10px 4px; text-align:right;">
                                <span style="background:#166534; color:#dcfce7; padding:3px 10px; border-radius:9999px; font-size:12px; font-weight:700;">
                                    {{ $row->total }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Sin asignar +24h -->
    <div style="background:#1e293b; border:1px solid #334155; border-radius:10px; padding:25px;">
        <h3 x-text="textosDashboard[idioma].sinAsignarTitulo" style="color:#ffffff; margin-bottom:20px; font-size:18px;"></h3>

        @if($unassignedStaleTickets->isEmpty())
            <p x-text="textosDashboard[idioma].sinDatos" style="color:#94a3b8; margin:0;"></p>
        @else
            <table style="width:100%; border-collapse:collapse; color:white;">
                <tbody>
                    @foreach($unassignedStaleTickets as $ticket)
                        @php
                            // Carbon 3 diffInHours() devuelve float (horas y fracción), no un
                            // entero truncado como en Carbon 2: hay que redondear para mostrar.
                            $hoursOld = (int) round($ticket->created_at->diffInHours(now()));
                            $severe = $hoursOld >= 48;
                        @endphp
                        <tr style="border-bottom:1px solid #334155; background-color: {{ $severe ? 'rgba(239,68,68,0.12)' : 'rgba(245,158,11,0.12)' }};">
                            <td style="padding:10px 4px;">
                                <a href="{{ route('tickets.show', $ticket) }}" style="color:#ffffff; text-decoration:none; font-weight:600;">
                                    #{{ $ticket->id }} {{ Str::limit($ticket->title, 30) }}
                                </a>
                                <div style="color:#94a3b8; font-size:12px;">{{ $ticket->category->name ?? '—' }}</div>
                            </td>
                            <td style="padding:10px 4px; text-align:right;">
                                <span style="background: {{ $severe ? '#7f1d1d' : '#78350f' }}; color: {{ $severe ? '#fecaca' : '#fde68a' }}; padding:3px 10px; border-radius:9999px; font-size:12px; font-weight:700; white-space:nowrap;">
                                    {{ $hoursOld }}h
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</div>
