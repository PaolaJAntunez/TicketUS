<x-app-layout>
    <div x-data="{
        idioma: localStorage.getItem('ticketus_lang') || 'es',

        // Diccionario de traducción para la sección de Tickets
        textosTickets: {
            es: {
                titulo: 'Gestión de Tickets',
                subtitulo: 'Historial y estado actual de tus reportes de soporte.',

                // Botones superiores
                btnCrear: '+ Crear Nuevo Ticket',
                btnExcel: '📊 Excel',
                btnPdf: '📄 PDF',

                // Filtros
                lblBuscar: 'Buscar',
                lblCategoria: 'Categoría',
                lblPrioridad: 'Prioridad',
                lblEstado: 'Estado',

                phBuscar: 'Título del ticket...',

                optTodas: 'Todas',
                optTodos: 'Todos',

                btnBuscar: 'Buscar',
                btnLimpiar: 'Limpiar',

                // Estados
                estadoAbierto: 'Abierto',
                estadoAsignado: 'Asignado',
                estadoPendiente: 'Pendiente de Aprobación',
                estadoProgreso: 'En Progreso',
                estadoEnEspera: 'En Espera',
                estadoResuelto: 'Resuelto',
                estadoCerrado: 'Cerrado',
                estadoCancelado: 'Cancelado',

                // Prioridades
                prioridadBaja: 'Baja',
                prioridadMedia: 'Media',
                prioridadAlta: 'Alta',
                prioridadUrgente: 'Urgente',

                sinTickets: 'No se encontraron tickets en el sistema.'
            },

            en: {
                titulo: 'Ticket Management',
                subtitulo: 'History and current status of your support reports.',

                btnCrear: '+ Create New Ticket',
                btnExcel: '📊 Excel',
                btnPdf: '📄 PDF',

                lblBuscar: 'Search',
                lblCategoria: 'Category',
                lblPrioridad: 'Priority',
                lblEstado: 'Status',

                phBuscar: 'Ticket title...',

                optTodas: 'All',
                optTodos: 'All',

                btnBuscar: 'Search',
                btnLimpiar: 'Clear',

                estadoAbierto: 'Open',
                estadoAsignado: 'Assigned',
                estadoPendiente: 'Pending Approval',
                estadoProgreso: 'In Progress',
                estadoEnEspera: 'On Hold',
                estadoResuelto: 'Resolved',
                estadoCerrado: 'Closed',
                estadoCancelado: 'Cancelled',

                prioridadBaja: 'Low',
                prioridadMedia: 'Medium',
                prioridadAlta: 'High',
                prioridadUrgente: 'Urgent',

                sinTickets: 'No tickets found in the system.'
            }
        }
    }" style="max-width:1600px; margin:40px auto; padding:0 20px;">

        @if(session('success'))
            <div style="margin-bottom: 16px; padding: 16px; background-color: #dcfce7; color: #166534; border-radius: 6px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Encabezado de la Sección --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 x-text="textosTickets[idioma].titulo" style="font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 6px;"></h1>
                <p class="text-muted-adaptive" x-text="textosTickets[idioma].subtitulo" style="font-size: 15px; margin: 0;"></p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                {{-- Toggle Tabla / Kanban --}}
                <div style="display:flex; border:1px solid #cbd5e1; border-radius:6px; overflow:hidden;">
                    <a href="{{ route('tickets.index', request()->query()) }}"
                       style="padding:10px 16px; font-size:13px; font-weight:600; text-decoration:none; color:#ffffff; background-color:#1e3a5f;">
                        <i class="ti ti-list"></i> Vista Tabla
                    </a>
                    <a href="{{ route('tickets.kanban', request()->query()) }}"
                       style="padding:10px 16px; font-size:13px; font-weight:600; text-decoration:none; color:#374151; background-color:#f1f5f9;">
                        <i class="ti ti-layout-kanban"></i> Vista Kanban
                    </a>
                </div>
                <a href="{{ route('tickets.create') }}"
                   x-text="textosTickets[idioma].btnCrear"
                   style="background-color: #2563eb; color: #ffffff; text-decoration:none; padding:12px 20px; border-radius:6px; font-size:14px; font-weight:600; display:inline-block;">
                </a>
                <a href="{{ route('reports.tickets.excel') }}"
                   x-text="textosTickets[idioma].btnExcel"
                   style="background-color: #16a34a; color: #ffffff; text-decoration:none; padding:12px 20px; border-radius:6px; font-size:14px; font-weight:600; display:inline-block;">
                </a>
                <a href="{{ route('reports.tickets.pdf') }}"
                   target="_blank"
                   x-text="textosTickets[idioma].btnPdf"
                   style="background-color: #dc2626; color: #ffffff; text-decoration:none; padding:12px 20px; border-radius:6px; font-size:14px; font-weight:600; display:inline-block;">
                </a>
            </div>
        </div>

        <x-ticket-filters :categories="$categories" :agents="$agents" />

        {{-- CSS propio de la tabla de monitoreo: reacciona a la clase "dark"
             que layouts.navigation ya aplica en <html> al alternar el tema.
             Se maneja aparte del hack global porque esta tabla tiene color
             de fondo propio por fila (zebra), y la heurística global no
             puede distinguirlo de forma segura. --}}
        <style>
            .ticket-row:nth-child(even) { background-color: #f8fafc; }
            .ticket-row:hover { background-color: #eff6ff; }

            html.dark .ticket-table-wrap { background-color: #1e293b !important; border-color: #334155 !important; }
            html.dark .ticket-th { background-color: #0f172a !important; color: #94a3b8 !important; border-bottom-color: #334155 !important; }
            html.dark .ticket-row { border-bottom-color: #334155 !important; }
            html.dark .ticket-row:nth-child(even) { background-color: #16213a !important; }
            html.dark .ticket-row:hover { background-color: #1e3a5f !important; }
            html.dark .ticket-td { color: #e2e8f0 !important; }
        </style>

        {{-- Tarjeta de total + encabezado de la tabla --}}
        <div style="display:flex; justify-content:flex-end; margin-bottom:12px;">
            <div style="background-color: #ffffff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.1); padding:12px 24px; display:flex; align-items:center; gap:12px;">
                <span style="font-size:13px; color: #64748b; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Total de tickets</span>
                <span style="background-color: #1e3a5f; color: #ffffff; font-size:16px; font-weight:700; padding:4px 14px; border-radius:9999px;">
                    {{ $tickets->count() }}
                </span>
            </div>
        </div>

        {{-- Tabla de monitoreo --}}
        <div class="ticket-table-wrap" style="width:100%;
            background-color: #ffffff;
            border:1px solid #e2e8f0;
            border-radius:8px;
            overflow:auto;
            -webkit-overflow-scrolling:touch;
            max-height:75vh;
            box-shadow:0 1px 3px rgba(0,0,0,.1);
            box-sizing:border-box;">
            <table style="width: 100%; min-width: 1280px; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr>
                        @foreach(['SITIO','DEPTO','ID','SOLICITANTE','ESTATUS','ASIGNADO A','CREADO','VENCE','RESTANTE','TÍTULO','DETALLE'] as $col)
                            <th class="ticket-th" style="position: sticky; top: 0; z-index: 1; background-color: #f8fafc; padding: 12px 16px; color: #64748b; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 2px solid #e2e8f0; white-space: nowrap; {{ $col === 'DETALLE' ? 'text-align:center;' : '' }}">
                                {{ $col }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>

@if($tickets->count())

    @foreach($tickets as $ticket)
        @php
            $site = $ticket->user->department ?? '—';

            $remainingHours = $ticket->due_date
                ? ($ticket->due_date->timestamp - now()->timestamp) / 3600
                : null;

            if ($remainingHours === null) {
                $remainingBg = '#f1f5f9'; $remainingText = '#64748b';
            } elseif ($remainingHours < 0) {
                $remainingBg = '#fee2e2'; $remainingText = '#991b1b';
            } elseif ($remainingHours < 24) {
                $remainingBg = '#fef9c3'; $remainingText = '#854d0e';
            } else {
                $remainingBg = '#dcfce7'; $remainingText = '#166534';
            }
        @endphp

        <tr class="ticket-row" onclick="window.location='{{ route('tickets.show', $ticket) }}'" style="cursor:pointer; border-bottom:1px solid #e2e8f0;">

            <td class="ticket-td" style="padding:12px 16px; color: #374151; white-space:nowrap;">{{ $site }}</td>

            <td class="ticket-td" style="padding:12px 16px; color: #374151; white-space:nowrap;">{{ $site }}</td>

            <td class="ticket-td" style="padding:12px 16px; color: #1e293b; font-family:monospace; font-weight:600;">#{{ $ticket->id }}</td>

            <td class="ticket-td" style="padding:12px 16px; color: #1e293b; white-space:nowrap;">{{ $ticket->user->name }}</td>

            <td style="padding:12px 16px;" onclick="event.stopPropagation();">
                <x-ticket-status-menu :ticket="$ticket" :approval-candidates="$approvalCandidates" />
            </td>

            <td class="ticket-td" style="padding:12px 16px; color: #374151; white-space:nowrap;">{{ $ticket->agent?->name ?? 'Sin asignar' }}</td>

            <td class="ticket-td" style="padding:12px 16px; color: #64748b; white-space:nowrap;">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>

            <td class="ticket-td" style="padding:12px 16px; color: #64748b; white-space:nowrap;">{{ $ticket->due_date?->format('d/m/Y H:i') ?? '—' }}</td>

            <td style="padding:12px 16px;">
                <span style="background-color: {{ $remainingBg }}; color: {{ $remainingText }}; padding:4px 10px; border-radius:9999px; font-size:11px; font-weight:700; white-space:nowrap;">
                    {{ $ticket->remaining ?? '—' }}
                </span>
            </td>

            <td class="ticket-td" style="padding:12px 16px; color: #1e293b; font-weight:500; max-width:260px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                {{ $ticket->title }}
            </td>

            <td style="padding:12px 16px; text-align:center;">
                <a href="{{ route('tickets.show', $ticket) }}"
                   onclick="event.stopPropagation();"
                   title="Ver detalle"
                   style="color: #2563eb; display:inline-flex; align-items:center; justify-content:center; text-decoration:none; font-size:18px;">
                    <i class="ti ti-eye"></i>
                </a>
            </td>

        </tr>

    @endforeach

@else

    <tr>
        <td colspan="11" x-text="textosTickets[idioma].sinTickets" style="padding:25px; text-align:center; color: #94a3b8;"></td>
    </tr>

@endif

</tbody>
            </table>
        </div>

    </div>
</x-app-layout>
