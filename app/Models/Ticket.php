<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'resolution',
        'status',
        'priority',
        'category_id',
        'subcategory_id',
        'user_id',
        'assigned_to',
        'resolved_at',
        'amount',
        'reopened_reason',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
            'due_date' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (! $ticket->due_date) {
                $hours = config("sla.hours.{$ticket->priority}", config('sla.default_hours'));
                $ticket->due_date = now()->addHours($hours);
            }
        });
    }

    /**
     * Tiempo restante hasta due_date: "+8 horas" si aún tiene tiempo,
     * "-15 horas" si ya venció. Null si el ticket no tiene due_date.
     */
    protected function remaining(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->due_date) {
                    return null;
                }

                $hours = ($this->due_date->timestamp - now()->timestamp) / 3600;
                $sign = $hours >= 0 ? '+' : '-';

                return $sign.round(abs($hours)).' horas';
            }
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(TicketComment::class);
}

    public function approvals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketApproval::class);
    }

    /**
     * True si hay un nivel (o aprobador ad-hoc) sin votar todavía. No depende
     * de status === 'pending_approval': un aprobador ad-hoc puede quedar
     * asignado sin mandar el ticket al flujo formal (ver
     * TicketApprovalService::assignAdhocApprover()), y en ese caso el ticket
     * sigue en in_progress/assigned pero igual tiene una aprobación pendiente.
     */
    public function hasPendingApproval(): bool
    {
        return $this->approvals()->where('status', 'pending')->exists();
    }

    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketActivityLog::class)->latest('id');
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketAttachment::class)->latest('id');
    }

    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketReminder::class)->orderBy('remind_at');
    }

    public function timeLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TimeLog::class)->latest('started_at');
    }

    public function tags(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_ticket');
    }
}