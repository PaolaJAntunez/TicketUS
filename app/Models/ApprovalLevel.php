<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'approval_flow_id',
        'order',
        'role',
        'name',
    ];

    public function approvalFlow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    public function ticketApprovals(): HasMany
    {
        return $this->hasMany(TicketApproval::class);
    }
}
