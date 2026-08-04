<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Única fuente de verdad para subir un adjunto de ticket: la usan tanto
 * TicketAttachmentController (adjuntar desde el detalle de un ticket ya
 * creado) como TicketController::store() (adjuntar al crear el ticket),
 * para no duplicar el storage/validación en dos sitios.
 */
class TicketAttachmentService
{
    public const MAX_KB = 10240;

    public const ALLOWED_MIMES = 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip';

    /**
     * Reglas de validación por archivo. $required=false se usa donde el
     * campo es opcional (ej. adjuntos al crear un ticket).
     */
    public static function rules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'max:'.self::MAX_KB,
            'mimes:'.self::ALLOWED_MIMES,
        ];
    }

    public function store(Ticket $ticket, UploadedFile $file, User $user): TicketAttachment
    {
        // Disco "local" = storage/app/private, nunca público: se descarga vía
        // TicketAttachmentController::download(), que exige el mismo permiso
        // que ver el ticket.
        $path = $file->store('ticket-attachments/'.$ticket->id, 'local');

        return TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}
