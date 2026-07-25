<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Editar Ticket #{{ $ticket->id }}
        </h2>
    </x-slot>

    <div style="padding: 32px 0;">
        <div style="max-width: 720px; margin: 0 auto; padding: 0 24px;">
            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: #1e3a5f; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">{{ $ticket->title }}</span>
                </div>

                <div style="padding: 24px;">

                    @if(session('success'))
                        <div style="margin-bottom: 16px; padding: 16px; background-color: #dcfce7; color: #166534; border-radius: 6px;">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div style="margin-bottom: 16px; padding: 16px; background-color: #fee2e2; color: #991b1b; border-radius: 6px;">
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div style="margin-bottom: 20px;">
                        <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Descripción original</p>
                        <p style="color: #374151; margin: 0; white-space: pre-line;">{{ $ticket->description }}</p>
                    </div>

                    <form action="{{ route('tickets.update', $ticket) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Estado</label>
                                <select name="status" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Abierto</option>
                                    <option value="assigned" {{ $ticket->status == 'assigned' ? 'selected' : '' }}>Asignado</option>
                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                                    <option value="on_hold" {{ $ticket->status == 'on_hold' ? 'selected' : '' }}>En Espera</option>
                                    <option value="pending_approval" {{ $ticket->status == 'pending_approval' ? 'selected' : '' }}>Pendiente de Aprobación</option>
                                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resuelto</option>
                                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Cerrado</option>
                                    <option value="rejected" {{ $ticket->status == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                                    <option value="cancelled" {{ $ticket->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Prioridad</label>
                                <select name="priority" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                    <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>Baja</option>
                                    <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Media</option>
                                    <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>Alta</option>
                                    <option value="urgent" {{ $ticket->priority == 'urgent' ? 'selected' : '' }}>Urgente</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-bottom: 24px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">Asignar a</label>
                            @if($ticket->assigned_to)
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 6px 0;">
                                    Actualmente asignado a: <strong style="color: #1e293b;">{{ $ticket->agent->name }}</strong>
                                </p>
                            @else
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 6px 0;">Sin asignar actualmente.</p>
                            @endif
                            <select name="assigned_to" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                <option value="">Sin asignar</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ $ticket->assigned_to == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if(Auth::user()->role === 'agent')
                                <p style="font-size: 12px; color: #94a3b8; margin: 6px 0 0 0;">Solo puedes asignarte el ticket a ti mismo; asignarlo a otro técnico requiere un administrador.</p>
                            @else
                                <p style="font-size: 12px; color: #94a3b8; margin: 6px 0 0 0;">Puedes reasignar a otro técnico en cualquier momento.</p>
                            @endif
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 12px;">
                            <a href="{{ route('tickets.show', $ticket) }}"
                               style="padding: 10px 18px; background-color: #e5e7eb; color: #374151; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: 500;">
                                Cancelar
                            </a>
                            <button type="submit"
                                    style="background-color: #1e3a5f; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; font-size: 14px; font-weight: 500;">
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @php
                $approvalFlow = $ticket->category?->approvalFlow;
                $adhocApproval = $ticket->approvals->firstWhere('approval_level_id', null);
                $statusBadge = [
                    'pending'  => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'Pendiente'],
                    'approved' => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'Aprobado'],
                    'rejected' => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Rechazado'],
                ];
            @endphp

            <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; margin-top: 20px;">
                <div style="background-color: #3f2d1e; padding: 16px 24px;">
                    <span style="color: #ffffff; font-size: 14px; font-weight: 600;">Aprobación</span>
                </div>

                <div style="padding: 24px;">
                    @if($approvalFlow)
                        <p style="font-size: 13px; color: #64748b; margin: 0 0 16px 0;">
                            Flujo configurado para esta categoría: <strong style="color: #374151;">{{ $approvalFlow->name }}</strong>
                        </p>

                        @forelse($approvalFlow->levels as $level)
                            @php
                                $approval = $ticket->approvals->firstWhere('approval_level_id', $level->id);
                                $badge = $approval ? $statusBadge[$approval->status] : null;
                                $effectiveApprover = $approval?->effectiveApprover() ?? $level->approver;
                                $isOverridden = $approval && $approval->approver_id;
                            @endphp
                            <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px; margin-bottom: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="font-size: 13px; font-weight: 600; color: #1e293b;">
                                        Nivel {{ $level->order }} &middot; {{ $level->name }}
                                    </span>
                                    @if($badge)
                                        <span style="background-color: {{ $badge['bg'] }}; color: {{ $badge['text'] }}; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600;">
                                            {{ $badge['label'] }}
                                        </span>
                                    @endif
                                </div>

                                @if(! $approval)
                                    <p style="font-size: 12px; color: #94a3b8; margin: 0;">
                                        Este ticket no tiene esta aprobación activa (el flujo se configuró después de crearse el ticket).
                                    </p>
                                @elseif($approval->status === 'pending')
                                    <form action="{{ route('tickets.approval.assign', $ticket) }}" method="POST" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="approval_id" value="{{ $approval->id }}">
                                        <select name="approver_id" style="flex: 1; min-width: 200px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                            <option value="">Seleccionar aprobador...</option>
                                            @foreach($approvalCandidates as $candidate)
                                                <option value="{{ $candidate->id }}" {{ $effectiveApprover?->id == $candidate->id ? 'selected' : '' }}>
                                                    {{ $candidate->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" style="background-color: #1e3a5f; color: #ffffff; padding: 8px 14px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: 500; white-space: nowrap;">
                                            Guardar
                                        </button>
                                    </form>
                                    @if($isOverridden)
                                        <p style="font-size: 11px; color: #b45309; margin: 6px 0 0 0;">
                                            Ajustado solo para este ticket (por defecto del flujo: {{ $level->approver?->name ?? 'sin asignar' }}).
                                        </p>
                                    @endif
                                @else
                                    <p style="font-size: 13px; color: #374151; margin: 0;">
                                        {{ $badge['label'] }} por <strong>{{ $approval->approvedBy?->name ?? '—' }}</strong>
                                        @if($approval->approved_at)
                                            el {{ $approval->approved_at->format('d/m/Y H:i') }}
                                        @endif
                                    </p>
                                    @if($approval->comments)
                                        <p style="font-size: 12px; color: #64748b; margin: 4px 0 0 0; white-space: pre-line;">"{{ $approval->comments }}"</p>
                                    @endif
                                @endif
                            </div>
                        @empty
                        @endforelse
                    @else
                        <div style="background-color: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px;">
                            Esta categoría no tiene flujo de aprobación configurado.
                        </div>

                        @if($adhocApproval && $adhocApproval->status !== 'pending')
                            @php $badge = $statusBadge[$adhocApproval->status]; @endphp
                            <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px 16px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="font-size: 13px; font-weight: 600; color: #1e293b;">Aprobación ad-hoc</span>
                                    <span style="background-color: {{ $badge['bg'] }}; color: {{ $badge['text'] }}; padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600;">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                                <p style="font-size: 13px; color: #374151; margin: 0;">
                                    {{ $badge['label'] }} por <strong>{{ $adhocApproval->approvedBy?->name ?? '—' }}</strong>
                                    @if($adhocApproval->approved_at)
                                        el {{ $adhocApproval->approved_at->format('d/m/Y H:i') }}
                                    @endif
                                </p>
                                @if($adhocApproval->comments)
                                    <p style="font-size: 12px; color: #64748b; margin: 4px 0 0 0; white-space: pre-line;">"{{ $adhocApproval->comments }}"</p>
                                @endif
                            </div>
                        @else
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">
                                Asignar aprobador solo para este ticket (opcional)
                            </label>
                            @if($adhocApproval)
                                <p style="font-size: 12px; color: #64748b; margin: 0 0 6px 0;">
                                    Actualmente: <strong style="color: #1e293b;">{{ $adhocApproval->approver?->name }}</strong>
                                    <span style="background-color: {{ $statusBadge['pending']['bg'] }}; color: {{ $statusBadge['pending']['text'] }}; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 600; margin-left: 6px;">Pendiente</span>
                                </p>
                            @endif
                            <form action="{{ route('tickets.approval.assign', $ticket) }}" method="POST" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                @csrf
                                @method('PUT')
                                <select name="approver_id" style="flex: 1; min-width: 200px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box;">
                                    <option value="">Sin aprobador (no requiere aprobación)</option>
                                    @foreach($approvalCandidates as $candidate)
                                        <option value="{{ $candidate->id }}" {{ $adhocApproval?->approver_id == $candidate->id ? 'selected' : '' }}>
                                            {{ $candidate->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" style="background-color: #1e3a5f; color: #ffffff; padding: 8px 14px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: 500; white-space: nowrap;">
                                    Guardar aprobación
                                </button>
                            </form>
                            <p style="font-size: 12px; color: #94a3b8; margin: 6px 0 0 0;">
                                Si no se asigna nadie, el ticket sigue su curso normal sin requerir aprobación.
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
