<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip',
            ],
        ]);

        $file = $request->file('file');
        // Disco "local" = storage/app/private, nunca público: se descarga vía
        // download() más abajo, que exige el mismo permiso que ver el ticket.
        $path = $file->store('ticket-attachments/'.$ticket->id, 'local');

        $attachment = TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        TicketActivityLog::record($ticket, Auth::user(), 'attachment_added', $attachment->original_name);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Archivo adjuntado.');
    }

    public function download(Ticket $ticket, TicketAttachment $attachment)
    {
        $this->authorize('view', $ticket);

        abort_unless((int) $attachment->ticket_id === (int) $ticket->id, 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}
