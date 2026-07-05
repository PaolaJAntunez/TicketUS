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
     * Pending approvals that are actually actionable right now: the lowest-order
     * level per ticket whose lower-order siblings (if any) are all approved.
     */
    public static function actionableForRole(?string $role = null)
    {
        return static::with(['ticket.user', 'ticket.category', 'approvalLevel'])
            ->where('status', 'pending')
            ->when($role, fn ($query) => $query->whereHas('approvalLevel', fn ($q) => $q->where('role', $role)))
            ->get()
            ->filter(function (self $approval) {
                return ! static::where('ticket_id', $approval->ticket_id)
                    ->where('status', '!=', 'approved')
                    ->whereHas('approvalLevel', fn ($q) => $q->where('order', '<', $approval->approvalLevel->order))
                    ->exists();
            })
            ->values();
    }
}
