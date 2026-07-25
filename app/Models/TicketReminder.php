<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketReminder extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'remind_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
