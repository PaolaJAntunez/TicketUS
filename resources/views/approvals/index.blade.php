<x-app-layout>
    <x-slot name="header">
        <h2 style="font-weight: 600; font-size: 20px; color: #ffffff; margin: 0;">
            Aprobaciones Pendientes
        </h2>
    </x-slot>

    <div style="padding: 32px 0;">
        <div style="max-width: 960px; margin: 0 auto; padding: 0 24px;">

            @if(session('success'))
                <div style="margin-bottom: 16px; padding: 16px; background-color: #dcfce7; color: #166534; border-radius: 6px;">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($approvals as $approval)
                <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                    <div style="background-color: #1e3a5f; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #ffffff; font-size: 14px; font-weight: 600;">
                            Ticket #{{ $approval->ticket->id }}: {{ $approval->ticket->title }}
                        </span>
                        <span style="background-color: rgba(255,255,255,0.15); color: #ffffff; border: 1px solid rgba(255,255,255,0.4); padding: 3px 10px; border-radius: 9999px; font-size: 11px; font-weight: 600;">
                            {{ $approval->approvalLevel->name }}
                        </span>
                    </div>

                    <div style="padding: 24px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div>
                                <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Solicitante</p>
                                <p style="font-weight: 500; color: #1e293b; margin: 0;">{{ $approval->ticket->user->name }}</p>
                            </div>
                            <div>
                                <p style="font-size: 13px; color: #64748b; margin: 0 0 4px 0;">Categoría</p>
                                <p style="font-weight: 500; color: #1e293b; margin: 0;">{{ $approval->ticket->category->name }}</p>
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <p style="font-size: 13px; color: #64748b; margin: 0 0 6px 0;">Descripción</p>
                            <p style="color: #1e293b; margin: 0;">{{ $approval->ticket->description }}</p>
                        </div>

                        <div style="margin-bottom: 8px;">
                            <a href="{{ route('tickets.show', $approval->ticket) }}" style="font-size: 13px; color: #2563eb; text-decoration: none; font-weight: 500;">
                                Ver ticket completo &rarr;
                            </a>
                        </div>

                        <div style="border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 4px;">
                                Comentario (opcional)
                            </label>
                            <textarea form="approve-form-{{ $approval->id }}" name="comments" rows="2"
                                      style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px; box-sizing: border-box; margin-bottom: 16px;"
                                      placeholder="Agrega un comentario para tu decisión..."
                                      id="comments-{{ $approval->id }}"></textarea>

                            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                                <form id="reject-form-{{ $approval->id }}"
                                      action="{{ route('approvals.reject', [$approval->ticket, $approval->approvalLevel]) }}"
                                      method="POST"
                                      onsubmit="document.getElementById('reject-comments-{{ $approval->id }}').value = document.getElementById('comments-{{ $approval->id }}').value; return confirm('¿Rechazar este ticket?');">
                                    @csrf
                                    <input type="hidden" name="comments" id="reject-comments-{{ $approval->id }}">
                                    <button type="submit"
                                            style="background-color: #ffffff; color: #991b1b; border: 1px solid #991b1b; padding: 10px 18px; border-radius: 6px; font-size: 14px; font-weight: 500; cursor: pointer;">
                                        Rechazar
                                    </button>
                                </form>

                                <form id="approve-form-{{ $approval->id }}"
                                      action="{{ route('approvals.approve', [$approval->ticket, $approval->approvalLevel]) }}"
                                      method="POST">
                                    @csrf
                                    <button type="submit"
                                            style="background-color: #2563eb; color: #ffffff; padding: 10px 18px; border-radius: 6px; border: none; font-size: 14px; font-weight: 500; cursor: pointer;">
                                        Aprobar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; padding: 48px; text-align: center;">
                    <p style="color: #64748b; font-size: 14px; margin: 0;">No tienes aprobaciones pendientes.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
