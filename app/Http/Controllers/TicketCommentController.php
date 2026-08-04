<?php

namespace App\Http\Controllers;

use App\Models\TicketActivityLog;
use App\Models\TicketComment;
use App\Models\Ticket;
use App\Notifications\TicketCommentNotification;
use App\Services\SafeNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketCommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $request->validate([
            'comment' => 'required|string|max:1000',
            'is_internal' => 'sometimes|boolean',
        ]);

        // Solo admin/agente pueden dejar notas internas, sin importar lo que
        // mande el formulario: el solicitante no debe poder marcarlas.
        $isInternal = $request->boolean('is_internal') && in_array(Auth::user()->role, ['admin', 'agent'], true);

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'comment'   => $request->comment,
            'is_internal' => $isInternal,
        ]);

        TicketActivityLog::record($ticket, Auth::user(), $isInternal ? 'internal_note' : 'commented', $request->comment);

        // Las notas internas no generan notificación al solicitante: no debe enterarse de que existen.
        if (! $isInternal) {
            if ($ticket->user_id === Auth::id()) {
                SafeNotifier::send($ticket->agent, new TicketCommentNotification($ticket, $comment));
            } else {
                SafeNotifier::send($ticket->user, new TicketCommentNotification($ticket, $comment));
            }
        }

        return redirect()->route('tickets.show', $ticket)->with('success', $isInternal ? 'Nota interna agregada.' : 'Comentario agregado.');
    }
}