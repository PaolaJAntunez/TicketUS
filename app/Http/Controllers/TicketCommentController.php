<?php

namespace App\Http\Controllers;

use App\Models\TicketComment;
use App\Models\Ticket;
use App\Notifications\TicketCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketCommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'comment'   => $request->comment,
        ]);

        if ($ticket->user_id === Auth::id()) {
            $ticket->agent?->notify(new TicketCommentNotification($ticket, $comment));
        } else {
            $ticket->user->notify(new TicketCommentNotification($ticket, $comment));
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Comentario agregado.');
    }
}