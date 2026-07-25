<!-- ACCIONABLE PARA EL AGENTE: mis asignados (con vencidos) + próximos a vencer -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:24px; margin-bottom:32px;">

    <div style="background:#1e293b; border:1px solid {{ $assignedOverdueCount > 0 ? '#7f1d1d' : '#334155' }}; border-radius:8px; padding:24px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:12px;">
            <h3 x-text="textosDashboard[idioma].misAsignadosTitulo" style="color:#cbd5e1;"></h3>
            <span style="font-size:24px; color:#38bdf8; font-weight:bold;">{{ $assignedTickets->count() }}</span>
        </div>
        @if($assignedOverdueCount > 0)
            <p style="color:#f87171; margin:0; font-weight:600;">
                {{ $assignedOverdueCount }} <span x-text="textosDashboard[idioma].misAsignadosVencidos"></span>
            </p>
        @else
            <p style="color:#64748b; margin:0;">—</p>
        @endif
    </div>

    <div style="background:#1e293b; border:1px solid #334155; border-radius:8px; padding:24px; grid-column: span 2;">
        <h3 x-text="textosDashboard[idioma].proximosVencerTitulo" style="color:#cbd5e1; margin-bottom:12px;"></h3>

        @if($dueSoon->isEmpty())
            <p x-text="textosDashboard[idioma].sinDatos" style="color:#64748b; margin:0;"></p>
        @else
            <div>
                @foreach($dueSoon as $ticket)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #334155;">
                        <a href="{{ route('tickets.show', $ticket) }}" style="color:#ffffff; text-decoration:none; font-size:14px;">
                            #{{ $ticket->id }} {{ Str::limit($ticket->title, 35) }}
                        </a>
                        <span style="background:#78350f; color:#fde68a; padding:3px 10px; border-radius:9999px; font-size:11px; font-weight:700; white-space:nowrap;">
                            {{ $ticket->due_date->format('d/m H:i') }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
