@props(['ticket', 'approvalCandidates'])

{{-- Selector rápido de estado: badge clickeable + dropdown con SOLO las
     transiciones válidas desde el estado actual (TicketStatusService), y un
     modal por transición que requiere dato adicional (en espera, resolución,
     cancelar, enviar a aprobación). Todo vía fetch, sin recargar la página.
     Autocontenido a propósito (no reutiliza los modales de "Acciones" de
     tickets/show.blade.php) para que funcione igual en la tabla de listado
     (una instancia por fila) y en el detalle. --}}
{{-- x-show manipula el atributo "style" vía JS (el.style.display = ...), y al
     hacerlo el navegador re-serializa TODO el atributo normalizando colores
     (#ffffff -> rgb(255, 255, 255)). Eso rompe el hack global de modo oscuro
     de navigation.blade.php, que matchea por el texto literal
     "background-color: #ffffff" en el atributo. Por eso este componente NO
     puede depender del hack global para nada que viva dentro de un x-show:
     usa clases propias + "html.dark ..." (mismo patrón que kanban.blade.php). --}}
<style>
    .ts-dropdown-panel { background-color:#ffffff; border:1px solid #e2e8f0; }
    html.dark .ts-dropdown-panel { background-color:#1e293b !important; border-color:#334155 !important; }

    .ts-dropdown-item { color:#374151; }
    .ts-dropdown-item:hover { background-color:#f1f5f9; }
    html.dark .ts-dropdown-item { color:#e2e8f0 !important; }
    html.dark .ts-dropdown-item:hover { background-color:#334155 !important; }

    .ts-modal-box { background-color:#ffffff; }
    html.dark .ts-modal-box { background-color:#1e293b !important; }

    .ts-modal-header { border-bottom:1px solid #e2e8f0; }
    html.dark .ts-modal-header { border-color:#334155 !important; }

    .ts-modal-title { color:#1e293b; }
    html.dark .ts-modal-title { color:#f1f5f9 !important; }

    .ts-modal-close { color:#64748b; }
    html.dark .ts-modal-close { color:#94a3b8 !important; }

    .ts-modal-text { color:#374151; }
    html.dark .ts-modal-text { color:#cbd5e1 !important; }

    .ts-hint-neutral { color:#64748b; }
    html.dark .ts-hint-neutral { color:#94a3b8 !important; }

    .ts-hint-warning { color:#9a3412; background-color:#fff7ed; border:1px solid #fed7aa; }
    html.dark .ts-hint-warning { color:#fed7aa !important; background-color:#7c2d12 !important; border-color:#9a3412 !important; }

    .ts-hint-danger { color:#991b1b; background-color:#fee2e2; border:1px solid #fecaca; }
    html.dark .ts-hint-danger { color:#fecaca !important; background-color:#7f1d1d !important; border-color:#991b1b !important; }

    .ts-textarea, .ts-select { border:1px solid #cbd5e1; background-color:#ffffff; color:#1e293b; }
    html.dark .ts-textarea, html.dark .ts-select { background-color:#0f172a !important; border-color:#475569 !important; color:#f1f5f9 !important; }

    .ts-btn-secondary { background-color:#e5e7eb; color:#374151; }
    html.dark .ts-btn-secondary { background-color:#334155 !important; color:#e2e8f0 !important; }

    .ts-error-toast { background-color:#fee2e2; color:#991b1b; }
    html.dark .ts-error-toast { background-color:#7f1d1d !important; color:#fecaca !important; }
</style>
@php
    $statusService = app(\App\Services\TicketStatusService::class);
    $authUser = auth()->user();
    $canManage = $authUser->role === 'admin' || (int) $ticket->assigned_to === (int) $authUser->id;

    // validTargets() ya filtra "pending_approval" si el ticket alguna vez
    // pasó por aprobación (misma regla que sendForApproval()).
    $targets = $canManage ? $statusService->validTargets($ticket) : [];

    $statusMeta = [
        'open' => ['label' => 'Abierto', 'bg' => '#fee2e2', 'text' => '#991b1b'],
        'assigned' => ['label' => 'Asignado', 'bg' => '#dbeafe', 'text' => '#1e40af'],
        'in_progress' => ['label' => 'En Proceso', 'bg' => '#dbeafe', 'text' => '#1e40af'],
        'on_hold' => ['label' => 'En Espera', 'bg' => '#fed7aa', 'text' => '#9a3412'],
        'pending_approval' => ['label' => 'Pendiente de Aprobación', 'bg' => '#fef9c3', 'text' => '#854d0e'],
        'resolved' => ['label' => 'Resuelto', 'bg' => '#e5e7eb', 'text' => '#374151'],
        'closed' => ['label' => 'Cerrado', 'bg' => '#e5e7eb', 'text' => '#374151'],
        'rejected' => ['label' => 'Rechazado', 'bg' => '#fecaca', 'text' => '#7f1d1d'],
        'cancelled' => ['label' => 'Cancelado', 'bg' => '#f3e8ff', 'text' => '#6b21a8'],
    ];

    $current = $statusMeta[$ticket->status] ?? ['label' => ucfirst($ticket->status), 'bg' => '#e5e7eb', 'text' => '#374151'];
    $hasFlow = (bool) ($ticket->category->approvalFlow ?? null);
@endphp

<div x-data="{
        ticketId: {{ $ticket->id }},
        currentStatus: {{ Js::from($ticket->status) }},
        currentLabel: {{ Js::from($current['label']) }},
        currentBg: {{ Js::from($current['bg']) }},
        currentText: {{ Js::from($current['text']) }},
        open: false,
        modal: null,
        comment: '',
        reason: '',
        resolutionText: '',
        approverId: '',
        saving: false,
        errorMsg: '',
        targets: {{ Js::from($targets) }},
        statusMeta: {{ Js::from($statusMeta) }},

        pickTarget(target) {
            this.open = false;
            this.errorMsg = '';

            if (! target.requires_modal) {
                this.applySimple(target.status);
                return;
            }

            this.modal = target.requires_modal;
            this.comment = '';
            this.reason = '';
            this.resolutionText = '';
            this.approverId = '';
        },

        closeModal() {
            this.modal = null;
            this.errorMsg = '';
        },

        async applySimple(status) {
            this.saving = true;
            const result = await this.postJson('/tickets/' + this.ticketId + '/status', { status: status }, 'PATCH');
            this.saving = false;

            if (result.ok) {
                this.updateCurrent(result.data);
            } else {
                this.showError(result.data.message);
            }
        },

        async submitHold() {
            if (! this.comment.trim()) { this.errorMsg = 'El motivo es obligatorio.'; return; }
            this.saving = true;
            const result = await this.postJson('/tickets/' + this.ticketId + '/hold', { comment: this.comment }, 'PATCH');
            this.saving = false;

            if (result.ok) {
                this.updateCurrent(result.data);
                this.closeModal();
            } else {
                this.showError(result.data.message);
            }
        },

        async submitResolution() {
            if (! this.resolutionText.trim()) { this.errorMsg = 'La resolución es obligatoria.'; return; }
            this.saving = true;
            const result = await this.postJson('/tickets/' + this.ticketId + '/resolution', { resolution: this.resolutionText }, 'PUT');
            this.saving = false;

            if (result.ok) {
                this.updateCurrent(result.data);
                this.closeModal();
            } else {
                this.showError(result.data.message);
            }
        },

        async submitCancel() {
            if (! this.reason.trim()) { this.errorMsg = 'El motivo es obligatorio.'; return; }
            this.saving = true;
            const result = await this.postJson('/tickets/' + this.ticketId + '/cancel', { reason: this.reason }, 'POST');
            this.saving = false;

            if (result.ok) {
                this.updateCurrent(result.data);
                this.closeModal();
            } else {
                this.showError(result.data.message);
            }
        },

        async submitSendApproval() {
            this.saving = true;
            const result = await this.postJson('/tickets/' + this.ticketId + '/send-for-approval', { approver_id: this.approverId || null }, 'POST');
            this.saving = false;

            if (result.ok) {
                this.updateCurrent(result.data);
                this.closeModal();
            } else {
                this.showError(result.data.message);
            }
        },

        showError(message) {
            this.errorMsg = message || 'No se pudo actualizar el estado.';
            setTimeout(() => { this.errorMsg = ''; }, 6000);
        },

        // data viene del backend: { status, targets }. Los nuevos targets
        // (transiciones válidas desde el estado recién aplicado) los calcula
        // TicketStatusService server-side, así el dropdown queda listo para
        // encadenar otro cambio sin recargar la página y sin duplicar la
        // tabla de transiciones en JavaScript.
        updateCurrent(data) {
            const status = data.status;
            this.currentStatus = status;
            const meta = this.statusMeta[status] || { label: status, bg: '#e5e7eb', text: '#374151' };
            this.currentLabel = meta.label;
            this.currentBg = meta.bg;
            this.currentText = meta.text;
            this.targets = data.targets || [];
        },

        async postJson(url, body, method) {
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
        }
     }"
     @keydown.escape.window="modal = null"
     style="position: relative; display: inline-flex; align-items: center; gap: 2px;">

    <button type="button" @click="targets.length ? (open = ! open) : null"
            :style="'background-color:' + currentBg + '; color:' + currentText + '; padding:4px 10px; border-radius:9999px; font-size:12px; font-weight:700; border:none; white-space:nowrap; ' + (targets.length ? 'cursor:pointer;' : 'cursor:default;')"
            x-text="currentLabel">
    </button>

    <i class="ti ti-chevron-down" x-show="targets.length" style="font-size:10px; color:#94a3b8; cursor:pointer;" @click="open = ! open"></i>

    <div x-show="open" x-cloak @click.outside="open = false" class="ts-dropdown-panel"
         style="position: absolute; top: 100%; left: 0; margin-top: 4px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,.15); min-width: 200px; z-index: 45; overflow: hidden;">
        <template x-for="target in targets" :key="target.status">
            <button type="button" @click="pickTarget(target)" class="ts-dropdown-item"
                    style="display:flex; align-items:center; justify-content:space-between; gap:8px; width:100%; text-align:left; padding:9px 12px; font-size:13px; background:none; border:none; cursor:pointer;">
                <span x-text="statusMeta[target.status] ? statusMeta[target.status].label : target.status"></span>
                <i class="ti ti-message-2" x-show="target.requires_modal" title="Requiere un motivo" style="font-size:12px; color:#94a3b8;"></i>
            </button>
        </template>
    </div>

    <div x-show="errorMsg" x-cloak class="ts-error-toast"
         style="position:absolute; top:100%; left:0; margin-top:4px; padding:6px 10px; border-radius:6px; font-size:11px; max-width:240px; z-index:46; box-shadow:0 2px 6px rgba(0,0,0,.15);"
         x-text="errorMsg">
    </div>

    {{-- Modal compartido: solo una plantilla activa a la vez según "modal" --}}
    <div x-show="modal" x-cloak style="position: fixed; inset: 0; z-index: 70;">
        <div style="width:100%; height:100%; background-color:rgba(15,23,42,0.6); display:flex; align-items:center; justify-content:center; padding:24px; box-sizing:border-box;" @click.self="closeModal()">
            <div class="ts-modal-box" style="border-radius:10px; max-width:460px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 20px 40px rgba(0,0,0,.25);">

                <template x-if="modal === 'hold'">
                    <div>
                        <div class="ts-modal-header" style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                            <h3 class="ts-modal-title" style="margin:0; font-size:15px; font-weight:700;">Poner en espera</h3>
                            <button type="button" @click="closeModal()" class="ts-modal-close" style="background:none; border:none; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
                        </div>
                        <div style="padding:20px;">
                            <p class="ts-hint-warning" style="font-size:12px; border-radius:6px; padding:8px 10px; margin:0 0 12px 0;">
                                Indica el motivo por el que se pausa el ticket (ej. esperando información del solicitante, esperando una pieza).
                            </p>
                            <textarea x-model="comment" rows="4" maxlength="1000" placeholder="Motivo obligatorio..." class="ts-textarea"
                                      style="width:100%; border-radius:6px; padding:8px; box-sizing:border-box; font-size:14px;"></textarea>
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                                <button type="button" @click="closeModal()" class="ts-btn-secondary" style="padding:8px 16px; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;">Cancelar</button>
                                <button type="button" @click="submitHold()" :disabled="saving || ! comment.trim()"
                                        style="padding:8px 16px; background-color:#9a3412; color:#ffffff; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;"
                                        x-text="saving ? 'Guardando...' : 'Poner en espera'"></button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="modal === 'resolution'">
                    <div>
                        <div class="ts-modal-header" style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                            <h3 class="ts-modal-title" style="margin:0; font-size:15px; font-weight:700;">Introducir resolución</h3>
                            <button type="button" @click="closeModal()" class="ts-modal-close" style="background:none; border:none; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
                        </div>
                        <div style="padding:20px;">
                            <p class="ts-hint-neutral" style="font-size:12px; margin:0 0 12px 0;">Al guardar, el ticket pasa a estado &quot;Resuelto&quot;.</p>
                            <textarea x-model="resolutionText" rows="5" maxlength="5000" placeholder="Describe cómo se resolvió el ticket..." class="ts-textarea"
                                      style="width:100%; border-radius:6px; padding:8px; box-sizing:border-box; font-size:14px;"></textarea>
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                                <button type="button" @click="closeModal()" class="ts-btn-secondary" style="padding:8px 16px; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;">Cancelar</button>
                                <button type="button" @click="submitResolution()" :disabled="saving || ! resolutionText.trim()"
                                        style="padding:8px 16px; background-color:#1e3a5f; color:#ffffff; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;"
                                        x-text="saving ? 'Guardando...' : 'Marcar como resuelto'"></button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="modal === 'cancelled'">
                    <div>
                        <div class="ts-modal-header" style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                            <h3 class="ts-modal-title" style="margin:0; font-size:15px; font-weight:700;">Cancelar solicitud</h3>
                            <button type="button" @click="closeModal()" class="ts-modal-close" style="background:none; border:none; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
                        </div>
                        <div style="padding:20px;">
                            <p class="ts-hint-danger" style="font-size:12px; border-radius:6px; padding:8px 10px; margin:0 0 12px 0;">
                                El ticket pasará a estado &quot;Cancelado&quot;. Esta acción se puede revertir reabriendo el ticket.
                            </p>
                            <textarea x-model="reason" rows="3" maxlength="1000" placeholder="Motivo de la cancelación..." class="ts-textarea"
                                      style="width:100%; border-radius:6px; padding:8px; box-sizing:border-box; font-size:14px;"></textarea>
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                                <button type="button" @click="closeModal()" class="ts-btn-secondary" style="padding:8px 16px; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;">Volver</button>
                                <button type="button" @click="submitCancel()" :disabled="saving || ! reason.trim()"
                                        style="padding:8px 16px; background-color:#991b1b; color:#ffffff; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;"
                                        x-text="saving ? 'Guardando...' : 'Cancelar solicitud'"></button>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="modal === 'pending_approval'">
                    <div>
                        <div class="ts-modal-header" style="padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                            <h3 class="ts-modal-title" style="margin:0; font-size:15px; font-weight:700;">Enviar para su aprobación</h3>
                            <button type="button" @click="closeModal()" class="ts-modal-close" style="background:none; border:none; cursor:pointer; font-size:20px; line-height:1;">&times;</button>
                        </div>
                        <div style="padding:20px;">
                            @if($hasFlow)
                                <p class="ts-modal-text" style="font-size:13px; margin:0 0 12px 0;">
                                    Esta categoría tiene un flujo de aprobación configurado. Se notificará al primer nivel.
                                </p>
                            @else
                                <p class="ts-modal-text" style="font-size:13px; margin:0 0 12px 0;">Esta categoría no tiene flujo configurado. Selecciona un aprobador:</p>
                                <select x-model="approverId" class="ts-select" style="width:100%; border-radius:6px; padding:8px; box-sizing:border-box; font-size:14px;">
                                    <option value="">Selecciona un aprobador...</option>
                                    @foreach($approvalCandidates as $candidate)
                                        <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <p class="ts-hint-neutral" style="font-size:12px; margin:12px 0 0 0;">El ticket quedará &quot;Pendiente de Aprobación&quot; hasta que se resuelva.</p>
                            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:16px;">
                                <button type="button" @click="closeModal()" class="ts-btn-secondary" style="padding:8px 16px; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;">Cancelar</button>
                                <button type="button" @click="submitSendApproval()" :disabled="saving"
                                        style="padding:8px 16px; background-color:#1e3a5f; color:#ffffff; border-radius:6px; border:none; cursor:pointer; font-size:13px; font-weight:600;"
                                        x-text="saving ? 'Enviando...' : 'Enviar a aprobación'"></button>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>
</div>
