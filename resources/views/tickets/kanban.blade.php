<x-app-layout>
    {{-- Cargado como <script src> normal (bloqueante, sin defer/module) para
         que "Sortable" ya exista en window cuando Alpine (que sí se carga
         diferido vía Vite) evalúe los x-init de más abajo. --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <div x-data="{
        idioma: localStorage.getItem('ticketus_lang') || 'es',

        // Mismo diccionario que tickets/index.blade.php: el componente de
        // filtros lee textosTickets[idioma] del scope Alpine del padre.
        textosTickets: {
            es: {
                titulo: 'Gestión de Tickets',
                subtitulo: 'Historial y estado actual de tus reportes de soporte.',
                btnCrear: '+ Crear Nuevo Ticket',
                btnExcel: '📊 Excel',
                btnPdf: '📄 PDF',
                lblBuscar: 'Buscar',
                lblCategoria: 'Categoría',
                lblPrioridad: 'Prioridad',
                lblEstado: 'Estado',
                phBuscar: 'Título del ticket...',
                optTodas: 'Todas',
                optTodos: 'Todos',
                btnBuscar: 'Buscar',
                btnLimpiar: 'Limpiar',
                estadoAbierto: 'Abierto',
                estadoAsignado: 'Asignado',
                estadoPendiente: 'Pendiente de Aprobación',
                estadoProgreso: 'En Progreso',
                estadoEnEspera: 'En Espera',
                estadoResuelto: 'Resuelto',
                estadoCerrado: 'Cerrado',
                estadoCancelado: 'Cancelado',
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
        },

        kanbanError: '',

        // En Espera (comentario obligatorio) y Resuelto (texto de resolución
        // obligatorio) no son cambios de estado simples: al soltar la
        // tarjeta ahí se abre un modal en vez de confirmar directo. Si se
        // cancela el modal, pendingMove.revert() regresa la tarjeta a su
        // columna original (mismo patrón que ya usa moveTicket para errores).
        holdModalOpen: false,
        holdComment: '',
        resolutionModalOpen: false,
        resolutionText: '',
        pendingMove: null,
        modalSaving: false,

        showKanbanError(msg) {
            this.kanbanError = msg;
            setTimeout(() => { this.kanbanError = ''; }, 5000);
        },

        async postJson(url, method, body) {
            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify(body),
                });
                const data = await res.json().catch(() => ({}));
                return { ok: res.ok, data: data };
            } catch (e) {
                return { ok: false, data: { message: 'Error de conexión.' } };
            }
        },

        // Cuenta solo tarjetas reales (.kanban-card), no el placeholder de
        // columna vacía: si la columna destino estaba vacía, ese placeholder
        // sigue en el DOM junto a la tarjeta recién soltada (Sortable solo
        // inserta, no sabe de placeholders) y hay que sacarlo; si la columna
        // origen quedó vacía, hay que volver a mostrarlo ahí.
        syncColumnPlaceholder(columnEl) {
            if (! columnEl) return 0;

            const cardCount = columnEl.querySelectorAll('.kanban-card').length;
            let placeholder = columnEl.querySelector('.kanban-empty-placeholder');

            if (cardCount > 0 && placeholder) {
                placeholder.remove();
            } else if (cardCount === 0 && ! placeholder) {
                placeholder = document.createElement('div');
                placeholder.className = 'text-muted-adaptive kanban-empty-placeholder';
                placeholder.style.cssText = 'font-size:12px; text-align:center; padding:20px 8px;';
                placeholder.textContent = 'Sin tickets.';
                columnEl.appendChild(placeholder);
            }

            return cardCount;
        },

        refreshCounts(fromColumn, toColumn) {
            const fromEl = document.querySelector('[data-column=' + fromColumn + ']');
            const toEl = document.querySelector('[data-column=' + toColumn + ']');
            if (fromEl) document.getElementById('kanban-count-' + fromColumn).textContent = this.syncColumnPlaceholder(fromEl);
            if (toEl) document.getElementById('kanban-count-' + toColumn).textContent = this.syncColumnPlaceholder(toEl);
        },

        async moveTicket(evt) {
            const card = evt.item;
            const ticketId = card.dataset.ticketId;
            const fromColumn = evt.from.dataset.column;
            const toColumn = evt.to.dataset.column;

            const revert = () => {
                const ref = evt.from.children[evt.oldIndex] || null;
                evt.from.insertBefore(card, ref);
            };

            if (fromColumn === toColumn) {
                return;
            }

            if (toColumn === 'on_hold') {
                this.pendingMove = { ticketId: ticketId, fromColumn: fromColumn, toColumn: toColumn, revert: revert };
                this.holdComment = '';
                this.holdModalOpen = true;
                return;
            }

            if (toColumn === 'resolved') {
                this.pendingMove = { ticketId: ticketId, fromColumn: fromColumn, toColumn: toColumn, revert: revert };
                this.resolutionText = '';
                this.resolutionModalOpen = true;
                return;
            }

            const statusMap = { in_progress: 'in_progress' };
            const newStatus = statusMap[toColumn];

            if (! newStatus) {
                revert();
                this.showKanbanError('Esta columna no admite mover tickets directamente aquí.');
                return;
            }

            const result = await this.postJson('/tickets/' + ticketId + '/status', 'PATCH', { status: newStatus });

            if (result.ok) {
                this.refreshCounts(fromColumn, toColumn);
            } else {
                revert();
                this.showKanbanError(result.data.message || 'No se pudo mover el ticket.');
            }
        },

        cancelPendingMove() {
            if (this.pendingMove) this.pendingMove.revert();
            this.pendingMove = null;
            this.holdModalOpen = false;
            this.resolutionModalOpen = false;
        },

        async confirmHold() {
            if (! this.holdComment.trim()) return;
            const move = this.pendingMove;
            this.modalSaving = true;
            const result = await this.postJson('/tickets/' + move.ticketId + '/hold', 'PATCH', { comment: this.holdComment });
            this.modalSaving = false;
            this.holdModalOpen = false;
            this.pendingMove = null;

            if (result.ok) {
                this.refreshCounts(move.fromColumn, move.toColumn);
            } else {
                move.revert();
                this.showKanbanError(result.data.message || 'No se pudo poner en espera.');
            }
        },

        async confirmResolution() {
            if (! this.resolutionText.trim()) return;
            const move = this.pendingMove;
            this.modalSaving = true;
            const result = await this.postJson('/tickets/' + move.ticketId + '/resolution', 'PUT', { resolution: this.resolutionText });
            this.modalSaving = false;
            this.resolutionModalOpen = false;
            this.pendingMove = null;

            if (result.ok) {
                this.refreshCounts(move.fromColumn, move.toColumn);
            } else {
                move.revert();
                this.showKanbanError(result.data.message || 'No se pudo resolver el ticket.');
            }
        }
    }" @keydown.escape.window="cancelPendingMove()" style="max-width:1600px; margin:40px auto; padding:0 20px;">

        @if(session('success'))
            <div style="margin-bottom: 16px; padding: 16px; background-color: #dcfce7; color: #166534; border-radius: 6px;">
                {{ session('success') }}
            </div>
        @endif

        <div x-show="kanbanError" x-cloak
             style="margin-bottom: 16px; padding: 16px; background-color: #fee2e2; color: #991b1b; border-radius: 6px;"
             x-text="kanbanError">
        </div>

        {{-- Encabezado --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 x-text="textosTickets[idioma].titulo" style="font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 6px;"></h1>
                <p class="text-muted-adaptive" x-text="textosTickets[idioma].subtitulo" style="font-size: 15px; margin: 0;"></p>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                {{-- Toggle Tabla / Kanban --}}
                <div style="display:flex; border:1px solid #cbd5e1; border-radius:6px; overflow:hidden;">
                    <a href="{{ route('tickets.index', request()->query()) }}"
                       style="padding:10px 16px; font-size:13px; font-weight:600; text-decoration:none; color:#374151; background-color:#f1f5f9;">
                        <i class="ti ti-list"></i> Vista Tabla
                    </a>
                    <a href="{{ route('tickets.kanban', request()->query()) }}"
                       style="padding:10px 16px; font-size:13px; font-weight:600; text-decoration:none; color:#ffffff; background-color:#1e3a5f;">
                        <i class="ti ti-layout-kanban"></i> Vista Kanban
                    </a>
                </div>
                <a href="{{ route('tickets.create') }}"
                   x-text="textosTickets[idioma].btnCrear"
                   style="background-color: #2563eb; color: #ffffff; text-decoration:none; padding:12px 20px; border-radius:6px; font-size:14px; font-weight:600; display:inline-block;">
                </a>
            </div>
        </div>

        <x-ticket-filters :categories="$categories" :agents="$agents" />

        {{-- Estilos propios del tablero: mismo patrón que ticket-table-wrap en
             tickets/index.blade.php (clases + "html.dark ..."), no el hack
             global por atributo de estilo, porque acá el color de fondo de
             columnas/tarjetas es propio y no debe "aplanarse". --}}
        <style>
            .kanban-scroll {
                display: flex;
                gap: 16px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 12px;
            }
            .kanban-column {
                flex: 0 0 300px;
                min-width: 300px;
                background-color: #f1f5f9;
                border: 1px solid #e2e8f0;
                border-radius: 10px;
                display: flex;
                flex-direction: column;
                max-height: 78vh;
            }
            html.dark .kanban-column { background-color: #0f172a !important; border-color: #334155 !important; }

            .kanban-column-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 14px 16px;
                border-bottom: 2px solid var(--kb-accent, #cbd5e1);
                font-weight: 700;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: .4px;
                color: #1e293b;
            }
            html.dark .kanban-column-header { color: #f1f5f9 !important; }

            .kanban-column-count {
                background-color: #e2e8f0;
                color: #374151;
                font-size: 12px;
                font-weight: 700;
                padding: 2px 10px;
                border-radius: 9999px;
            }
            html.dark .kanban-column-count { background-color: #334155 !important; color: #e2e8f0 !important; }

            .kanban-column-body {
                flex: 1;
                overflow-y: auto;
                padding: 12px;
                display: flex;
                flex-direction: column;
                gap: 10px;
                min-height: 80px;
            }

            .kanban-card {
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 12px 14px;
                box-shadow: 0 1px 2px rgba(0,0,0,.06);
                cursor: grab;
                text-decoration: none;
                display: block;
            }
            html.dark .kanban-card { background-color: #1e293b !important; border-color: #334155 !important; }

            .kanban-card--locked { cursor: default; opacity: .8; }
            .kanban-card.sortable-ghost { opacity: .4; }
            .kanban-card.sortable-drag { box-shadow: 0 4px 10px rgba(0,0,0,.2); }

            .kanban-card-id {
                font-family: monospace;
                font-weight: 700;
                font-size: 12px;
                color: #64748b;
            }
            html.dark .kanban-card-id { color: #94a3b8 !important; }

            .kanban-card-title {
                font-size: 14px;
                font-weight: 600;
                color: #1e293b;
                margin: 6px 0 8px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            html.dark .kanban-card-title { color: #f1f5f9 !important; }

            .kanban-card-meta {
                font-size: 12px;
                color: #64748b;
                margin-bottom: 3px;
            }
            html.dark .kanban-card-meta { color: #94a3b8 !important; }

            .kanban-card-footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 10px;
                gap: 6px;
                flex-wrap: wrap;
            }

            .kanban-due {
                font-size: 11px;
                font-weight: 700;
                padding: 2px 8px;
                border-radius: 9999px;
                background-color: #f1f5f9;
                color: #475569;
            }
            html.dark .kanban-due { background-color: #334155 !important; color: #cbd5e1 !important; }

            .kanban-due--overdue {
                background-color: #fee2e2 !important;
                color: #991b1b !important;
            }
            html.dark .kanban-due--overdue { background-color: #7f1d1d !important; color: #fecaca !important; }

            /* Modales de "en espera"/resolución: mismas clases que
               ticket-status-menu.blade.php (mismo problema de x-show
               rompiendo el hack global por atributo de estilo, ver comentario
               en ese componente). */
            .ts-modal-box { background-color:#ffffff; }
            html.dark .ts-modal-box { background-color:#1e293b !important; }

            .ts-modal-header { border-bottom:1px solid #e2e8f0; }
            html.dark .ts-modal-header { border-color:#334155 !important; }

            .ts-modal-title { color:#1e293b; }
            html.dark .ts-modal-title { color:#f1f5f9 !important; }

            .ts-modal-close { color:#64748b; }
            html.dark .ts-modal-close { color:#94a3b8 !important; }

            .ts-hint-neutral { color:#64748b; }
            html.dark .ts-hint-neutral { color:#94a3b8 !important; }

            .ts-hint-warning { color:#9a3412; background-color:#fff7ed; border:1px solid #fed7aa; }
            html.dark .ts-hint-warning { color:#fed7aa !important; background-color:#7c2d12 !important; border-color:#9a3412 !important; }

            .ts-textarea { border:1px solid #cbd5e1; background-color:#ffffff; color:#1e293b; }
            html.dark .ts-textarea { background-color:#0f172a !important; border-color:#475569 !important; color:#f1f5f9 !important; }

            .ts-btn-secondary { background-color:#e5e7eb; color:#374151; }
            html.dark .ts-btn-secondary { background-color:#334155 !important; color:#e2e8f0 !important; }
        </style>

        <div class="kanban-scroll">
            @php
                $columns = [
                    'open' => [
                        'label' => 'Abierto',
                        'statuses' => ['open'],
                        // Ya no se puede "retroceder" a Abierto arrastrando: la
                        // única forma de volver a este estado es la Reapertura
                        // (con motivo) o la aprobación del flujo — nunca un
                        // drop libre. Antes esto aceptaba drops; era inconsistente
                        // con la matriz de estados (TicketStatusService).
                        'accepts_drop' => false,
                        'accent' => '#2563eb',
                    ],
                    'pending_approval' => [
                        'label' => 'Pendiente de Aprobación',
                        'statuses' => ['pending_approval'],
                        'accepts_drop' => false,
                        'accent' => '#ca8a04',
                    ],
                    'in_progress' => [
                        'label' => 'En Proceso',
                        'statuses' => ['assigned', 'in_progress'],
                        'accepts_drop' => true,
                        'accent' => '#0891b2',
                    ],
                    'on_hold' => [
                        'label' => 'En Espera',
                        'statuses' => ['on_hold'],
                        // Acepta drop, pero moveTicket() intercepta el destino
                        // "on_hold" y abre el modal de motivo antes de
                        // confirmar — nunca hace el PATCH directo.
                        'accepts_drop' => true,
                        'accent' => '#ea580c',
                    ],
                    'resolved' => [
                        'label' => 'Resuelto',
                        'statuses' => ['resolved', 'closed'],
                        // Mismo caso: acepta drop, pero abre el modal de
                        // resolución antes de confirmar (ya no resuelve directo).
                        'accepts_drop' => true,
                        'accent' => '#16a34a',
                    ],
                    'cancelled' => [
                        'label' => 'Cancelado',
                        'statuses' => ['cancelled', 'rejected'],
                        'accepts_drop' => false,
                        'accent' => '#6b7280',
                    ],
                ];

                $priorityColors = [
                    'low' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Baja'],
                    'medium' => ['bg' => '#ffedd5', 'text' => '#9a3412', 'label' => 'Media'],
                    'high' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Alta'],
                    'urgent' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Urgente'],
                ];

                $authUser = Auth::user();

                $ticketsByColumn = [];
                foreach ($columns as $key => $col) {
                    $ticketsByColumn[$key] = $tickets->filter(
                        fn ($t) => in_array($t->status, $col['statuses'], true)
                    )->values();
                }
            @endphp

            @foreach($columns as $key => $col)
                <div class="kanban-column" style="--kb-accent: {{ $col['accent'] }};">
                    <div class="kanban-column-header">
                        <span>{{ $col['label'] }}</span>
                        <span class="kanban-column-count" id="kanban-count-{{ $key }}">{{ $ticketsByColumn[$key]->count() }}</span>
                    </div>

                    <div class="kanban-column-body"
                         data-column="{{ $key }}"
                         x-init="Sortable.create($el, {
                            group: { name: 'kanban-tickets', put: {{ $col['accepts_drop'] ? 'true' : 'false' }} },
                            animation: 150,
                            filter: '.kanban-card--locked',
                            preventOnFilter: false,
                            onEnd: (evt) => moveTicket(evt)
                         })">

                        @forelse($ticketsByColumn[$key] as $ticket)
                            @php
                                $p = $priorityColors[$ticket->priority] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->priority];

                                $isOverdue = $ticket->due_date
                                    && $ticket->due_date->isPast()
                                    && ! in_array($ticket->status, ['resolved', 'closed', 'rejected', 'cancelled'], true);

                                $canDrag = in_array($ticket->status, ['open', 'assigned', 'in_progress', 'on_hold'], true)
                                    && ($authUser->role === 'admin' || (int) $ticket->assigned_to === (int) $authUser->id);
                            @endphp

                            <a href="{{ route('tickets.show', $ticket) }}"
                               class="kanban-card {{ $canDrag ? '' : 'kanban-card--locked' }}"
                               data-ticket-id="{{ $ticket->id }}"
                               title="{{ $canDrag ? 'Arrastra para cambiar de estado' : 'Solo el agente asignado o un admin puede mover este ticket' }}">

                                <div class="kanban-card-id">#{{ $ticket->id }}</div>

                                <div class="kanban-card-title">{{ \Illuminate\Support\Str::limit($ticket->title, 60) }}</div>

                                <div class="kanban-card-meta"><i class="ti ti-user"></i> {{ $ticket->user->name }}</div>
                                <div class="kanban-card-meta"><i class="ti ti-headset"></i> {{ $ticket->agent?->name ?? 'Sin asignar' }}</div>

                                <div class="kanban-card-footer">
                                    <span style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; padding:2px 8px; border-radius:9999px; font-size:11px; font-weight:700;">
                                        {{ $p['label'] }}
                                    </span>

                                    @if($ticket->due_date)
                                        <span class="kanban-due {{ $isOverdue ? 'kanban-due--overdue' : '' }}">
                                            {{ $ticket->due_date->format('d/m H:i') }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="text-muted-adaptive kanban-empty-placeholder" style="font-size:12px; text-align:center; padding:20px 8px;">
                                Sin tickets.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Modal: motivo obligatorio al soltar en "En Espera" --}}
        <div x-show="holdModalOpen" x-cloak style="position: fixed; inset: 0; z-index: 70;">
            <div style="width:100%; height:100%; background-color:rgba(15,23,42,0.6); display:flex; align-items:center; justify-content:center; padding:24px; box-sizing:border-box;" @click.self="cancelPendingMove()">
                <div class="ts-modal-box" style="border-radius:10px; max-width:460px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px rgba(0,0,0,.25);">
                    <div class="ts-modal-header" style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                        <h3 class="ts-modal-title" style="margin:0; font-size:15px; font-weight:700;">Poner en espera</h3>
                        <button type="button" @click="cancelPendingMove()" class="ts-modal-close" style="background:none; border:none; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
                    </div>
                    <div style="padding:20px;">
                        <p class="ts-hint-warning" style="font-size:12px; border-radius:6px; padding:8px 10px; margin:0 0 12px 0;">
                            Indica el motivo por el que se pausa el ticket (ej. esperando información del solicitante, esperando una pieza).
                        </p>
                        <textarea x-model="holdComment" rows="4" maxlength="1000" placeholder="Motivo obligatorio..." class="ts-textarea"
                                  style="width:100%; border-radius:6px; padding:8px; box-sizing:border-box; font-size:14px;"></textarea>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                            <button type="button" @click="cancelPendingMove()" class="ts-btn-secondary" style="padding:8px 16px; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;">Cancelar</button>
                            <button type="button" @click="confirmHold()" :disabled="modalSaving || ! holdComment.trim()"
                                    style="padding:8px 16px; background-color:#9a3412; color:#ffffff; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;"
                                    x-text="modalSaving ? 'Guardando...' : 'Poner en espera'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal: texto de resolución obligatorio al soltar en "Resuelto" --}}
        <div x-show="resolutionModalOpen" x-cloak style="position: fixed; inset: 0; z-index: 70;">
            <div style="width:100%; height:100%; background-color:rgba(15,23,42,0.6); display:flex; align-items:center; justify-content:center; padding:24px; box-sizing:border-box;" @click.self="cancelPendingMove()">
                <div class="ts-modal-box" style="border-radius:10px; max-width:460px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px rgba(0,0,0,.25);">
                    <div class="ts-modal-header" style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                        <h3 class="ts-modal-title" style="margin:0; font-size:15px; font-weight:700;">Introducir resolución</h3>
                        <button type="button" @click="cancelPendingMove()" class="ts-modal-close" style="background:none; border:none; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
                    </div>
                    <div style="padding:20px;">
                        <p class="ts-hint-neutral" style="font-size:12px; margin:0 0 12px 0;">Al guardar, el ticket pasa a estado &quot;Resuelto&quot;.</p>
                        <textarea x-model="resolutionText" rows="5" maxlength="5000" placeholder="Describe cómo se resolvió el ticket..." class="ts-textarea"
                                  style="width:100%; border-radius:6px; padding:8px; box-sizing:border-box; font-size:14px;"></textarea>
                        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                            <button type="button" @click="cancelPendingMove()" class="ts-btn-secondary" style="padding:8px 16px; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;">Cancelar</button>
                            <button type="button" @click="confirmResolution()" :disabled="modalSaving || ! resolutionText.trim()"
                                    style="padding:8px 16px; background-color:#1e3a5f; color:#ffffff; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;"
                                    x-text="modalSaving ? 'Guardando...' : 'Marcar como resuelto'"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
