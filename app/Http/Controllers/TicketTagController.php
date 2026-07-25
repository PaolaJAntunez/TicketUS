<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketTagController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'name' => ['required', 'string', 'max:50'],
        ]);

        // Catálogo abierto: si la etiqueta no existe, se crea (como las
        // labels de GitHub Issues) y queda disponible para otros tickets.
        $tag = Tag::firstOrCreate(['name' => trim($request->input('name'))]);

        if (! $ticket->tags()->where('tags.id', $tag->id)->exists()) {
            $ticket->tags()->attach($tag->id);
            TicketActivityLog::record($ticket, Auth::user(), 'tag_added', $tag->name);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Etiqueta agregada.');
    }

    public function destroy(Ticket $ticket, Tag $tag)
    {
        $this->authorize('update', $ticket);

        $ticket->tags()->detach($tag->id);
        TicketActivityLog::record($ticket, Auth::user(), 'tag_removed', $tag->name);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Etiqueta quitada.');
    }
}
