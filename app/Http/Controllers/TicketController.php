<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\User;
use App\Notifications\ApprovalRequestedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->role === 'agent') {
            $tickets = Ticket::with(['category', 'user', 'agent'])->latest()->get();
        } else {
            $tickets = Ticket::with(['category', 'user', 'agent'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('tickets.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'priority'    => 'required|in:low,medium,high,urgent',
        ]);

        $category = Category::with(['approvalFlow.levels'])->find($request->category_id);
        $levels = $category?->approvalFlow?->levels ?? collect();

        $ticket = Ticket::create([
            'title'       => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'priority'    => $request->priority,
            'user_id'     => Auth::id(),
            'status'      => $levels->isNotEmpty() ? 'pending_approval' : 'open',
        ]);

        if ($levels->isNotEmpty()) {
            foreach ($levels as $level) {
                TicketApproval::create([
                    'ticket_id'         => $ticket->id,
                    'approval_level_id' => $level->id,
                    'status'            => 'pending',
                ]);
            }

            $firstLevel = $levels->first();
            $approvers = User::where('role', $firstLevel->role)->get();

            foreach ($approvers as $approver) {
                $approver->notify(new ApprovalRequestedNotification($ticket, $firstLevel));
            }
        }

        $ticket->user->notify(new TicketCreatedNotification($ticket));

        return redirect()->route('tickets.index')->with('success', 'Ticket creado exitosamente.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load(['category', 'user', 'agent', 'approvals.approvalLevel', 'approvals.approvedBy']);
        $agents = User::where('role', 'agent')->get();

        return view('tickets.show', compact('ticket', 'agents'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $categories = Category::all();
        return view('tickets.edit', compact('ticket', 'categories'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $request->validate([
            'status'      => 'required|in:open,assigned,in_progress,resolved,closed,pending_approval,rejected',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('role', 'agent')],
        ]);

        $oldStatus = $ticket->status;
        $oldAssignedTo = $ticket->assigned_to;

        $newAssignedTo = $request->input('assigned_to') ?: null;
        $assignedChanged = $newAssignedTo !== null && (int) $newAssignedTo !== (int) $oldAssignedTo;

        $data = [
            'status'      => $request->status,
            'priority'    => $request->priority,
            'assigned_to' => $newAssignedTo,
        ];

        if ($assignedChanged) {
            $data['status'] = 'assigned';
        }

        $ticket->update($data);

        if ($oldStatus !== $ticket->status) {
            $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, $ticket->status));
        }

        if ($assignedChanged) {
            $ticket->agent->notify(new TicketAssignedNotification($ticket));
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket actualizado.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket eliminado.');
    }
}