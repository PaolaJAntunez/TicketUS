<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeLog extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
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

    public function isRunning(): bool
    {
        return $this->ended_at === null;
    }

    /**
     * Minutos transcurridos: hasta ahora si sigue corriendo, hasta ended_at si ya paró.
     */
    protected function minutes(): Attribute
    {
        return Attribute::make(
            // Carbon 3 diffInMinutes() devuelve float; se redondea al minuto.
            get: fn () => (int) round($this->started_at->diffInMinutes($this->ended_at ?? now()))
        );
    }
}
