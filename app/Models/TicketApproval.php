<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'approval_level_id',
        'approver_id',
        'status',
        'approved_by',
        'comments',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function approvalLevel(): BelongsTo
    {
        return $this->belongsTo(ApprovalLevel::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Aprobador ajustado solo para este ticket (override). Null si este nivel
     * usa el aprobador por defecto del flujo, o si aún no se ha ajustado.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Aprobador que realmente decide sobre esta fila: el override del ticket
     * si existe, si no el aprobador por defecto del nivel del flujo. Para
     * aprobaciones ad-hoc (sin approval_level_id) solo existe el override.
     */
    public function effectiveApprover(): ?User
    {
        return $this->approver ?? $this->approvalLevel?->approver;
    }

    public function isAdhoc(): bool
    {
        return $this->approval_level_id === null;
    }

    /**
     * Pending approvals that are actually actionable right now: the lowest-order
     * level per ticket whose lower-order siblings (if any) are all approved.
     * Ad-hoc approvals (sin flujo) no tienen orden que respetar, así que siempre
     * son accionables. Admins ven todo lo accionable; el resto solo ve aquello
     * donde son el aprobador efectivo (override del ticket, o el del nivel).
     */
    public static function actionableForUser(User $user)
    {
        $isAdmin = $user->role === 'admin';

        return static::with(['ticket.user', 'ticket.category', 'approvalLevel', 'approver'])
            ->where('status', 'pending')
            ->when(! $isAdmin, fn ($query) => $query->where(function ($q) use ($user) {
                $q->where('approver_id', $user->id)
                    ->orWhere(function ($q2) use ($user) {
                        $q2->whereNull('approver_id')
                            ->whereHas('approvalLevel', fn ($q3) => $q3->where('approver_id', $user->id));
                    });
            }))
            ->get()
            ->filter(function (self $approval) {
                if (! $approval->approvalLevel) {
                    return true;
                }

                return ! static::where('ticket_id', $approval->ticket_id)
                    ->where('status', '!=', 'approved')
                    ->whereHas('approvalLevel', fn ($q) => $q->where('order', '<', $approval->approvalLevel->order))
                    ->exists();
            })
            ->values();
    }
}
