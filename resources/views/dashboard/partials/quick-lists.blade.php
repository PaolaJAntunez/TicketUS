<!-- LISTAS RÁPIDAS: últimos creados / últimos resueltos (todos los roles) -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(420px,1fr)); gap:30px; margin-top:30px;">

    <div style="background:#1e293b; border:1px solid #334155; border-radius:10px; padding:25px;">
        <h3 x-text="textosDashboard[idioma].ultimosCreadosTitulo" style="color:#ffffff; margin-bottom:20px; font-size:18px;"></h3>

        @if($recentCreated->isEmpty())
            <p x-text="textosDashboard[idioma].sinDatos" style="color:#94a3b8; margin:0;"></p>
        @else
            @foreach($recentCreated as $ticket)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid #334155;">
                    <div>
                        <a href="{{ route('tickets.show', $ticket) }}" style="color:#ffffff; text-decoration:none; font-size:14px; font-weight:600;">
                            #{{ $ticket->id }} {{ Str::limit($ticket->title, 32) }}
                        </a>
                        <div style="color:#94a3b8; font-size:12px;">{{ $ticket->category->name ?? '—' }} &middot; {{ $ticket->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div style="background:#1e293b; border:1px solid #334155; border-radius:10px; padding:25px;">
        <h3 x-text="textosDashboard[idioma].ultimosResueltosTitulo" style="color:#ffffff; margin-bottom:20px; font-size:18px;"></h3>

        @if($recentResolved->isEmpty())
            <p x-text="textosDashboard[idioma].sinDatos" style="color:#94a3b8; margin:0;"></p>
        @else
            @foreach($recentResolved as $ticket)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid #334155;">
                    <div>
                        <a href="{{ route('tickets.show', $ticket) }}" style="color:#ffffff; text-decoration:none; font-size:14px; font-weight:600;">
                            #{{ $ticket->id }} {{ Str::limit($ticket->title, 32) }}
                        </a>
                        <div style="color:#94a3b8; font-size:12px;">{{ $ticket->category->name ?? '—' }} &middot; {{ $ticket->resolved_at?->format('d/m/Y H:i') ?? '—' }}</div>
                    </div>
                    <span style="background:#166534; color:#dcfce7; padding:3px 10px; border-radius:9999px; font-size:11px; font-weight:700; white-space:nowrap;">
                        {{ ucfirst($ticket->status) }}
                    </span>
                </div>
            @endforeach
        @endif
    </div>

</div>
