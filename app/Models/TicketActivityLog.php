<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'description',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(Ticket $ticket, ?User $user, string $action, ?string $description = null): self
    {
        return static::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
        ]);
    }

    /**
     * Activity log entries are an immutable audit trail: block update/delete
     * even if calling code invokes them directly on a loaded instance.
     */
    protected static function booted(): void
    {
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }
}
