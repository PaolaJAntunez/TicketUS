<?php

namespace App\Services;

use App\Models\Ticket;

/**
 * Única fuente de verdad de qué cambios de estado de ticket son válidos.
 * La usan: Kanban (drag & drop), el selector rápido de estado (tabla y
 * detalle), y los guards de resolution()/cancel()/sendForApproval() — así
 * la regla es idéntica en todos lados en vez de reimplementarse cada vez.
 *
 * No cubre las transiciones automáticas del sistema (pending_approval ->
 * open/rejected, que dispara TicketApprovalService al aprobar/rechazar) ni
 * la Reapertura (siempre a "open", con motivo obligatorio, desde cualquier
 * estado final — ver TicketPolicy::reopen()).
 */
class TicketStatusService
{
    /**
     * Transiciones "simples": sin dato adicional, aplican directo vía
     * TicketController::updateStatus() (Kanban + selector rápido).
     */
    public const SIMPLE_TRANSITIONS = [
        'open' => ['in_progress'],
        'assigned' => ['in_progress'],
        'in_progress' => [],
        'on_hold' => ['in_progress'],
        'pending_approval' => [],
    ];

    /**
     * Transiciones que requieren datos adicionales y pasan por una acción
     * dedicada (modal correspondiente), no por un PATCH genérico. Clave =
     * estado destino, valor = estados de origen desde los que es válida.
     */
    public const GUARDED_TRANSITIONS = [
        // Requiere texto de resolución -> TicketController::resolution()
        'resolved' => ['in_progress', 'assigned'],
        // Requiere comentario obligatorio -> TicketController::hold()
        'on_hold' => ['in_progress', 'assigned'],
        // Requiere aprobador (si la categoría no tiene flujo) -> ApprovalController::sendForApproval()
        'pending_approval' => ['open', 'assigned', 'in_progress'],
        // Requiere motivo -> TicketController::cancel()
        'cancelled' => ['open', 'assigned', 'in_progress', 'pending_approval', 'on_hold'],
    ];

    /**
     * Qué modal/acción dedicada resuelve cada transición guardada. Se usa
     * en la UI para decidir qué modal abrir al elegir cada opción.
     */
    public const MODALS = [
        'resolved' => 'resolution',
        'on_hold' => 'hold',
        'pending_approval' => 'pending_approval',
        'cancelled' => 'cancelled',
    ];

    public function canTransition(string $from, string $to): bool
    {
        return $this->isSimple($from, $to) || $this->isGuarded($from, $to);
    }

    public function isSimple(string $from, string $to): bool
    {
        return in_array($to, self::SIMPLE_TRANSITIONS[$from] ?? [], true);
    }

    public function isGuarded(string $from, string $to): bool
    {
        return in_array($from, self::GUARDED_TRANSITIONS[$to] ?? [], true);
    }

    /**
     * Todas las transiciones válidas desde el estado ACTUAL del ticket, para
     * armar el selector rápido. Cada entrada trae el estado destino y, si
     * aplica, qué modal debe abrirse antes de confirmar (null = transición
     * directa). Recibe el Ticket (no solo el string de estado) porque
     * "pending_approval" además depende de si el ticket ya pasó por un
     * proceso de aprobación (misma regla que sendForApproval()) — así esa
     * excepción también queda centralizada acá en vez de repetirse en cada
     * controlador y en el componente Blade.
     *
     * Se recalcula server-side después de cada cambio de estado y viaja en
     * la respuesta JSON (ver TicketController::updateStatus()/hold()/etc.),
     * para que el selector rápido pueda encadenar otro cambio sin recargar
     * la página ni duplicar esta tabla de transiciones en JavaScript.
     *
     * @return array<int, array{status: string, requires_modal: ?string}>
     */
    public function validTargets(Ticket $ticket): array
    {
        $from = $ticket->status;
        $targets = [];

        foreach (self::SIMPLE_TRANSITIONS[$from] ?? [] as $to) {
            $targets[$to] = ['status' => $to, 'requires_modal' => null];
        }

        foreach (self::GUARDED_TRANSITIONS as $to => $froms) {
            if (! in_array($from, $froms, true)) {
                continue;
            }

            if ($to === 'pending_approval' && $ticket->approvals()->exists()) {
                continue;
            }

            // No se puede resolver con un nivel (o aprobador ad-hoc) sin votar
            // todavía, aunque el status del ticket no sea "pending_approval"
            // (ver Ticket::hasPendingApproval()).
            if ($to === 'resolved' && $ticket->hasPendingApproval()) {
                continue;
            }

            $targets[$to] = ['status' => $to, 'requires_modal' => self::MODALS[$to]];
        }

        return array_values($targets);
    }
}
