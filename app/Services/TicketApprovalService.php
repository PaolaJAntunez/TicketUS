<?php

namespace App\Services;

use App\Models\ApprovalLevel;
use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketApproval;
use App\Models\User;
use App\Notifications\ApprovalRequestedNotification;
use App\Notifications\TicketRejectedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Owns the approval workflow's business rules: level ordering, self-approval,
 * duplicate votes, and the terminal effect of a rejection. Kept out of the
 * controller because these rules interact (e.g. the order check needs the
 * full set of sibling levels) in ways a Form Request can't express cleanly.
 */
class TicketApprovalService
{
    public function __construct(protected TicketStatusService $statusService)
    {
    }

    public function approve(Ticket $ticket, ApprovalLevel $level, User $actor, ?string $comments): TicketApproval
    {
        $approval = $this->guardedPendingApproval($ticket, $level, $actor);

        $approval->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'comments' => $comments,
            'approved_at' => now(),
        ]);

        TicketActivityLog::record($ticket, $actor, 'approved', $comments);

        $nextLevel = ApprovalLevel::where('approval_flow_id', $level->approval_flow_id)
            ->where('order', '>', $level->order)
            ->orderBy('order')
            ->first();

        if ($nextLevel) {
            $nextApproval = TicketApproval::where('ticket_id', $ticket->id)
                ->where('approval_level_id', $nextLevel->id)
                ->first();

            SafeNotifier::send($nextApproval?->effectiveApprover(), new ApprovalRequestedNotification($ticket, $nextLevel));
        } else {
            $oldStatus = $ticket->status;
            $ticket->update(['status' => 'open']);
            SafeNotifier::send($ticket->user, new TicketStatusUpdatedNotification($ticket, $oldStatus, $ticket->status));
        }

        return $approval->refresh();
    }

    public function reject(Ticket $ticket, ApprovalLevel $level, User $actor, string $comments): TicketApproval
    {
        $approval = $this->guardedPendingApproval($ticket, $level, $actor);

        $approval->update([
            'status' => 'rejected',
            'approved_by' => $actor->id,
            'comments' => $comments,
            'approved_at' => now(),
        ]);

        $ticket->update(['status' => 'rejected']);

        TicketActivityLog::record($ticket, $actor, 'rejected', $comments);

        SafeNotifier::send($ticket->user, new TicketRejectedNotification($ticket, $comments));

        return $approval->refresh();
    }

    public function reopen(Ticket $ticket, User $actor, string $reason): Ticket
    {
        if (! in_array($ticket->status, TicketStatusService::TERMINAL_STATES, true)) {
            throw ValidationException::withMessages([
                'reason' => 'Solo se puede reabrir un ticket rechazado, resuelto, cerrado o cancelado.',
            ]);
        }

        $oldStatus = $ticket->status;

        $ticket->update([
            'status' => 'open',
            'reopened_reason' => $reason,
        ]);

        TicketActivityLog::record($ticket, $actor, 'reopened', $reason);

        SafeNotifier::send($ticket->user, new TicketStatusUpdatedNotification($ticket, $oldStatus, $ticket->status));

        return $ticket->refresh();
    }

    /**
     * Resolves the pending TicketApproval for this ticket/level while enforcing:
     * #1 the previous levels must already be approved,
     * #2 the creator cannot approve/reject their own ticket,
     * #3/#4 a rejected/closed ticket can no longer be acted on (reopen() is the only way back),
     * #5 the same user cannot vote twice on the same ticket.
     */
    protected function guardedPendingApproval(Ticket $ticket, ApprovalLevel $level, User $actor): TicketApproval
    {
        return DB::transaction(function () use ($ticket, $level, $actor) {
            $ticket->refresh();

            if ($ticket->status !== 'pending_approval') {
                throw ValidationException::withMessages([
                    'ticket' => 'Este ticket ya no requiere tu aprobación: alguien más ya lo resolvió, o cambió de estado por otro camino (estado actual: '.TicketStatusService::label($ticket->status).').',
                ]);
            }

            if ($actor->id === $ticket->user_id) {
                throw ValidationException::withMessages([
                    'actor' => 'El creador del ticket no puede aprobar ni rechazar su propio ticket.',
                ]);
            }

            $hasUnapprovedPriorLevel = TicketApproval::where('ticket_id', $ticket->id)
                ->where('status', '!=', 'approved')
                ->whereHas('approvalLevel', fn ($q) => $q->where('order', '<', $level->order))
                ->exists();

            if ($hasUnapprovedPriorLevel) {
                throw ValidationException::withMessages([
                    'level' => 'No puedes actuar sobre este nivel: los aprobadores anteriores todavía no aprueban.',
                ]);
            }

            $alreadyVoted = TicketApproval::where('ticket_id', $ticket->id)
                ->where('approved_by', $actor->id)
                ->exists();

            if ($alreadyVoted) {
                throw ValidationException::withMessages([
                    'actor' => 'Ya has votado sobre este ticket.',
                ]);
            }

            $approval = TicketApproval::where('ticket_id', $ticket->id)
                ->where('approval_level_id', $level->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $approval) {
                throw ValidationException::withMessages([
                    'level' => 'Este nivel de aprobación ya no está pendiente.',
                ]);
            }

            return $approval;
        });
    }

    /**
     * Ajusta el aprobador de un nivel del flujo solo para este ticket, sin tocar
     * la configuración general del flujo. Solo se permite mientras el nivel
     * siga pendiente (regla #5 del pedido: un nivel ya resuelto no se toca).
     */
    public function reassignLevelApprover(Ticket $ticket, TicketApproval $approval, User $actor, User $newApprover): TicketApproval
    {
        if ((int) $approval->ticket_id !== (int) $ticket->id) {
            throw ValidationException::withMessages([
                'approval' => 'Esta aprobación no pertenece a este ticket.',
            ]);
        }

        if ($approval->status !== 'pending') {
            throw ValidationException::withMessages([
                'approval' => 'Solo se puede ajustar el aprobador de un nivel que todavía está pendiente.',
            ]);
        }

        if ((int) $newApprover->id === (int) $ticket->user_id) {
            throw ValidationException::withMessages([
                'approver_id' => 'El creador del ticket no puede ser su propio aprobador.',
            ]);
        }

        $oldApprover = $approval->effectiveApprover();

        $approval->update(['approver_id' => $newApprover->id]);

        TicketActivityLog::record(
            $ticket,
            $actor,
            'approval_reassigned',
            sprintf(
                'Nivel "%s": aprobador ajustado de %s a %s (solo para este ticket).',
                $approval->approvalLevel?->name ?? 'Aprobación',
                $oldApprover?->name ?? 'sin asignar',
                $newApprover->name
            )
        );

        return $approval->refresh();
    }

    /**
     * Asigna, cambia o quita el aprobador ad-hoc de un ticket cuya categoría no
     * tiene flujo configurado. Si $newApprover es null y ya existía uno pendiente,
     * lo quita y el ticket sigue su curso normal sin requerir aprobación.
     */
    public function assignAdhocApprover(Ticket $ticket, User $actor, ?User $newApprover): ?TicketApproval
    {
        $existing = TicketApproval::where('ticket_id', $ticket->id)
            ->whereNull('approval_level_id')
            ->first();

        if ($existing && $existing->status !== 'pending') {
            throw ValidationException::withMessages([
                'approval' => 'La aprobación ad-hoc de este ticket ya fue resuelta y no puede modificarse.',
            ]);
        }

        if (! $newApprover) {
            if ($existing) {
                $existing->delete();
                TicketActivityLog::record($ticket, $actor, 'approval_reassigned', 'Aprobador ad-hoc removido; el ticket ya no requiere aprobación.');
            }

            return null;
        }

        if ((int) $newApprover->id === (int) $ticket->user_id) {
            throw ValidationException::withMessages([
                'approver_id' => 'El creador del ticket no puede ser su propio aprobador.',
            ]);
        }

        if ($existing) {
            $oldApprover = $existing->approver;
            $existing->update(['approver_id' => $newApprover->id]);

            TicketActivityLog::record(
                $ticket,
                $actor,
                'approval_reassigned',
                sprintf('Aprobador ad-hoc cambiado de %s a %s.', $oldApprover?->name ?? 'sin asignar', $newApprover->name)
            );

            return $existing->refresh();
        }

        $approval = TicketApproval::create([
            'ticket_id' => $ticket->id,
            'approval_level_id' => null,
            'approver_id' => $newApprover->id,
            'status' => 'pending',
        ]);

        TicketActivityLog::record($ticket, $actor, 'approval_reassigned', "Aprobador ad-hoc asignado: {$newApprover->name}.");

        SafeNotifier::send($newApprover, new ApprovalRequestedNotification($ticket));

        return $approval;
    }

    public function approveAdhoc(Ticket $ticket, User $actor, ?string $comments): TicketApproval
    {
        $approval = $this->guardedPendingAdhocApproval($ticket, $actor);

        $approval->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
            'comments' => $comments,
            'approved_at' => now(),
        ]);

        TicketActivityLog::record($ticket, $actor, 'approved', $comments);

        return $approval->refresh();
    }

    public function rejectAdhoc(Ticket $ticket, User $actor, string $comments): TicketApproval
    {
        $approval = $this->guardedPendingAdhocApproval($ticket, $actor);

        $approval->update([
            'status' => 'rejected',
            'approved_by' => $actor->id,
            'comments' => $comments,
            'approved_at' => now(),
        ]);

        TicketActivityLog::record($ticket, $actor, 'rejected', $comments);

        SafeNotifier::send($ticket->user, new TicketRejectedNotification($ticket, $comments));

        return $approval->refresh();
    }

    /**
     * Dispara el envío a aprobación de un ticket que aún no pasó por el proceso
     * (a diferencia de la creación normal, esto pasa DESPUÉS de creado el
     * ticket, vía la acción "Enviar para su aprobación"). Si la categoría
     * tiene flujo configurado, crea las filas por nivel igual que al crear un
     * ticket nuevo; si no, usa el aprobador ad-hoc que se le pase y, a
     * diferencia del ajuste rápido en la pantalla de edición, sí bloquea el
     * ticket en pending_approval — es la diferencia entre "dejar preparado un
     * aprobador" y "mandarlo a aprobación ahora".
     */
    public function sendForApproval(Ticket $ticket, User $actor, ?User $adhocApprover = null): Ticket
    {
        // El aviso se dispara DESPUÉS de que la transacción cierre (ver abajo):
        // un fallo de correo (Postmark caído, timeout, etc.) no debe revertir
        // la creación de la aprobación ni el cambio de estado del ticket.
        $notifiable = null;
        $notification = null;
        /** @var ?Notification $notification */

        $result = DB::transaction(function () use ($ticket, $actor, $adhocApprover, &$notifiable, &$notification) {
            $ticket->refresh();

            if ($ticket->approvals()->exists()) {
                throw ValidationException::withMessages([
                    'ticket' => 'Este ticket ya pasó por un proceso de aprobación.',
                ]);
            }

            // Misma matriz que usan el Kanban y el selector rápido: solo desde
            // open/assigned/in_progress. Bloquea también "on_hold" — un ticket
            // pausado debe reanudarse antes de mandarse a aprobación.
            if (! $this->statusService->canTransition($ticket->status, 'pending_approval')) {
                throw ValidationException::withMessages([
                    'ticket' => 'Este ticket no puede enviarse a aprobación en su estado actual.',
                ]);
            }

            $category = $ticket->category()->with('approvalFlow.levels.approver')->first();
            $levels = $category?->approvalFlow?->levels ?? collect();

            if ($levels->isNotEmpty()) {
                foreach ($levels as $level) {
                    TicketApproval::create([
                        'ticket_id' => $ticket->id,
                        'approval_level_id' => $level->id,
                        'status' => 'pending',
                    ]);
                }

                $firstLevel = $levels->first();
                $notifiable = $firstLevel->approver;
                $notification = new ApprovalRequestedNotification($ticket, $firstLevel);

                $description = "Enviado al flujo \"{$category->approvalFlow->name}\".";
            } else {
                if (! $adhocApprover) {
                    throw ValidationException::withMessages([
                        'approver_id' => 'Esta categoría no tiene flujo configurado: selecciona un aprobador.',
                    ]);
                }

                if ((int) $adhocApprover->id === (int) $ticket->user_id) {
                    throw ValidationException::withMessages([
                        'approver_id' => 'El creador del ticket no puede ser su propio aprobador.',
                    ]);
                }

                TicketApproval::create([
                    'ticket_id' => $ticket->id,
                    'approval_level_id' => null,
                    'approver_id' => $adhocApprover->id,
                    'status' => 'pending',
                ]);

                $notifiable = $adhocApprover;
                $notification = new ApprovalRequestedNotification($ticket);

                $description = "Enviado a aprobación ad-hoc: {$adhocApprover->name}.";
            }

            $ticket->update(['status' => 'pending_approval']);

            TicketActivityLog::record($ticket, $actor, 'sent_for_approval', $description);

            return $ticket->refresh();
        });

        SafeNotifier::send($notifiable, $notification);

        return $result;
    }

    /**
     * A diferencia de guardedPendingApproval(), una aprobación ad-hoc no depende
     * del estado 'pending_approval' del ticket (se puede asignar en cualquier
     * momento de su ciclo de vida) ni de niveles previos (siempre es única).
     */
    protected function guardedPendingAdhocApproval(Ticket $ticket, User $actor): TicketApproval
    {
        return DB::transaction(function () use ($ticket, $actor) {
            $ticket->refresh();

            if (in_array($ticket->status, TicketStatusService::TERMINAL_STATES, true)) {
                throw ValidationException::withMessages([
                    'ticket' => 'Este ticket ya no puede aprobarse ni rechazarse: ya está '.TicketStatusService::label($ticket->status).'.',
                ]);
            }

            if ($actor->id === $ticket->user_id) {
                throw ValidationException::withMessages([
                    'actor' => 'El creador del ticket no puede aprobar ni rechazar su propio ticket.',
                ]);
            }

            $approval = TicketApproval::where('ticket_id', $ticket->id)
                ->whereNull('approval_level_id')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $approval) {
                throw ValidationException::withMessages([
                    'approval' => 'No hay una aprobación ad-hoc pendiente para este ticket.',
                ]);
            }

            if ($actor->role !== 'admin' && (int) $approval->approver_id !== (int) $actor->id) {
                throw ValidationException::withMessages([
                    'actor' => 'No eres el aprobador asignado para este ticket.',
                ]);
            }

            return $approval;
        });
    }
}
