<!-- TABLAS ADMIN: top agentes + sin asignar -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(min(420px,100%),1fr)); gap:30px; margin-top:30px;">

    <!-- Top 5 agentes -->
    <div style="background:#1e293b; border:1px solid #334155; border-radius:10px; padding:25px;">
        <h3 x-text="textosDashboard[idioma].topAgentesTitulo" style="color:#ffffff; margin-bottom:20px; font-size:18px;"></h3>

        @if($topAgentsThisMonth->isEmpty())
            <p x-text="textosDashboard[idioma].sinDatos" style="color:#94a3b8; margin:0;"></p>
        @else
            @foreach($topAgentsThisMonth as $row)
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:10px 4px; border-bottom:1px solid #334155;">
                    <span style="font-weight:600; color:#ffffff; min-width:0;">{{ $row->agent?->name ?? '—' }}</span>
                    <span style="background:#166534; color:#dcfce7; padding:3px 10px; border-radius:9999px; font-size:12px; font-weight:700; white-space:nowrap; flex-shrink:0;">
                        {{ $row->total }}
                    </span>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Sin asignar +24h -->
    <div style="background:#1e293b; border:1px solid #334155; border-radius:10px; padding:25px;">
        <h3 x-text="textosDashboard[idioma].sinAsignarTitulo" style="color:#ffffff; margin-bottom:20px; font-size:18px;"></h3>

        @if($unassignedStaleTickets->isEmpty())
            <p x-text="textosDashboard[idioma].sinDatos" style="color:#94a3b8; margin:0;"></p>
        @else
            @foreach($unassignedStaleTickets as $ticket)
                @php
                    // Carbon 3 diffInHours() devuelve float (horas y fracción), no un
                    // entero truncado como en Carbon 2: hay que redondear para mostrar.
                    $hoursOld = (int) round($ticket->created_at->diffInHours(now()));
                    $severe = $hoursOld >= 48;
                @endphp
                <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:10px 4px; border-bottom:1px solid #334155; background-color: {{ $severe ? 'rgba(239,68,68,0.12)' : 'rgba(245,158,11,0.12)' }};">
                    <div style="min-width:0;">
                        <a href="{{ route('tickets.show', $ticket) }}" style="color:#ffffff; text-decoration:none; font-weight:600;">
                            #{{ $ticket->id }} {{ Str::limit($ticket->title, 30) }}
                        </a>
                        <div style="color:#94a3b8; font-size:12px;">{{ $ticket->category->name ?? '—' }}</div>
                    </div>
                    <span style="background: {{ $severe ? '#7f1d1d' : '#78350f' }}; color: {{ $severe ? '#fecaca' : '#fde68a' }}; padding:3px 10px; border-radius:9999px; font-size:12px; font-weight:700; white-space:nowrap; flex-shrink:0;">
                        {{ $hoursOld }}h
                    </span>
                </div>
            @endforeach
        @endif
    </div>

</div>
