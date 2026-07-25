<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TimeLog;
use Illuminate\Support\Facades\Auth;

class TicketTimeLogController extends Controller
{
    public function start(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $user = Auth::user();

        $alreadyRunning = TimeLog::where('ticket_id', $ticket->id)
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->exists();

        if ($alreadyRunning) {
            return back()->withErrors(['timer' => 'Ya tienes un temporizador corriendo en este ticket.']);
        }

        TimeLog::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        TicketActivityLog::record($ticket, $user, 'timer_started');

        return redirect()->route('tickets.show', $ticket)->with('success', 'Temporizador iniciado.');
    }

    public function stop(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $user = Auth::user();

        $log = TimeLog::where('ticket_id', $ticket->id)
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $log) {
            return back()->withErrors(['timer' => 'No hay un temporizador corriendo en este ticket.']);
        }

        $log->update(['ended_at' => now()]);

        // Carbon 3 diffInMinutes() devuelve float; se redondea para el log.
        $minutes = (int) round($log->started_at->diffInMinutes(now()));
        TicketActivityLog::record($ticket, $user, 'timer_stopped', "{$minutes} minutos registrados.");

        return redirect()->route('tickets.show', $ticket)->with('success', 'Temporizador detenido.');
    }
}
