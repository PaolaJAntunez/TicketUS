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
                estadoPendiente: 'Pendiente',
                estadoProgreso: 'En Progreso',
                estadoResuelto: 'Resuelto',
                estadoCerrado: 'Cerrado',

                // Prioridades
                prioridadBaja: 'Baja',
                prioridadMedia: 'Media',
                prioridadAlta: 'Alta',
                prioridadUrgente: 'Urgente',

                // Encabezados de tabla
                thId: 'ID',
                thAsunto: 'Asunto',
                thCategoria: 'Categoría',
                thEstado: 'Estado',
                thPrioridad: 'Prioridad',
                thAcciones: 'Acciones',

                // Badges
                badgeAbierto: 'Abierto',
                badgeProgreso: 'En Progreso',
                badgeResuelto: 'Resuelto',

                // Acciones
                btnVer: 'Ver Detalle',

                // Mensajes
                sinTickets: 'No se encontraron tickets en el sistema.'
            },

            en: {
                titulo: 'Ticket Management',
                subtitulo: 'History and current status of your support reports.',

                // Top buttons
                btnCrear: '+ Create New Ticket',
                btnExcel: '📊 Excel',
                btnPdf: '📄 PDF',

                // Filters
                lblBuscar: 'Search',
                lblCategoria: 'Category',
                lblPrioridad: 'Priority',
                lblEstado: 'Status',

                phBuscar: 'Ticket title...',

                optTodas: 'All',
                optTodos: 'All',

                btnBuscar: 'Search',
                btnLimpiar: 'Clear',

                // Status
                estadoAbierto: 'Open',
                estadoAsignado: 'Assigned',
                estadoPendiente: 'Pending Approval',
                estadoProgreso: 'In Progress',
                estadoResuelto: 'Resolved',
                estadoCerrado: 'Closed',

                // Priorities
                prioridadBaja: 'Low',
                prioridadMedia: 'Medium',
                prioridadAlta: 'High',
                prioridadUrgente: 'Urgent',

                // Table headers
                thId: 'ID',
                thAsunto: 'Subject',
                thCategoria: 'Category',
                thEstado: 'Status',
                thPrioridad: 'Priority',
                thAcciones: 'Actions',

                // Badges
                badgeAbierto: 'Open',
                badgeProgreso: 'In Progress',
                badgeResuelto: 'Resolved',

                // Actions
                btnVer: 'View Details',

                // Messages
                sinTickets: 'No tickets found in the system.'
            }
        }
    }" style="max-width:1200px; margin:40px auto; padding:0 20px;">

        <!-- Encabezado de la Sección -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 x-text="textosTickets[idioma].titulo" style="font-size: 28px; font-weight: 700; color: #ffffff; margin-bottom: 6px;"></h1>
                <p x-text="textosTickets[idioma].subtitulo" style="font-size: 15px; color: #94a3b8; margin: 0;"></p>
            </div>
            <!-- Botón de crear ticket dinámico  -->
            <div style="display:flex; gap:10px; flex-wrap:wrap;">

    <a href="{{ route('tickets.create') }}"
       x-text="textosTickets[idioma].btnCrear"
       style="background-color:#2563eb; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:6px; font-size:14px; font-weight:600; display:inline-block; transition:background-color .2s;">
    </a>

    <a href="{{ route('reports.tickets.excel') }}"
       x-text="textosTickets[idioma].btnExcel"
       style="background-color:#16a34a; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:6px; font-size:14px; font-weight:600; display:inline-block; transition:background-color .2s;">
    </a>

    <a href="{{ route('reports.tickets.pdf') }}"
       target="_blank"
       x-text="textosTickets[idioma].btnPdf"
       style="background-color:#dc2626; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:6px; font-size:14px; font-weight:600; display:inline-block; transition:background-color .2s;">
    </a>
</div>

        <x-ticket-filters :categories="$categories" />

        <!-- Tabla de Tickets con soporte multi-idioma -->
        <div style="width:100%;
            background-color:#1e293b;
            border:1px solid #334155;
            border-radius:8px;
            overflow:hidden;
            box-shadow:0 4px 6px rgba(0,0,0,.15);
            box-sizing:border-box;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="background-color: #0f172a; border-bottom: 1px solid #334155;">
                        <th x-text="textosTickets[idioma].thId" style="padding: 16px; color: #94a3b8; font-weight: 600; width: 80px;"></th>
                        <th x-text="textosTickets[idioma].thAsunto" style="padding: 16px; color: #94a3b8; font-weight: 600;"></th>
                        <th x-text="textosTickets[idioma].thCategoria" style="padding: 16px; color: #94a3b8; font-weight: 600;"></th>
                        <th x-text="textosTickets[idioma].thEstado" style="padding: 16px; color: #94a3b8; font-weight: 600;"></th>
                        <th x-text="textosTickets[idioma].thPrioridad" style="padding: 16px; color: #94a3b8; font-weight: 600;"></th>
                        <th x-text="textosTickets[idioma].thAcciones" style="padding: 16px; color: #94a3b8; font-weight: 600; text-align: center; width: 150px;"></th>
                    </tr>
                </thead>
                <tbody>

@if($tickets->count())

    @foreach($tickets as $ticket)

    <tr style="border-bottom:1px solid #334155;">

        <td style="padding:16px;color:#f1f5f9;font-family:monospace;">
            #{{ $ticket->id }}
        </td>

        <td style="padding:16px;color:#f1f5f9;font-weight:500;">
            {{ $ticket->title }}
        </td>

        <td style="padding:16px;color:#cbd5e1;">
            {{ $ticket->category->name ?? 'Sin categoría' }}
        </td>

        <td style="padding:16px;">

            @if($ticket->status=='open')

                <span style="background:rgba(239,68,68,.15);
                             color:#ef4444;
                             padding:4px 8px;
                             border-radius:4px;">
                    Abierto
                </span>

            @elseif($ticket->status=='assigned')

                <span style="background:rgba(59,130,246,.15);
                             color:#3b82f6;
                             padding:4px 8px;
                             border-radius:4px;">
                    Asignado
                </span>

            @elseif($ticket->status=='pending_approval')

                <span style="background:rgba(234,179,8,.15);
                             color:#eab308;
                             padding:4px 8px;
                             border-radius:4px;">
                    Pendiente
                </span>

            @elseif($ticket->status=='in_progress')

                <span style="background:rgba(59,130,246,.15);
                             color:#3b82f6;
                             padding:4px 8px;
                             border-radius:4px;">
                    En progreso
                </span>

            @elseif($ticket->status=='resolved')

                <span style="background:rgba(16,185,129,.15);
                             color:#10b981;
                             padding:4px 8px;
                             border-radius:4px;">
                    Resuelto
                </span>

            @else

                <span style="background:#475569;
                             color:white;
                             padding:4px 8px;
                             border-radius:4px;">
                    Cerrado
                </span>

            @endif

        </td>

        <td style="padding:16px;color:#f1f5f9;">

            @switch($ticket->priority)

                @case('urgent')
                    <span style="color:#dc2626;font-weight:bold;">Urgente</span>
                    @break

                @case('high')
                    <span style="color:#ef4444;font-weight:bold;">Alta</span>
                    @break

                @case('medium')
                    <span style="color:#eab308;font-weight:bold;">Media</span>
                    @break

                @default
                    <span style="color:#22c55e;font-weight:bold;">Baja</span>

            @endswitch

        </td>

        <td style="padding:16px;text-align:center;">

            <a href="{{ route('tickets.show',$ticket) }}"
               style="color:#3b82f6;text-decoration:none;font-weight:600;">
                Ver detalle
            </a>

        </td>

    </tr>

    @endforeach

@else

<tr>

    <td colspan="6"
        style="padding:25px;
               text-align:center;
               color:#94a3b8;">

        No se encontraron tickets.

    </td>

</tr>

@endif

</tbody>
            </table>
        </div>

    </div>
</x-app-layout>