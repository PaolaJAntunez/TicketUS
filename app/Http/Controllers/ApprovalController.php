<?php

namespace App\Http\Controllers;

use App\Http\Requests\Approvals\ApproveAdhocTicketRequest;
use App\Http\Requests\Approvals\ApproveTicketRequest;
use App\Http\Requests\Approvals\AssignApprovalRequest;
use App\Http\Requests\Approvals\RejectAdhocTicketRequest;
use App\Http\Requests\Approvals\RejectTicketRequest;
use App\Http\Requests\Approvals\ReopenTicketRequest;
use App\Models\ApprovalLevel;
use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\User;
use App\Services\TicketApprovalService;
use App\Services\TicketStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ApprovalController extends Controller
{
    public function __construct(protected TicketApprovalService $approvals)
    {
    }

    public function index()
    {
        $user = Auth::user();

        $approvals = TicketApproval::actionableForUser($user);

        return view('approvals.index', compact('approvals'));
    }

    public function approve(ApproveTicketRequest $request, Ticket $ticket, ApprovalLevel $level)
    {
        try {
            $this->approvals->approve($ticket, $level, Auth::user(), $request->validated('comments'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('failed_ticket_id', $ticket->id);
        }

        return redirect()->route('approvals.index')->with('success', 'Aprobación registrada correctamente.');
    }

    public function reject(RejectTicketRequest $request, Ticket $ticket, ApprovalLevel $level)
    {
        try {
            $this->approvals->reject($ticket, $level, Auth::user(), $request->validated('comments'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('failed_ticket_id', $ticket->id);
        }

        return redirect()->route('approvals.index')->with('success', 'Ticket rechazado.');
    }

    public function reopen(ReopenTicketRequest $request, Ticket $ticket)
    {
        try {
            $this->approvals->reopen($ticket, Auth::user(), $request->validated('reason'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket reabierto correctamente.');
    }

    /**
     * Desde la pantalla de asignación del ticket: ajusta el aprobador de un
     * nivel del flujo solo para este ticket (approval_id presente), o
     * asigna/cambia/quita el aprobador ad-hoc cuando la categoría no tiene
     * flujo configurado (approval_id ausente).
     */
    public function assign(AssignApprovalRequest $request, Ticket $ticket)
    {
        $newApprover = $request->validated('approver_id') ? User::find($request->validated('approver_id')) : null;

        try {
            if ($request->filled('approval_id')) {
                if (! $newApprover) {
                    return back()->withErrors(['approver_id' => 'Debes seleccionar un aprobador para este nivel.']);
                }

                $approval = TicketApproval::findOrFail($request->validated('approval_id'));
                $this->approvals->reassignLevelApprover($ticket, $approval, Auth::user(), $newApprover);
            } else {
                $this->approvals->assignAdhocApprover($ticket, Auth::user(), $newApprover);
            }
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()->route('tickets.edit', $ticket)->with('success', 'Aprobación actualizada.');
    }

    public function approveAdhoc(ApproveAdhocTicketRequest $request, Ticket $ticket)
    {
        try {
            $this->approvals->approveAdhoc($ticket, Auth::user(), $request->validated('comments'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('failed_ticket_id', $ticket->id);
        }

        return redirect()->route('approvals.index')->with('success', 'Aprobación registrada correctamente.');
    }

    public function rejectAdhoc(RejectAdhocTicketRequest $request, Ticket $ticket)
    {
        try {
            $this->approvals->rejectAdhoc($ticket, Auth::user(), $request->validated('comments'));
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('failed_ticket_id', $ticket->id);
        }

        return redirect()->route('approvals.index')->with('success', 'Ticket rechazado.');
    }

    /**
     * Acción "Enviar para su aprobación" del menú Acciones: a diferencia de
     * assign() (que solo ajusta quién aprobará), esto efectivamente dispara
     * el proceso y bloquea el ticket en pending_approval.
     */
    public function sendForApproval(Request $request, Ticket $ticket, TicketStatusService $statusService)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'approver_id' => ['nullable', Rule::exists('users', 'id')],
        ]);

        $adhocApprover = $request->filled('approver_id') ? User::find($request->input('approver_id')) : null;

        try {
            $this->approvals->sendForApproval($ticket, Auth::user(), $adhocApprover);
        } catch (ValidationException $e) {
            return $request->wantsJson()
                ? response()->json(['message' => collect($e->errors())->flatten()->first()], 422)
                : back()->withErrors($e->errors());
        }

        if ($request->wantsJson()) {
            $ticket->refresh();

            return response()->json([
                'status' => $ticket->status,
                'targets' => $statusService->validTargets($ticket),
                'message' => 'Ticket enviado a aprobación.',
            ]);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket enviado a aprobación.');
    }
}
