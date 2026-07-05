<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Ticket #{{ $ticket->id }}: {{ $ticket->title }}
        </h2>
    </x-slot>

    <div style="padding: 32px 0;">
        <div style="max-width: 960px; margin: 0 auto; padding: 0 24px;">

            @if(session('success'))
                <div style="margin-bottom: 16px; padding: 16px; background-color: #dcfce7; color: #166534; border-radius: 6px;">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $statusColors = [
                    'open' => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Abierto'],
                    'pending_approval' => ['bg' => '#e0e7ff', 'text' => '#3730a3', 'label' => 'Pendiente de Aprobación'],
                    'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Rechazado'],
                    'assigned' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'Asignado'],
                    'in_progress' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'En Progreso'],
                    'resolved' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Resuelto'],
                    'closed' => ['bg' => '#1f2937', 'text' => '#ffffff', 'label' => 'Cerrado'],
                ];
                $priorityColors = [
                    'low' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Baja'],
                    'medium' => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'Media'],
                    'high' => ['bg' => '#ffedd5', 'text' => '#9a3412', 'label' => 'Alta'],
                    'urgent' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Urgente'],
                ];
                $s = $statusColors[$ticket->status] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->status];
                $p = $priorityColors[$ticket->priority] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => $ticket->priority];
            @endphp

            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; margin-bottom: 24px;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Detalle del Ticket</span>
                </div>

                <div style="padding: 24px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
                        <div>
                            <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Estado</p>
                            <span style="background-color: {{ $s['bg'] }}; color: {{ $s['text'] }}; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                {{ $s['label'] }}
                            </span>
                        </div>
                        <div>
                            <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Prioridad</p>
                            <span style="background-color: {{ $p['bg'] }}; color: {{ $p['text'] }}; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                                {{ $p['label'] }}
                            </span>
                        </div>
                        <div>
                            <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Categoría</p>
                            <p style="font-weight: 500; color: #1e293b; margin: 0;">{{ $ticket->category->name }}</p>
                        </div>
                        <div>
                            <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Reportado por</p>
                            <p style="font-weight: 500; color: #1e293b; margin: 0;">{{ $ticket->user->name }}</p>
                        </div>
                        <div>
                            <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Asignado a</p>
                            <p style="font-weight: 500; color: #1e293b; margin: 0;">{{ $ticket->agent?->name ?? 'Sin asignar' }}</p>
                        </div>
                        <div>
                            <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Fecha de creación</p>
                            <p style="font-weight: 500; color: #1e293b; margin: 0;">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div style="margin-bottom: 24px;">
                        <p style="font-size: 13px; color: #64748b; margin: 0 0 6px 0;">Descripción</p>
                        <p style="color: #1e293b; margin: 0;">{{ $ticket->description }}</p>
                    </div>

                    @if($ticket->approvals->isNotEmpty())
                        <div style="margin-bottom: 24px;">
                            <p style="font-size: 13px; color: #64748b; margin: 0 0 10px 0;">Historial de Aprobación</p>
                            @php
                                $approvalStatusColors = [
                                    'pending' => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Pendiente'],
                                    'approved' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Aprobado'],
                                    'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Rechazado'],
                                ];
                            @endphp
                            @foreach($ticket->approvals->sortBy('approvalLevel.order') as $approval)
                                @php $as = $approvalStatusColors[$approval->status]; @endphp
                                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding: 10px 0;">
                                    <div>
                                        <p style="margin: 0; font-size: 14px; font-weight: 500; color: #1e293b;">{{ $approval->approvalLevel->name }}</p>
                                        @if($approval->approvedBy)
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

                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'agent')
                        <div style="border-top: 1px solid #e2e8f0; padding-top: 20px;">
                            @if($ticket->status === 'pending_approval')
                                <div style="background-color: #e0e7ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 16px; color: #3730a3; font-size: 14px;">
                                    Este ticket está pendiente de aprobación y no puede modificarse hasta que finalice el flujo de aprobación.
                                </div>
                            @else
                                <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;">
                                <h3 style="font-size: 14px; font-weight: 600; color: #1e3a5f; margin: 0 0 16px 0;">Actualizar Ticket</h3>
                                <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                                        <div>
                                            <label style="display: block; font-size: 13px; color: #4b5563; margin-bottom: 4px;">Estado</label>
                                            <select name="status" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Abierto</option>
                                                <option value="assigned" {{ $ticket->status == 'assigned' ? 'selected' : '' }}>Asignado</option>
                                                <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                                                <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resuelto</option>
                                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Cerrado</option>
                                                <option value="rejected" {{ $ticket->status == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 13px; color: #4b5563; margin-bottom: 4px;">Prioridad</label>
                                            <select name="priority" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                                <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>Baja</option>
                                                <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Media</option>
                                                <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>Alta</option>
                                                <option value="urgent" {{ $ticket->priority == 'urgent' ? 'selected' : '' }}>Urgente</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label style="display: block; font-size: 13px; color: #4b5563; margin-bottom: 4px;">Asignar a</label>
                                            <select name="assigned_to" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                                <option value="">Sin asignar</option>
                                                @foreach($agents as $agent)
                                                    <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>
                                                        {{ $agent->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit"
                                            style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                        Actualizar
                                    </button>
                                </form>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sección de comentarios -->
            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Comentarios</span>
                </div>

                <div style="padding: 24px;">
                    @forelse($ticket->comments as $comment)
                        @php
                            $initials = collect(explode(' ', trim($comment->user->name)))
                                ->filter()
                                ->map(fn($n) => mb_strtoupper(mb_substr($n, 0, 1)))
                                ->take(2)
                                ->implode('');
                        @endphp
                        <div style="display: flex; gap: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 16px;">
                            <div style="width: 36px; height: 36px; min-width: 36px; border-radius: 50%; background-color: #1e3a5f; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                                {{ $initials }}
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-size: 14px; font-weight: 600; color: #1e293b;">{{ $comment->user->name }}</span>
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
                            <textarea name="comment" rows="3"
                                      style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;"
                                      placeholder="Escribe tu comentario aquí..."></textarea>
                        </div>
                        <button type="submit"
                                style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                            Enviar comentario
                        </button>
                    </form>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 24px;">
                <a href="{{ route('tickets.index') }}"
                   style="background-color: #ffffff; color: #1e3a5f; border: 1px solid #1e3a5f; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 600;">
                    &larr; Volver a la lista
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
