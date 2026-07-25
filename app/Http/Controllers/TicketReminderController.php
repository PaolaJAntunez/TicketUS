<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketReminderController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $user = Auth::user();

        // Regla del pedido: el recordatorio solo lo puede crear (y ver) el
        // agente puntualmente asignado, o un admin.
        abort_unless(
            $user->role === 'admin' || (int) $ticket->assigned_to === (int) $user->id,
            403,
            'Solo el agente asignado puede agregar recordatorios en este ticket.'
        );

        $request->validate([
            'remind_at' => ['required', 'date'],
            'note' => ['required', 'string', 'max:1000'],
        ]);

        TicketReminder::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'remind_at' => $request->input('remind_at'),
            'note' => $request->input('note'),
        ]);

        TicketActivityLog::record($ticket, $user, 'reminder_added', $request->input('note'));

        return redirect()->route('tickets.show', $ticket)->with('success', 'Recordatorio agregado.');
    }
}
