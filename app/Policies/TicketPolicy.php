<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        if (in_array($user->role, ['admin', 'agent']) || $user->id === $ticket->user_id) {
            return true;
        }

        return $ticket->approvals()
            ->whereHas('approvalLevel', fn ($q) => $q->where('role', $user->role))
            ->exists();
    }

    public function update(User $user, Ticket $ticket): bool
    {
        // Regla #11: mientras el ticket está en proceso de aprobación, el creador
        // solo puede consultarlo; únicamente un admin puede editarlo en ese estado.
        if ($user->id === $ticket->user_id && $ticket->status === 'pending_approval' && $user->role !== 'admin') {
            return false;
        }

        return $this->view($user, $ticket);
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    public function reopen(User $user, Ticket $ticket): bool
    {
        return in_array($user->role, ['admin', 'agent'], true);
    }
}
