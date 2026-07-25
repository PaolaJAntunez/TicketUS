<?php

namespace App\Http\Controllers;

use App\Exports\DashboardExport;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketApproval;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        [$view, $data] = $this->buildDashboardData($request);

        return view($view, $data);
    }

    /**
     * Espejo de index(): construye exactamente los mismos datos que ve la
     * pantalla, pero como PDF. Reutiliza buildDashboardData() a propósito —
     * así pantalla y reporte nunca pueden desincronizarse (misma query,
     * mismos filtros de fecha, mismo rol).
     */
    public function downloadPdf(Request $request)
    {
        [, $data] = $this->buildDashboardData($request);

        $pdf = Pdf::loadView('reports.dashboard', $data)->setPaper('a4', 'portrait');

        return $pdf->download('Reporte_Dashboard_'.now()->format('Y-m-d').'.pdf');
    }

    public function downloadExcel(Request $request)
    {
        [, $data] = $this->buildDashboardData($request);

        return Excel::download(
            new DashboardExport($data),
            'Reporte_Dashboard_'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Punto único de armado de datos, compartido por pantalla/PDF/Excel.
     * Devuelve [nombre_de_vista, datos] — index() renderiza la vista con
     * esos datos; downloadPdf()/downloadExcel() ignoran el nombre de vista
     * y usan los mismos datos para el reporte.
     */
    protected function buildDashboardData(Request $request): array
    {
        $user = Auth::user();

        [$from, $to] = $this->resolveDateRange($request);

        // Compartido entre los tres roles: cualquier usuario puede tener
        // aprobaciones pendientes (approver_id de un flujo o de un ticket
        // puntual), no solo admin/agent. Mismo criterio que usa /approvals.
        $myPendingApprovals = TicketApproval::actionableForUser($user)->count();

        return match ($user->role) {
            'admin' => ['dashboard.admin', $this->adminDashboardData($from, $to, $myPendingApprovals, $user)],
            'agent' => ['dashboard.agent', $this->agentDashboardData($user, $from, $to, $myPendingApprovals)],
            default => ['dashboard.user', $this->userDashboardData($user, $from, $to, $myPendingApprovals)],
        };
    }

    /**
     * Rango del selector de fechas (?from=&to=), por defecto los últimos 30
     * días. Solo escala los conteos/gráficas de tendencia: las tarjetas y
     * listas "accionables" (vencidos, sin asignar, próximos a vencer,
     * aprobaciones pendientes, top agentes del mes) son fotos del presente y
     * deliberadamente no dependen de este rango.
     */
    protected function resolveDateRange(Request $request): array
    {
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now();

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();

        return [$from, $to];
    }

    protected function adminDashboardData(Carbon $from, Carbon $to, int $myPendingApprovals, User $generatedBy): array
    {
        $totalCount = Ticket::whereBetween('created_at', [$from, $to])->count();
        $openCount = Ticket::where('status', 'open')->count();
        $inProgressCount = Ticket::whereIn('status', ['assigned', 'in_progress'])->count();
        $resolvedCount = Ticket::whereIn('status', ['resolved', 'closed'])->count();
        $pendingApprovalCount = Ticket::where('status', 'pending_approval')->count();

        $overdueCount = Ticket::where('due_date', '<', now())
            ->whereNotIn('status', ['resolved', 'closed', 'cancelled', 'rejected'])
            ->count();

        $avgResolutionHours = Ticket::whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, resolved_at)) / 60 as avg_hours')
            ->value('avg_hours');

        $pendingApprovalsSystemCount = TicketApproval::where('status', 'pending')->count();

        // --- Sección nueva: tickets reabiertos en el rango filtrado ---
        $reopenedCount = TicketActivityLog::where('action', 'reopened')
            ->whereBetween('created_at', [$from, $to])
            ->count();

        // --- Sección nueva: tendencia semanal (fija al presente, mismo
        // criterio que "vencidos"/"top agentes": no depende del filtro). ---
        $thisWeekCount = Ticket::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $lastWeekCount = Ticket::whereBetween('created_at', [
            now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek(),
        ])->count();
        $weekTrendPercent = $lastWeekCount > 0
            ? round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1)
            : ($thisWeekCount > 0 ? 100.0 : 0.0);
        $weekTrendDirection = $thisWeekCount >= $lastWeekCount ? 'up' : 'down';

        $agents = User::where('role', 'agent')->withCount('assignedTickets')->get();
        $ticketsByAgent = $agents;

        $categoryBreakdown = Category::withCount(['tickets' => fn ($q) => $q->whereBetween('created_at', [$from, $to])])
            ->orderByDesc('tickets_count')
            ->take(8)
            ->get()
            ->filter(fn ($c) => $c->tickets_count > 0)
            ->values();
        $maxCategoryCount = max($categoryBreakdown->max('tickets_count'), 1);
        $ticketsByCategory = $categoryBreakdown;

        // --- Sección nueva: top 5 con más incidentes — reutiliza
        // $categoryBreakdown (ya ordenado desc y filtrado), sin query nueva. ---
        $topCategories = $categoryBreakdown->take(5)->values();

        $ticketsByStatus = Ticket::whereBetween('created_at', [$from, $to])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $ticketsByMonth = Ticket::whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at, '%m') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // --- Sección nueva: resolución promedio por categoría, en el rango. ---
        $avgResolutionByCategory = Category::query()
            ->join('tickets', 'tickets.category_id', '=', 'categories.id')
            ->whereNotNull('tickets.resolved_at')
            ->whereBetween('tickets.resolved_at', [$from, $to])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('avg_hours')
            ->selectRaw('categories.id, categories.name, AVG(TIMESTAMPDIFF(MINUTE, tickets.created_at, tickets.resolved_at)) / 60 as avg_hours, COUNT(tickets.id) as resolved_count')
            ->get();

        // --- Sección nueva: prioridad por mes (barras apiladas), en el rango. ---
        $ticketsByPriorityMonth = Ticket::whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at, '%m') as month, priority, COUNT(*) as total")
            ->groupBy('month', 'priority')
            ->orderBy('month')
            ->get();

        // Top 5 agentes por resueltos ESTE MES (fijo, no depende del rango elegido).
        $topAgentsThisMonth = Ticket::whereIn('status', ['resolved', 'closed'])
            ->whereNotNull('assigned_to')
            ->whereBetween('resolved_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->select('assigned_to', DB::raw('COUNT(*) as total'))
            ->groupBy('assigned_to')
            ->orderByDesc('total')
            ->take(5)
            ->with('agent:id,name')
            ->get();

        // Abiertos, sin agente, creados hace más de 24h.
        $unassignedStaleTickets = Ticket::where('status', 'open')
            ->whereNull('assigned_to')
            ->where('created_at', '<', now()->subHours(24))
            ->orderBy('created_at')
            ->with('category')
            ->get();

        $recentCreated = Ticket::with(['category', 'user'])->latest()->take(5)->get();
        $recentResolved = Ticket::with(['category', 'user'])
            ->whereIn('status', ['resolved', 'closed'])
            ->latest('resolved_at')
            ->take(5)
            ->get();

        return compact(
            'totalCount', 'openCount', 'inProgressCount', 'resolvedCount', 'pendingApprovalCount',
            'overdueCount', 'avgResolutionHours', 'pendingApprovalsSystemCount', 'myPendingApprovals',
            'reopenedCount', 'thisWeekCount', 'lastWeekCount', 'weekTrendPercent', 'weekTrendDirection',
            'agents', 'categoryBreakdown', 'maxCategoryCount', 'topCategories',
            'ticketsByAgent', 'ticketsByStatus', 'ticketsByCategory', 'ticketsByMonth',
            'avgResolutionByCategory', 'ticketsByPriorityMonth',
            'topAgentsThisMonth', 'unassignedStaleTickets', 'recentCreated', 'recentResolved',
            'from', 'to', 'generatedBy'
        );
    }

    protected function agentDashboardData(User $user, Carbon $from, Carbon $to, int $myPendingApprovals): array
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

        // Desglose de vencidos entre los ya asignados: sobre la colección que
        // ya se cargó arriba, sin disparar una query aparte.
        $assignedOverdueCount = $assignedTickets->filter(fn ($t) => $t->due_date && $t->due_date->isPast())->count();

        $dueSoon = Ticket::where('assigned_to', $user->id)
            ->whereNotIn('status', ['resolved', 'closed', 'cancelled', 'rejected'])
            ->whereBetween('due_date', [now(), now()->addHours(24)])
            ->orderBy('due_date')
            ->with('category')
            ->get();

        $ticketsByPriority = Ticket::select('priority', DB::raw('COUNT(*) as total'))
            ->where('assigned_to', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('priority')
            ->get();
        $ticketsByStatus = Ticket::select('status', DB::raw('COUNT(*) as total'))
            ->where('assigned_to', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->get();

        $recentCreated = Ticket::with(['category', 'user'])->latest()->take(5)->get();
        $recentResolved = Ticket::with(['category', 'user'])
            ->whereIn('status', ['resolved', 'closed'])
            ->latest('resolved_at')
            ->take(5)
            ->get();

        return compact(
            'assignedTickets', 'assignedCount', 'inProgressCount', 'resolvedTodayCount',
            'ticketsByPriority', 'ticketsByStatus', 'myPendingApprovals',
            'assignedOverdueCount', 'dueSoon', 'recentCreated', 'recentResolved', 'from', 'to'
        ) + ['generatedBy' => $user];
    }

    protected function userDashboardData(User $user, Carbon $from, Carbon $to, int $myPendingApprovals): array
    {
        $activeTickets = Ticket::with('category')
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->latest()
            ->get();

        $openCount = Ticket::where('user_id', $user->id)->where('status', 'open')->count();
        $inProgressCount = Ticket::where('user_id', $user->id)->whereIn('status', ['assigned', 'in_progress'])->count();
        $resolvedCount = Ticket::where('user_id', $user->id)->whereIn('status', ['resolved', 'closed'])->count();
        $myTicketsByStatus = Ticket::select('status', DB::raw('COUNT(*) as total'))
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->get();
        $myTicketsByMonth = Ticket::selectRaw("DATE_FORMAT(created_at, '%m') as month, COUNT(*) as total")
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $recentCreated = Ticket::with(['category', 'user'])->where('user_id', $user->id)->latest()->take(5)->get();
        $recentResolved = Ticket::with(['category', 'user'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['resolved', 'closed'])
            ->latest('resolved_at')
            ->take(5)
            ->get();

        return compact(
            'activeTickets', 'openCount', 'inProgressCount', 'resolvedCount',
            'myTicketsByStatus', 'myTicketsByMonth', 'myPendingApprovals',
            'recentCreated', 'recentResolved', 'from', 'to'
        ) + ['generatedBy' => $user];
    }
}
