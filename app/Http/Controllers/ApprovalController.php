<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLevel;
use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\User;
use App\Notifications\ApprovalRequestedNotification;
use App\Notifications\TicketRejectedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $approvals = TicketApproval::actionableForRole($user->role !== 'admin' ? $user->role : null);

        return view('approvals.index', compact('approvals'));
    }

    public function approve(Request $request, Ticket $ticket, ApprovalLevel $level)
    {
        $approval = $this->authorizedApproval($ticket, $level);

        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        $approval->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'comments'    => $request->comments,
            'approved_at' => now(),
        ]);

        $nextLevel = ApprovalLevel::where('approval_flow_id', $level->approval_flow_id)
            ->where('order', '>', $level->order)
            ->orderBy('order')
            ->first();

        if ($nextLevel) {
            $approvers = User::where('role', $nextLevel->role)->get();

            foreach ($approvers as $approver) {
                $approver->notify(new ApprovalRequestedNotification($ticket, $nextLevel));
            }
        } else {
            $oldStatus = $ticket->status;
            $ticket->update(['status' => 'open']);
            $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, $ticket->status));
        }

        return redirect()->route('approvals.index')->with('success', 'Aprobación registrada correctamente.');
    }

    public function reject(Request $request, Ticket $ticket, ApprovalLevel $level)
    {
        $approval = $this->authorizedApproval($ticket, $level);

        $request->validate([
            'comments' => 'nullable|string|max:1000',
        ]);

        $approval->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'comments'    => $request->comments,
            'approved_at' => now(),
        ]);

        $ticket->update(['status' => 'rejected']);

        $ticket->user->notify(new TicketRejectedNotification($ticket, $request->comments));

        return redirect()->route('approvals.index')->with('success', 'Ticket rechazado.');
    }

    protected function authorizedApproval(Ticket $ticket, ApprovalLevel $level): TicketApproval
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && $user->role !== $level->role) {
            abort(403);
        }

        return TicketApproval::where('ticket_id', $ticket->id)
            ->where('approval_level_id', $level->id)
            ->where('status', 'pending')
            ->firstOrFail();
    }
}
