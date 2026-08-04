<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Ticket #{{ $ticket->id }}: {{ $ticket->title }}
        </h2>
    </x-slot>

    <style>
        .ticket-3col { display: flex; gap: 20px; align-items: flex-start; }
        .ticket-col-left { width: 260px; flex-shrink: 0; }
        .ticket-col-right { width: 280px; flex-shrink: 0; }
        .ticket-col-main { flex: 1; min-width: 0; }

        @media (max-width: 960px) {
            /* align-items:flex-start (arriba) es para la fila de escritorio
               (no estirar columnas de distinta altura); en columna ese mismo
               align-items pasa a controlar el eje horizontal, así que sin
               "stretch" cada hijo se dimensiona por su contenido en vez de
               ocupar el ancho completo — eso es lo que causaba el overflow
               horizontal (.ticket-col-main renderizando ~650px en un
               viewport de 375px). */
            .ticket-3col { flex-direction: column; align-items: stretch; }
            .ticket-col-left, .ticket-col-right { width: 100%; }

            /* En escritorio el orden natural del DOM ya es
               izquierda(relacionados)/centro(principal)/derecha(propiedades).
               En móvil se reordena visualmente con flex `order` (sin tocar el
               DOM, para no arriesgar el scope de Alpine que vive en <main>):
               principal primero, propiedades después, relacionados al final. */
            .ticket-col-main { order: 1; }
            .ticket-col-right { order: 2; }
            .ticket-col-left { order: 3; }
        }

        /* Dropdown "Acciones": su grid de 3 columnas (680px) se sale de
           cualquier pantalla angosta. Por debajo de 640px colapsa a 1
           columna; el ancho del panel ya se limita con min() en el propio
           componente (ver width="w-[min(680px,92vw)]" abajo). */
        @media (max-width: 640px) {
            .ticket-actions-grid { grid-template-columns: 1fr !important; }
        }
    </style>

    <div style="padding: 24px 0;">
        <div style="max-width: 1400px; margin: 0 auto; padding: 0 24px;">

            @if(session('success'))
                <div style="margin-bottom: 16px; padding: 16px; background-color: #dcfce7; color: #166534; border-radius: 6px;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="margin-bottom: 16px; padding: 16px; background-color: #fee2e2; color: #991b1b; border-radius: 6px;">
                    @foreach($errors->all() as $error)
                        <p style="margin: 0;">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @php
                $statusColors = [
                    'open' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Abierto'],
                    'pending_approval' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'Pendiente de Aprobación'],
                    'rejected' => ['bg' => '#fecaca', 'text' => '#7f1d1d', 'label' => 'Rechazado'],
                    'assigned' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'En Proceso'],
                    'in_progress' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'En Proceso'],
                    'on_hold' => ['bg' => '#fed7aa', 'text' => '#9a3412', 'label' => 'En Espera'],
                    'resolved' => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Resuelto'],
                    'closed' => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Cerrado'],
                    'cancelled' => ['bg' => '#f3e8ff', 'text' => '#6b21a8', 'label' => 'Cancelado'],
                ];
                $priorityColors = [
                    'low' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Baja'],
                    'medium' => ['bg' => '#ffedd5', 'text' => '#9a3412', 'label' => 'Media'],
                    'high' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Alta'],
                    'urgent' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Urgente'],
                ];
                $s = $statusColors[$ticket->status] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->status];
                $p = $priorityColors[$ticket->priority] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->priority];
                $isOverdue = $ticket->due_date && $ticket->due_date->isPast() && ! in_array($ticket->status, ['resolved', 'closed', 'rejected', 'cancelled'], true);
                $site = $ticket->user->department ?? '—';
            @endphp

            <div class="ticket-3col">

                {{-- ================= COLUMNA IZQUIERDA ================= --}}
                <aside class="ticket-col-left">
                    <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                        <div style="background-color: #1e3a5f; padding: 12px 16px;">
                            <span style="color: #ffffff; font-size: 13px; font-weight: 600;">Tickets del Solicitante</span>
                        </div>
                        <div>
                            @forelse($relatedTickets as $rt)
                                @php
                                    $isActive = $rt->id === $ticket->id;
                                    $rtOverdue = $rt->due_date && $rt->due_date->isPast() && ! in_array($rt->status, ['resolved', 'closed', 'rejected'], true);
                                @endphp
                                <a href="{{ route('tickets.show', $rt) }}"
                                   style="display: block; padding: 12px 16px; text-decoration: none; border-bottom: 1px solid #e2e8f0; border-left: 3px solid {{ $isActive ? '#2563eb' : 'transparent' }}; background-color: {{ $isActive ? '#eff6ff' : '#ffffff' }};">
                                    <p style="margin: 0 0 2px 0; font-size: 11px; font-family: monospace; color: #94a3b8;">#{{ $rt->id }}</p>
                                    <p style="margin: 0 0 4px 0; font-size: 13px; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $rt->title }}
                                    </p>
                                    <p style="margin: 0 0 2px 0; font-size: 11px; color: {{ $rtOverdue ? '#dc2626' : '#64748b' }}; font-weight: {{ $rtOverdue ? '600' : '400' }};">
                                        Vence: {{ $rt->due_date?->format('d/m/Y H:i') ?? '—' }}
                                    </p>
                                    <p style="margin: 0; font-size: 11px; color: #94a3b8;">{{ $rt->user->name }}</p>
                                </a>
                            @empty
                                <p style="padding: 16px; font-size: 13px; color: #64748b; margin: 0;">Sin otras solicitudes.</p>
                            @endforelse
                        </div>
                    </div>
                </aside>

                {{-- ================= COLUMNA CENTRAL ================= --}}
                @php
                    $canSendForApproval = $ticket->approvals->isEmpty()
                        && ! in_array($ticket->status, ['pending_approval', 'resolved', 'closed', 'rejected', 'cancelled'], true);
                    $runningTimeLog = $ticket->timeLogs->first(fn ($l) => (int) $l->user_id === (int) Auth::id() && $l->ended_at === null);
                    // Carbon 3 diffInMinutes() devuelve float; se redondea al minuto.
                    $totalMinutesLogged = (int) round($ticket->timeLogs->whereNotNull('ended_at')->sum(fn ($l) => $l->started_at->diffInMinutes($l->ended_at)));
                    $canSeeReminders = Auth::user()->role === 'admin' || (int) Auth::id() === (int) $ticket->assigned_to;
                    $canAddInternalNote = in_array(Auth::user()->role, ['admin', 'agent'], true);
                @endphp
                <main class="ticket-col-main" x-data="{
                    tab: 'detalles',
                    openModal: null,
                    tabStyle(key) {
                        return this.tab === key
                            ? 'padding:10px 16px; background:none; border:none; border-bottom:2px solid #2563eb; color:#2563eb; font-weight:600; font-size:14px; cursor:pointer; white-space:nowrap; margin-bottom:-2px;'
                            : 'padding:10px 16px; background:none; border:none; border-bottom:2px solid transparent; color:#64748b; font-weight:500; font-size:14px; cursor:pointer; white-space:nowrap; margin-bottom:-2px;';
                    }
                }">

                    {{-- Encabezado + barra de acciones --}}
                    <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 20px 24px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
                            <div>
                                <p style="font-family: monospace; color: #64748b; font-size: 13px; margin: 0 0 4px 0;">#{{ $ticket->id }}</p>
                                <h1 style="font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">{{ $ticket->title }}</h1>
                                <p style="font-size: 13px; color: #64748b; margin: 0 0 8px 0;">
                                    Creado por <strong style="color: #374151;">{{ $ticket->user->name }}</strong> el {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </p>
                                @if($ticket->tags->isNotEmpty())
                                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                        @foreach($ticket->tags as $tag)
                                            <span style="display: inline-flex; align-items: center; gap: 6px; background-color: #eef2ff; color: #4338ca; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600;">
                                                {{ $tag->name }}
                                                @can('update', $ticket)
                                                    <form action="{{ route('tickets.tags.destroy', [$ticket, $tag]) }}" method="POST" style="display: inline; margin: 0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" title="Quitar etiqueta" style="background: none; border: none; padding: 0; margin: 0; cursor: pointer; color: #4338ca; font-size: 12px; line-height: 1;">&times;</button>
                                                    </form>
                                                @endcan
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @if($ticket->due_date)
                                <div style="text-align: right;">
                                    <p style="font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin: 0 0 4px 0;">Vence</p>
                                    <p style="font-size: 15px; font-weight: 700; color: {{ $isOverdue ? '#991b1b' : '#1e293b' }}; margin: 0 0 4px 0;">
                                        {{ $ticket->due_date->format('d/m/Y H:i') }}
                                    </p>
                                    <span style="background-color: {{ $isOverdue ? '#fee2e2' : '#dcfce7' }}; color: {{ $isOverdue ? '#991b1b' : '#166534' }}; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 700;">
                                        {{ $ticket->remaining }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Barra de acciones --}}
                        <div style="display: flex; gap: 8px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0; flex-wrap: wrap;">
                            @can('update', $ticket)
                                <a href="{{ route('tickets.edit', $ticket) }}"
                                   style="display: inline-flex; align-items: center; gap: 6px; background-color: #1e3a5f; color: #ffffff; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600;">
                                    <i class="ti ti-edit"></i> Editar
                                </a>

                                <x-dropdown align="left" width="w-56">
                                    <x-slot name="trigger">
                                        <button type="button" style="display: inline-flex; align-items: center; gap: 6px; background-color: #ffffff; color: #374151; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                                            <i class="ti ti-user-plus"></i> Asignar
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <form action="{{ route('tickets.update', $ticket) }}" method="POST" style="padding: 16px; min-width: 220px;">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="{{ $ticket->status }}">
                                            <input type="hidden" name="priority" value="{{ $ticket->priority }}">
                                            <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px;">Técnico</label>
                                            <select name="assigned_to" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box; margin-bottom: 10px;">
                                                <option value="">Sin asignar</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>
                                                        {{ $agent->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" style="width: 100%; background-color: #1e3a5f; color: #ffffff; padding: 8px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: 600;">
                                                Asignar
                                            </button>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            @endcan

                            @can('update', $ticket)
                                @php
                                    $actionItemStyle = 'display:flex; align-items:center; gap:8px; width:100%; text-align:left; padding:8px 10px; margin:2px 0; font-size:13px; color:#374151; background:none; border:none; border-radius:6px; cursor:pointer;';
                                    $actionHoverAttrs = 'onmouseover="this.style.backgroundColor=\'#f1f5f9\'" onmouseout="this.style.removeProperty(\'background-color\')"';
                                @endphp
                                <x-dropdown align="left" width="w-[min(680px,76vw)]">
                                    <x-slot name="trigger">
                                        <button type="button" style="display: inline-flex; align-items: center; gap: 6px; background-color: #ffffff; color: #374151; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                                            Acciones <i class="ti ti-chevron-down" style="font-size: 12px;"></i>
                                        </button>
                                    </x-slot>
                                    <x-slot name="content">
                                        <div class="ticket-actions-grid" style="padding: 16px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px 20px;">
                                            {{-- COLUMNA 1: Gestión del ticket --}}
                                            <div>
                                                <p style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin: 4px 0 8px 0;">Gestión del ticket</p>
                                                @if($canAddInternalNote)
                                                    <button type="button" @click="openModal = 'internalNote'" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }}">
                                                        <i class="ti ti-note"></i> Agregar nota interna
                                                    </button>
                                                @endif
                                                <button type="button" @click="openModal = 'resolution'" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }}">
                                                    <i class="ti ti-check"></i> Introducir resolución
                                                </button>
                                                <button type="button" @click="openModal = 'attachment'" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }}">
                                                    <i class="ti ti-paperclip"></i> Agregar archivo adjunto
                                                </button>
                                                @if($canSeeReminders)
                                                    <button type="button" @click="openModal = 'reminder'" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }}">
                                                        <i class="ti ti-bell"></i> Agregar recordatorio
                                                    </button>
                                                @endif
                                            </div>

                                            {{-- COLUMNA 2: Flujo y seguimiento --}}
                                            <div>
                                                <p style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin: 4px 0 8px 0;">Flujo y seguimiento</p>
                                                @if($canSendForApproval)
                                                    <button type="button" @click="openModal = 'sendApproval'" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }}">
                                                        <i class="ti ti-send"></i> Enviar para su aprobación
                                                    </button>
                                                @endif
                                                <button type="button" @click="openModal = 'timer'" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }}">
                                                    <i class="ti ti-clock"></i> {{ $runningTimeLog ? 'Detener temporizador' : 'Iniciar temporizador' }}
                                                </button>
                                                <button type="button" @click="tab = 'trabajo'" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }}">
                                                    <i class="ti ti-history"></i> Ver historial de cambios
                                                </button>
                                            </div>

                                            {{-- COLUMNA 3: Organización --}}
                                            <div>
                                                <p style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin: 4px 0 8px 0;">Organización</p>
                                                <button type="button" @click="openModal = 'tag'" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }}">
                                                    <i class="ti ti-tag"></i> Agregar etiqueta
                                                </button>
                                                <form action="{{ route('tickets.duplicate', $ticket) }}" method="POST" onsubmit="return confirm('¿Copiar esta solicitud como un ticket nuevo?');">
                                                    @csrf
                                                    <button type="submit" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }}">
                                                        <i class="ti ti-copy"></i> Copiar solicitud
                                                    </button>
                                                </form>
                                                <button type="button" @click="openModal = 'cancel'" {!! $actionHoverAttrs !!} style="{{ $actionItemStyle }} color:#991b1b;">
                                                    <i class="ti ti-x"></i> Cancelar solicitud
                                                </button>
                                            </div>
                                        </div>

                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; border-top: 1px solid #e2e8f0;">
                                            <a href="{{ route('tickets.index') }}" style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #374151; text-decoration: none;">
                                                <i class="ti ti-arrow-left"></i> Volver a la lista
                                            </a>
                                            @can('delete', $ticket)
                                                <form action="{{ route('tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('¿Eliminar este ticket? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: #991b1b; background: none; border: none; cursor: pointer;">
                                                        <i class="ti ti-trash"></i> Eliminar ticket
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </x-slot>
                                </x-dropdown>
                            @endcan

                            <x-dropdown align="left" width="48">
                                <x-slot name="trigger">
                                    <button type="button" style="display: inline-flex; align-items: center; gap: 6px; background-color: #ffffff; color: #374151; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                                        <i class="ti ti-message-circle"></i> Respuesta <i class="ti ti-chevron-down" style="font-size: 12px;"></i>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <button type="button"
                                            @click="tab = 'comentarios'; $nextTick(() => $refs.commentBox?.focus())"
                                            style="display: block; width: 100%; text-align: left; padding: 10px 16px; font-size: 13px; color: #374151; background: none; border: none; cursor: pointer;">
                                        <i class="ti ti-corner-up-left"></i> Responder al solicitante
                                    </button>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>

                    {{-- Tabs --}}
                    <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                        <div style="display: flex; gap: 4px; border-bottom: 2px solid #e2e8f0; padding: 0 20px; overflow-x: auto;">
                            <button type="button" @click="tab = 'detalles'" :style="tabStyle('detalles')">Detalles</button>
                            <button type="button" @click="tab = 'resolucion'" :style="tabStyle('resolucion')">Resolución</button>
                            <button type="button" @click="tab = 'tareas'" :style="tabStyle('tareas')">Tareas</button>
                            <button type="button" @click="tab = 'trabajo'" :style="tabStyle('trabajo')">Registros de trabajo</button>
                            <button type="button" @click="tab = 'comentarios'" :style="tabStyle('comentarios')">Comentarios e Historial</button>
                        </div>

                        <div style="padding: 24px;">

                            {{-- --- TAB: Detalles --- --}}
                            <div x-show="tab === 'detalles'" x-cloak>
                                <div style="margin-bottom: 24px;">
                                    <p style="font-size: 13px; color: #64748b; margin: 0 0 6px 0;">Descripción</p>
                                    <p style="color: #1e293b; margin: 0; white-space: pre-line;">{{ $ticket->description }}</p>
                                </div>

                                @if($ticket->status === 'pending_approval')
                                    <div style="background-color: #e0e7ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 16px; color: #3730a3; font-size: 14px; margin-bottom: 24px;">
                                        Este ticket está pendiente de aprobación y no puede modificarse hasta que finalice el flujo de aprobación.
                                    </div>
                                @elseif(in_array($ticket->status, ['rejected', 'resolved', 'closed', 'cancelled']) && Auth::user()->can('reopen', $ticket))
                                    <div style="background-color: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 20px; margin-bottom: 24px;">
                                        <h3 style="font-size: 14px; font-weight: 600; color: #9a3412; margin: 0 0 8px 0;">Ticket en estado final</h3>
                                        <p style="font-size: 13px; color: #9a3412; margin: 0 0 16px 0;">
                                            Este ticket está {{ strtolower($s['label']) }}. Para reactivarlo indica el motivo de la reapertura.
                                        </p>
                                        <form action="{{ route('tickets.reopen', $ticket) }}" method="POST">
                                            @csrf
                                            <textarea name="reason" rows="2" required
                                                      style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box; margin-bottom: 12px;"
                                                      placeholder="Motivo de la reapertura..."></textarea>
                                            <button type="submit"
                                                    style="background-color: #9a3412; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                                Reabrir ticket
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                @if($ticket->approvals->isNotEmpty())
                                    @php
                                        $approvalStatusColors = [
                                            'pending' => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Pendiente'],
                                            'approved' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Aprobado'],
                                            'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Rechazado'],
                                        ];
                                    @endphp
                                    <div>
                                        <p style="font-size: 13px; color: #64748b; margin: 0 0 10px 0;">Historial de Aprobación</p>
                                        @foreach($ticket->approvals->sortBy(fn($a) => $a->approvalLevel->order ?? 0) as $approval)
                                            @php
                                                $as = $approvalStatusColors[$approval->status];
                                                $approver = $approval->effectiveApprover();
                                            @endphp
                                            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding: 10px 0;">
                                                <div>
                                                    <p style="margin: 0; font-size: 14px; font-weight: 500; color: #1e293b;">
                                                        {{ $approval->approvalLevel?->name ?? 'Ad-hoc' }}
                                                        @if($approval->approver_id)
                                                            <span style="font-size: 11px; font-weight: 500; color: #b45309;">(ajustado para este ticket)</span>
                                                        @endif
                                                    </p>
                                                    @if($approval->status === 'pending')
                                                        <p style="margin: 0; font-size: 12px; color: #64748b;">
                                                            Aprobador: {{ $approver?->name ?? 'Sin asignar' }}
                                                        </p>
                                                    @elseif($approval->approvedBy)
                                                        <p style="margin: 0; font-size: 12px; color: #64748b;">
                                                            Por {{ $approval->approvedBy->name }} el {{ $approval->approved_at?->format('d/m/Y H:i') }}
                                                        </p>
                                                    @endif
                                                    @if($approval->comments)
                                                        <p style="margin: 4px 0 0 0; font-size: 13px; color: #374151;">"{{ $approval->comments }}"</p>
                                                    @endif
                                                </div>
                                                <span style="background-color: {{ $as['bg'] }}; color: {{ $as['text'] }}; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; white-space: nowrap;">
                                                    {{ $as['label'] }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($ticket->attachments->isNotEmpty())
                                    <div style="margin-top: 24px;">
                                        <p style="font-size: 13px; color: #64748b; margin: 0 0 10px 0;">Archivos Adjuntos</p>
                                        @foreach($ticket->attachments as $attachment)
                                            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding: 10px 0;">
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <i class="ti ti-paperclip" style="color: #64748b;"></i>
                                                    <div>
                                                        <p style="margin: 0; font-size: 14px; font-weight: 500; color: #1e293b;">{{ $attachment->original_name }}</p>
                                                        <p style="margin: 0; font-size: 12px; color: #64748b;">
                                                            {{ round($attachment->size / 1024) }} KB &middot; subido por {{ $attachment->user->name }} el {{ $attachment->created_at->format('d/m/Y H:i') }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <a href="{{ route('tickets.attachments.download', [$ticket, $attachment]) }}" style="font-size: 12px; font-weight: 600; color: #2563eb; text-decoration: none; white-space: nowrap;">
                                                    Descargar
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- --- TAB: Resolución --- --}}
                            <div x-show="tab === 'resolucion'" x-cloak>
                                @if($ticket->resolved_at)
                                    <p style="font-size: 13px; color: #64748b; margin: 0 0 6px 0;">Resuelto el</p>
                                    <p style="color: #1e293b; margin: 0 0 20px 0; font-weight: 500;">{{ $ticket->resolved_at->format('d/m/Y H:i') }}</p>
                                @endif
                                @if($ticket->resolution)
                                    <p style="font-size: 13px; color: #64748b; margin: 0 0 6px 0;">Cómo se resolvió</p>
                                    <p style="color: #1e293b; margin: 0; white-space: pre-line;">{{ $ticket->resolution }}</p>
                                @else
                                    <p style="color: #64748b; font-size: 14px; font-style: italic;">
                                        Todavía no se registró una resolución. Usa "Acciones &rarr; Introducir resolución".
                                    </p>
                                @endif
                            </div>

                            {{-- --- TAB: Tareas --- --}}
                            <div x-show="tab === 'tareas'" x-cloak>
                                <p style="color: #64748b; font-size: 14px; font-style: italic;">
                                    No implementado aún — este sistema no tiene un módulo de tareas.
                                </p>
                            </div>

                            {{-- --- TAB: Registros de trabajo --- --}}
                            <div x-show="tab === 'trabajo'" x-cloak>
                                @php
                                    $actionMeta = [
                                        'approved' => ['icon' => 'ti-check', 'label' => 'Aprobado', 'color' => '#166534'],
                                        'rejected' => ['icon' => 'ti-x', 'label' => 'Rechazado', 'color' => '#991b1b'],
                                        'reopened' => ['icon' => 'ti-refresh', 'label' => 'Reabierto', 'color' => '#9a3412'],
                                        'reassigned' => ['icon' => 'ti-user-share', 'label' => 'Reasignado', 'color' => '#1e40af'],
                                        'commented' => ['icon' => 'ti-message', 'label' => 'Comentario', 'color' => '#64748b'],
                                        'internal_note' => ['icon' => 'ti-note', 'label' => 'Nota interna', 'color' => '#b45309'],
                                        'resolved' => ['icon' => 'ti-check', 'label' => 'Resuelto', 'color' => '#166534'],
                                        'duplicated' => ['icon' => 'ti-copy', 'label' => 'Duplicado', 'color' => '#1e40af'],
                                        'cancelled' => ['icon' => 'ti-x', 'label' => 'Cancelado', 'color' => '#6b21a8'],
                                        'sent_for_approval' => ['icon' => 'ti-send', 'label' => 'Enviado a aprobación', 'color' => '#1e40af'],
                                        'approval_reassigned' => ['icon' => 'ti-user-check', 'label' => 'Aprobador ajustado', 'color' => '#b45309'],
                                        'timer_started' => ['icon' => 'ti-player-play', 'label' => 'Temporizador iniciado', 'color' => '#1e40af'],
                                        'timer_stopped' => ['icon' => 'ti-player-stop', 'label' => 'Temporizador detenido', 'color' => '#64748b'],
                                        'attachment_added' => ['icon' => 'ti-paperclip', 'label' => 'Adjunto agregado', 'color' => '#64748b'],
                                        'reminder_added' => ['icon' => 'ti-bell', 'label' => 'Recordatorio agregado', 'color' => '#b45309'],
                                        'tag_added' => ['icon' => 'ti-tag', 'label' => 'Etiqueta agregada', 'color' => '#4338ca'],
                                        'tag_removed' => ['icon' => 'ti-tag-off', 'label' => 'Etiqueta quitada', 'color' => '#64748b'],
                                    ];
                                    // Igual que en el tab de Comentarios: el texto de la nota interna y del
                                    // recordatorio quedó guardado como description de estos logs, así que el
                                    // filtro de visibilidad se repite acá o se filtraría el comentario pero
                                    // no su eco en el historial.
                                    $visibleActivityLogs = $ticket->activityLogs->filter(function ($log) use ($canAddInternalNote, $canSeeReminders) {
                                        if ($log->action === 'internal_note' && ! $canAddInternalNote) return false;
                                        if ($log->action === 'reminder_added' && ! $canSeeReminders) return false;
                                        return true;
                                    });
                                @endphp
                                @forelse($visibleActivityLogs as $log)
                                    @php $meta = $actionMeta[$log->action] ?? ['icon' => 'ti-info-circle', 'label' => ucfirst($log->action), 'color' => '#64748b']; @endphp
                                    <div style="display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                                        <div style="width: 32px; height: 32px; min-width: 32px; border-radius: 50%; background-color: {{ $meta['color'] }}1a; display: flex; align-items: center; justify-content: center;">
                                            <i class="ti {{ $meta['icon'] }}" style="color: {{ $meta['color'] }}; font-size: 16px;"></i>
                                        </div>
                                        <div style="flex: 1;">
                                            <p style="margin: 0; font-size: 13px; font-weight: 600; color: #1e293b;">
                                                {{ $meta['label'] }}
                                                <span style="font-weight: 400; color: #64748b;">por {{ $log->user->name ?? 'Sistema' }}</span>
                                            </p>
                                            @if($log->description)
                                                <p style="margin: 2px 0 0 0; font-size: 13px; color: #374151;">{{ $log->description }}</p>
                                            @endif
                                            <p style="margin: 2px 0 0 0; font-size: 11px; color: #94a3b8;">{{ $log->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p style="color: #64748b; font-size: 14px;">Sin registros de actividad.</p>
                                @endforelse
                            </div>

                            {{-- --- TAB: Comentarios e Historial --- --}}
                            <div x-show="tab === 'comentarios'" x-cloak>
                                @php
                                    // El solicitante no ve notas internas: se filtran acá, no solo se ocultan con CSS.
                                    $visibleComments = $canAddInternalNote
                                        ? $ticket->comments
                                        : $ticket->comments->where('is_internal', false);
                                @endphp
                                @forelse($visibleComments as $comment)
                                    @php
                                        $initials = collect(explode(' ', trim($comment->user->name)))
                                            ->filter()
                                            ->map(fn($n) => mb_strtoupper(mb_substr($n, 0, 1)))
                                            ->take(2)
                                            ->implode('');
                                    @endphp
                                    <div style="display: flex; gap: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 16px; {{ $comment->is_internal ? 'background-color: #fffbeb; padding: 12px; border-radius: 8px; border: 1px solid #fde68a;' : '' }}">
                                        <div style="width: 36px; height: 36px; min-width: 36px; border-radius: 50%; background-color: #1e3a5f; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                                            {{ $initials }}
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                                <span style="font-size: 14px; font-weight: 600; color: #1e293b;">
                                                    {{ $comment->user->name }}
                                                    @if($comment->is_internal)
                                                        <span style="background-color: #fde68a; color: #92400e; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-left: 6px;">Nota interna</span>
                                                    @endif
                                                </span>
                                                <span style="font-size: 12px; color: #64748b;">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <p style="color: #374151; font-size: 14px; margin: 0;">{{ $comment->comment }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p style="color: #64748b; font-size: 14px;">No hay comentarios aún.</p>
                                @endforelse

                                <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST" style="margin-top: 16px;">
                                    @csrf
                                    <div style="margin-bottom: 12px;">
                                        <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Agregar comentario</label>
                                        <textarea name="comment" rows="3" x-ref="commentBox"
                                                  style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;"
                                                  placeholder="Escribe tu comentario aquí..."></textarea>
                                    </div>
                                    <div style="display: flex; gap: 10px;">
                                        <button type="submit"
                                                style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                            Responder
                                        </button>
                                        <button type="button" disabled title="Función no disponible aún"
                                                style="background-color: #e5e7eb; color: #9ca3af; padding: 10px 18px; border-radius: 6px; border: none; font-size: 14px; font-weight: 500; cursor: not-allowed;">
                                            Reenviar
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                    {{-- ============ MODALES DEL MENÚ "ACCIONES" ============
                         Wrapper de visibilidad (x-show, sin más estilos) separado
                         del wrapper de layout (flex/centrado): x-show limpia la
                         propiedad display del MISMO atributo style si conviven,
                         dejando el modal pegado a la izquierda en vez de centrado. --}}
                    @php
                        $modalOverlayWrap = 'position: fixed; inset: 0; z-index: 60;';
                        $modalOverlayFlex = 'width: 100%; height: 100%; background-color: rgba(15,23,42,0.6); display: flex; align-items: center; justify-content: center; padding: 24px; box-sizing: border-box;';
                        $modalCard = 'background-color: #ffffff; border-radius: 10px; max-width: 480px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.25);';
                        $modalHeader = 'padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;';
                        $modalTitle = 'margin: 0; font-size: 15px; font-weight: 700; color: #1e293b;';
                        $modalClose = 'background: none; border: none; cursor: pointer; color: #64748b; font-size: 20px; line-height: 1; padding: 0;';
                        $modalBody = 'padding: 20px;';
                        $modalFooter = 'display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;';
                        $btnCancel = 'padding: 8px 16px; background-color: #e5e7eb; color: #374151; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: 600;';
                        $btnPrimary = 'padding: 8px 16px; background-color: #1e3a5f; color: #ffffff; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: 600;';
                        $btnDanger = 'padding: 8px 16px; background-color: #991b1b; color: #ffffff; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: 600;';
                        $fieldStyle = 'width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box; font-size: 14px;';
                        $flow = $ticket->category->approvalFlow ?? null;
                    @endphp

                    {{-- 1. Agregar nota interna --}}
                    @if($canAddInternalNote)
                        <div id="internal-note-modal" x-show="openModal === 'internalNote'" x-cloak style="{{ $modalOverlayWrap }}">
                            <div style="{{ $modalOverlayFlex }}" @click.self="openModal = null">
                                <div style="{{ $modalCard }}">
                                    <div style="{{ $modalHeader }}">
                                        <h3 style="{{ $modalTitle }}">Agregar nota interna</h3>
                                        <button type="button" @click="openModal = null" style="{{ $modalClose }}">&times;</button>
                                    </div>
                                    <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST" style="{{ $modalBody }}">
                                        @csrf
                                        <input type="hidden" name="is_internal" value="1">
                                        <p style="font-size: 12px; color: #b45309; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 8px 10px; margin: 0 0 12px 0;">
                                            Solo la ven admin/agentes. El solicitante no la ve ni recibe notificación.
                                        </p>
                                        <textarea name="comment" rows="4" required maxlength="1000" style="{{ $fieldStyle }}" placeholder="Escribe la nota interna..."></textarea>
                                        <div style="{{ $modalFooter }}">
                                            <button type="button" @click="openModal = null" style="{{ $btnCancel }}">Cancelar</button>
                                            <button type="submit" style="{{ $btnPrimary }}">Guardar nota</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 2. Introducir resolución --}}
                    <div id="resolution-modal" x-show="openModal === 'resolution'" x-cloak style="{{ $modalOverlayWrap }}">
                        <div style="{{ $modalOverlayFlex }}" @click.self="openModal = null">
                            <div style="{{ $modalCard }}">
                                <div style="{{ $modalHeader }}">
                                    <h3 style="{{ $modalTitle }}">Introducir resolución</h3>
                                    <button type="button" @click="openModal = null" style="{{ $modalClose }}">&times;</button>
                                </div>
                                <form action="{{ route('tickets.resolution', $ticket) }}" method="POST" style="{{ $modalBody }}">
                                    @csrf
                                    @method('PUT')
                                    <p style="font-size: 12px; color: #64748b; margin: 0 0 12px 0;">
                                        Al guardar, el ticket pasa a estado "Resuelto".
                                    </p>
                                    <textarea name="resolution" rows="5" required maxlength="5000" style="{{ $fieldStyle }}" placeholder="Describe cómo se resolvió el ticket...">{{ $ticket->resolution }}</textarea>
                                    <div style="{{ $modalFooter }}">
                                        <button type="button" @click="openModal = null" style="{{ $btnCancel }}">Cancelar</button>
                                        <button type="submit" style="{{ $btnPrimary }}">Marcar como resuelto</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Agregar archivo adjunto --}}
                    <div id="attachment-modal" x-show="openModal === 'attachment'" x-cloak style="{{ $modalOverlayWrap }}">
                        <div style="{{ $modalOverlayFlex }}" @click.self="openModal = null">
                            <div style="{{ $modalCard }}">
                                <div style="{{ $modalHeader }}">
                                    <h3 style="{{ $modalTitle }}">Agregar archivo adjunto</h3>
                                    <button type="button" @click="openModal = null" style="{{ $modalClose }}">&times;</button>
                                </div>
                                <form action="{{ route('tickets.attachments.store', $ticket) }}" method="POST" enctype="multipart/form-data" style="{{ $modalBody }}">
                                    @csrf
                                    <input type="file" name="file" required style="{{ $fieldStyle }}">
                                    <p style="font-size: 12px; color: #94a3b8; margin: 8px 0 0 0;">Máx. 10MB. Imágenes, PDF, Office, ZIP o TXT.</p>
                                    <div style="{{ $modalFooter }}">
                                        <button type="button" @click="openModal = null" style="{{ $btnCancel }}">Cancelar</button>
                                        <button type="submit" style="{{ $btnPrimary }}">Subir archivo</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Agregar recordatorio --}}
                    @if($canSeeReminders)
                        <div id="reminder-modal" x-show="openModal === 'reminder'" x-cloak style="{{ $modalOverlayWrap }}">
                            <div style="{{ $modalOverlayFlex }}" @click.self="openModal = null">
                                <div style="{{ $modalCard }}">
                                    <div style="{{ $modalHeader }}">
                                        <h3 style="{{ $modalTitle }}">Agregar recordatorio</h3>
                                        <button type="button" @click="openModal = null" style="{{ $modalClose }}">&times;</button>
                                    </div>
                                    <form action="{{ route('tickets.reminders.store', $ticket) }}" method="POST" style="{{ $modalBody }}">
                                        @csrf
                                        <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px;">Fecha y hora</label>
                                        <input type="datetime-local" name="remind_at" required style="{{ $fieldStyle }} margin-bottom: 12px;">
                                        <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px;">Nota</label>
                                        <textarea name="note" rows="3" required maxlength="1000" style="{{ $fieldStyle }}" placeholder="¿Qué hay que recordar?"></textarea>
                                        <p style="font-size: 12px; color: #94a3b8; margin: 8px 0 0 0;">Solo lo ves tú (el agente asignado) o un admin.</p>
                                        <div style="{{ $modalFooter }}">
                                            <button type="button" @click="openModal = null" style="{{ $btnCancel }}">Cancelar</button>
                                            <button type="submit" style="{{ $btnPrimary }}">Guardar recordatorio</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 5. Enviar para su aprobación --}}
                    @if($canSendForApproval)
                        <div id="send-approval-modal" x-show="openModal === 'sendApproval'" x-cloak style="{{ $modalOverlayWrap }}">
                            <div style="{{ $modalOverlayFlex }}" @click.self="openModal = null">
                                <div style="{{ $modalCard }}">
                                    <div style="{{ $modalHeader }}">
                                        <h3 style="{{ $modalTitle }}">Enviar para su aprobación</h3>
                                        <button type="button" @click="openModal = null" style="{{ $modalClose }}">&times;</button>
                                    </div>
                                    <form action="{{ route('tickets.approval.send', $ticket) }}" method="POST" style="{{ $modalBody }}">
                                        @csrf
                                        @if($flow)
                                            <p style="font-size: 13px; color: #374151; margin: 0 0 12px 0;">
                                                Esta categoría tiene el flujo <strong>"{{ $flow->name }}"</strong> configurado.
                                                Se notificará a <strong>{{ $flow->levels->first()?->approver?->name ?? 'sin aprobador asignado' }}</strong> (Nivel 1).
                                            </p>
                                        @else
                                            <p style="font-size: 13px; color: #374151; margin: 0 0 12px 0;">
                                                Esta categoría no tiene flujo de aprobación configurado. Selecciona un aprobador para este ticket:
                                            </p>
                                            <select name="approver_id" required style="{{ $fieldStyle }}">
                                                <option value="">Selecciona un aprobador...</option>
                                                @foreach($approvalCandidates as $candidate)
                                                    <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                        <p style="font-size: 12px; color: #94a3b8; margin: 12px 0 0 0;">
                                            El ticket quedará "Pendiente de aprobación" hasta que se resuelva.
                                        </p>
                                        <div style="{{ $modalFooter }}">
                                            <button type="button" @click="openModal = null" style="{{ $btnCancel }}">Cancelar</button>
                                            <button type="submit" style="{{ $btnPrimary }}">Enviar a aprobación</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 6. Iniciar/detener temporizador --}}
                    <div id="timer-modal" x-show="openModal === 'timer'" x-cloak style="{{ $modalOverlayWrap }}">
                        <div style="{{ $modalOverlayFlex }}" @click.self="openModal = null">
                            <div style="{{ $modalCard }}">
                                <div style="{{ $modalHeader }}">
                                    <h3 style="{{ $modalTitle }}">Temporizador</h3>
                                    <button type="button" @click="openModal = null" style="{{ $modalClose }}">&times;</button>
                                </div>
                                <div style="{{ $modalBody }}">
                                    @if($runningTimeLog)
                                        <p style="font-size: 13px; color: #374151; margin: 0 0 16px 0;">
                                            Corriendo desde <strong>{{ $runningTimeLog->started_at->format('d/m/Y H:i') }}</strong>.
                                        </p>
                                        <form action="{{ route('tickets.timelogs.stop', $ticket) }}" method="POST">
                                            @csrf
                                            <div style="{{ $modalFooter }} margin-top: 0;">
                                                <button type="button" @click="openModal = null" style="{{ $btnCancel }}">Cerrar</button>
                                                <button type="submit" style="{{ $btnDanger }}">Detener temporizador</button>
                                            </div>
                                        </form>
                                    @else
                                        <p style="font-size: 13px; color: #374151; margin: 0 0 16px 0;">
                                            No tienes un temporizador corriendo en este ticket.
                                        </p>
                                        <form action="{{ route('tickets.timelogs.start', $ticket) }}" method="POST">
                                            @csrf
                                            <div style="{{ $modalFooter }} margin-top: 0;">
                                                <button type="button" @click="openModal = null" style="{{ $btnCancel }}">Cerrar</button>
                                                <button type="submit" style="{{ $btnPrimary }}">Iniciar temporizador</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 7. Agregar etiqueta --}}
                    <div id="tag-modal" x-show="openModal === 'tag'" x-cloak style="{{ $modalOverlayWrap }}">
                        <div style="{{ $modalOverlayFlex }}" @click.self="openModal = null">
                            <div style="{{ $modalCard }}">
                                <div style="{{ $modalHeader }}">
                                    <h3 style="{{ $modalTitle }}">Agregar etiqueta</h3>
                                    <button type="button" @click="openModal = null" style="{{ $modalClose }}">&times;</button>
                                </div>
                                <form action="{{ route('tickets.tags.store', $ticket) }}" method="POST" style="{{ $modalBody }}">
                                    @csrf
                                    <input type="text" name="name" id="tag-name-input" required maxlength="50" style="{{ $fieldStyle }}" placeholder="Nombre de la etiqueta...">
                                    @if($allTags->isNotEmpty())
                                        <p style="font-size: 12px; color: #64748b; margin: 12px 0 6px 0;">O elige una existente:</p>
                                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                            @foreach($allTags as $existingTag)
                                                <button type="button"
                                                        onclick="document.getElementById('tag-name-input').value = {{ Js::from($existingTag->name) }}"
                                                        style="background-color: #eef2ff; color: #4338ca; border: none; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600; cursor: pointer;">
                                                    {{ $existingTag->name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div style="{{ $modalFooter }}">
                                        <button type="button" @click="openModal = null" style="{{ $btnCancel }}">Cancelar</button>
                                        <button type="submit" style="{{ $btnPrimary }}">Agregar etiqueta</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- 8. Cancelar solicitud --}}
                    <div id="cancel-modal" x-show="openModal === 'cancel'" x-cloak style="{{ $modalOverlayWrap }}">
                        <div style="{{ $modalOverlayFlex }}" @click.self="openModal = null">
                            <div style="{{ $modalCard }}">
                                <div style="{{ $modalHeader }}">
                                    <h3 style="{{ $modalTitle }}">Cancelar solicitud</h3>
                                    <button type="button" @click="openModal = null" style="{{ $modalClose }}">&times;</button>
                                </div>
                                <form action="{{ route('tickets.cancel', $ticket) }}" method="POST" style="{{ $modalBody }}">
                                    @csrf
                                    <p style="font-size: 12px; color: #991b1b; background-color: #fee2e2; border: 1px solid #fecaca; border-radius: 6px; padding: 8px 10px; margin: 0 0 12px 0;">
                                        El ticket pasará a estado "Cancelado" y se notificará al solicitante. Esta acción se puede revertir reabriendo el ticket.
                                    </p>
                                    <label style="display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px;">Motivo de la cancelación</label>
                                    <textarea name="reason" rows="3" required maxlength="1000" style="{{ $fieldStyle }}" placeholder="¿Por qué se cancela?"></textarea>
                                    <div style="{{ $modalFooter }}">
                                        <button type="button" @click="openModal = null" style="{{ $btnCancel }}">Volver</button>
                                        <button type="submit" style="{{ $btnDanger }}">Cancelar solicitud</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </main>

                {{-- ================= COLUMNA DERECHA ================= --}}
                <aside class="ticket-col-right">
                    <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                        <div style="background-color: #1e3a5f; padding: 12px 16px;">
                            <span style="color: #ffffff; font-size: 13px; font-weight: 600;">Propiedades</span>
                        </div>
                        <div style="padding: 16px;">

                            <div style="margin-bottom: 16px;">
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: .5px;">Estado</p>
                                <x-ticket-status-menu :ticket="$ticket" :approval-candidates="$approvalCandidates" />
                            </div>

                            <div style="margin-bottom: 16px;">
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: .5px;">Prioridad</p>
                                <span style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 700;">
                                    {{ $p['label'] }}
                                </span>
                            </div>

                            <div style="margin-bottom: 16px;">
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: .5px;">Fecha/Hora de Vencimiento</p>
                                <p style="font-size: 13px; font-weight: 500; color: {{ $isOverdue ? '#991b1b' : '#1e293b' }}; margin: 0;">
                                    {{ $ticket->due_date?->format('d/m/Y H:i') ?? '—' }}
                                </p>
                            </div>

                            <div style="margin-bottom: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0;" x-data="{ reassigning: false }">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <p style="font-size: 12px; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: .5px;">Técnico Asignado</p>
                                    @can('update', $ticket)
                                        <button type="button" @click="reassigning = !reassigning" style="background: none; border: none; color: #2563eb; font-size: 11px; font-weight: 600; cursor: pointer; padding: 0;">
                                            Reasignar
                                        </button>
                                    @endcan
                                </div>
                                <p style="font-size: 13px; font-weight: 500; color: #1e293b; margin: 0;" x-show="!reassigning">
                                    {{ $ticket->agent?->name ?? 'Sin asignar' }}
                                </p>
                                @can('update', $ticket)
                                    <form x-show="reassigning" x-cloak action="{{ route('tickets.update', $ticket) }}" method="POST" style="margin-top: 8px;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $ticket->status }}">
                                        <input type="hidden" name="priority" value="{{ $ticket->priority }}">
                                        <select name="assigned_to" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px; box-sizing: border-box; margin-bottom: 8px; font-size: 13px;">
                                            <option value="">Sin asignar</option>
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>
                                                    {{ $agent->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" style="width: 100%; background-color: #1e3a5f; color: #ffffff; padding: 6px; border-radius: 6px; border: none; cursor: pointer; font-size: 12px; font-weight: 600;">
                                            Guardar
                                        </button>
                                    </form>
                                @endcan
                            </div>

                            @if($totalMinutesLogged > 0 || $runningTimeLog)
                                <div style="margin-bottom: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                                    <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: .5px;">Tiempo Registrado</p>
                                    <p style="font-size: 13px; font-weight: 500; color: #1e293b; margin: 0;">
                                        {{ intdiv($totalMinutesLogged, 60) }}h {{ $totalMinutesLogged % 60 }}m
                                        @if($runningTimeLog)
                                            <span style="background-color: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-left: 4px;">Corriendo</span>
                                        @endif
                                    </p>
                                </div>
                            @endif

                            @if($canSeeReminders && $ticket->reminders->isNotEmpty())
                                <div style="margin-bottom: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                                    <p style="font-size: 12px; color: #64748b; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: .5px;">Recordatorios</p>
                                    @foreach($ticket->reminders as $reminder)
                                        <div style="margin-bottom: 8px;">
                                            <p style="font-size: 13px; font-weight: 600; color: #1e293b; margin: 0;">{{ $reminder->remind_at->format('d/m/Y H:i') }}</p>
                                            <p style="font-size: 12px; color: #64748b; margin: 0;">{{ $reminder->note }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @php
                                $approvalStatusColors ??= [
                                    'pending' => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Pendiente'],
                                    'approved' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Aprobado'],
                                    'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Rechazado'],
                                ];
                                $approvalsSorted = $ticket->approvals->sortBy(fn($a) => $a->approvalLevel->order ?? 0);
                                $currentApproval = $approvalsSorted->firstWhere('status', 'rejected')
                                    ?? $approvalsSorted->firstWhere('status', 'pending')
                                    ?? $approvalsSorted->last();
                            @endphp
                            <div style="margin-bottom: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <p style="font-size: 12px; color: #64748b; margin: 0; text-transform: uppercase; letter-spacing: .5px;">Aprobación</p>
                                    @can('update', $ticket)
                                        <a href="{{ route('tickets.edit', $ticket) }}" style="font-size: 11px; font-weight: 600; color: #2563eb; text-decoration: none;">
                                            Gestionar
                                        </a>
                                    @endcan
                                </div>
                                @if($currentApproval)
                                    @php $cs = $approvalStatusColors[$currentApproval->status]; @endphp
                                    <span style="background-color: {{ $cs['bg'] }}; color: {{ $cs['text'] }}; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 700;">
                                        {{ $cs['label'] }}
                                    </span>
                                    <p style="font-size: 13px; font-weight: 500; color: #1e293b; margin: 6px 0 0 0;">
                                        {{ $currentApproval->approvalLevel?->name ?? 'Ad-hoc' }}: {{ $currentApproval->effectiveApprover()?->name ?? 'Sin asignar' }}
                                    </p>
                                @else
                                    <p style="font-size: 13px; font-weight: 500; color: #94a3b8; margin: 0; font-style: italic;">Sin aprobación requerida</p>
                                @endif
                            </div>

                            <div style="margin-bottom: 16px;">
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: .5px;">Departamento/Grupo</p>
                                <p style="font-size: 13px; font-weight: 500; color: #1e293b; margin: 0;">{{ $site }}</p>
                            </div>

                            <div style="margin-bottom: 8px;">
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: .5px;">Sitio/Ubicación</p>
                                <p style="font-size: 13px; font-weight: 500; color: #1e293b; margin: 0;">{{ $site }}</p>
                            </div>

                            <details style="margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 12px;">
                                <summary style="cursor: pointer; font-size: 12px; font-weight: 600; color: #374151; text-transform: uppercase; letter-spacing: .5px;">
                                    Más propiedades
                                </summary>
                                <div style="padding-top: 12px;">
                                    <div style="margin-bottom: 12px;">
                                        <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0;">Categoría</p>
                                        <p style="font-size: 13px; font-weight: 500; color: #1e293b; margin: 0;">{{ $ticket->category->name ?? '—' }}</p>
                                    </div>
                                    <div style="margin-bottom: 12px;">
                                        <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0;">Subcategoría</p>
                                        @if($ticket->subcategory)
                                            <p style="font-size: 13px; font-weight: 500; color: #1e293b; margin: 0;">{{ $ticket->subcategory->name }}</p>
                                        @else
                                            <p style="font-size: 13px; color: #94a3b8; margin: 0; font-style: italic;">No aplica</p>
                                        @endif
                                    </div>
                                    <div>
                                        <p style="font-size: 12px; color: #64748b; margin: 0 0 4px 0;">Fecha de creación</p>
                                        <p style="font-size: 13px; font-weight: 500; color: #1e293b; margin: 0;">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </details>

                        </div>
                    </div>
                </aside>

            </div>

        </div>
    </div>
</x-app-layout>
