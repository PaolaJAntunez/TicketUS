<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketAttachment;
use App\Services\TicketAttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentController extends Controller
{
    public function __construct(protected TicketAttachmentService $attachments)
    {
    }

    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'file' => TicketAttachmentService::rules(),
        ]);

        $attachment = $this->attachments->store($ticket, $request->file('file'), Auth::user());

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
