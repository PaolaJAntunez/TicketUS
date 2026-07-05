<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            'admin'    => $this->adminDashboard(),
            'agent'    => $this->agentDashboard($user),
            'approver' => $this->approverDashboard($user),
            default    => $this->userDashboard($user),
        };
    }

    protected function adminDashboard()
    {
        $totalCount = Ticket::count();
        $openCount = Ticket::where('status', 'open')->count();
        $inProgressCount = Ticket::whereIn('status', ['assigned', 'in_progress'])->count();
        $resolvedCount = Ticket::whereIn('status', ['resolved', 'closed'])->count();
        $pendingApprovalCount = Ticket::where('status', 'pending_approval')->count();

        $recentTickets = Ticket::with(['category', 'user'])->latest()->take(5)->get();

        $agents = User::where('role', 'agent')->withCount('assignedTickets')->get();

        $categoryBreakdown = Category::withCount('tickets')->orderByDesc('tickets_count')->get();
        $maxCategoryCount = max($categoryBreakdown->max('tickets_count'), 1);

        return view('dashboard', compact(
            'totalCount',
            'openCount',
            'inProgressCount',
            'resolvedCount',
            'pendingApprovalCount',
            'recentTickets',
            'agents',
            'categoryBreakdown',
            'maxCategoryCount',
        ));
    }

    protected function agentDashboard(User $user)
    {
        $assignedTickets = Ticket::with(['category', 'user'])
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->latest()
            ->get();

        $assignedCount = Ticket::where('assigned_to', $user->id)->where('status', 'assigned')->count();
        $inProgressCount = Ticket::where('assigned_to', $user->id)->where('status', 'in_progress')->count();
        $resolvedTodayCount = Ticket::where('assigned_to', $user->id)
            ->whereIn('status', ['resolved', 'closed'])
            ->whereDate('updated_at', today())
            ->count();

        return view('dashboard', compact('assignedTickets', 'assignedCount', 'inProgressCount', 'resolvedTodayCount'));
    }

    protected function approverDashboard(User $user)
    {
        $pendingApprovals = TicketApproval::actionableForRole($user->role);

        $approvalHistory = TicketApproval::with(['ticket', 'approvalLevel'])
            ->where('approved_by', $user->id)
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('approved_at')
            ->take(10)
            ->get();

        return view('dashboard', compact('pendingApprovals', 'approvalHistory'));
    }

    protected function userDashboard(User $user)
    {
        $activeTickets = Ticket::with('category')
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->latest()
            ->get();

        $openCount = Ticket::where('user_id', $user->id)->where('status', 'open')->count();
        $inProgressCount = Ticket::where('user_id', $user->id)->whereIn('status', ['assigned', 'in_progress'])->count();
        $resolvedCount = Ticket::where('user_id', $user->id)->whereIn('status', ['resolved', 'closed'])->count();

        return view('dashboard', compact('activeTickets', 'openCount', 'inProgressCount', 'resolvedCount'));
    }
}
