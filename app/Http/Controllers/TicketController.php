<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTicketRequest;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\TicketActivityLog;
use App\Models\TicketApproval;
use App\Models\User;
use App\Notifications\ApprovalRequestedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Services\TicketStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = $this->filteredTicketsQuery($request)->latest()->get();

        $categories = Category::orderBy('name')->get();
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        // Pool de aprobadores para el modal "Enviar a aprobación" del selector
        // rápido de estado (mismo criterio que en Editar/detalle: cualquiera).
        $approvalCandidates = User::orderBy('name')->get();

        return view('tickets.index', compact('tickets', 'categories', 'agents', 'approvalCandidates'));
    }

    /**
     * Misma tabla de tickets que "index", agrupada por columnas de estado
     * en lugar de filas. Reutiliza los mismos filtros (categoría/estado/
     * asignado a) para que el toggle Tabla/Kanban no pierda el contexto.
     */
    public function kanban(Request $request)
    {
        $tickets = $this->filteredTicketsQuery($request)->latest()->get();

        $categories = Category::orderBy('name')->get();
        $agents = User::where('role', 'agent')->orderBy('name')->get();

        return view('tickets.kanban', compact('tickets', 'categories', 'agents'));
    }

    /**
     * Búsqueda global (Ctrl+K) por #ID, título o nombre del solicitante.
     * Misma restricción de visibilidad que index()/kanban(): admin/agent ven
     * todo, el resto solo lo propio.
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $search = trim((string) $request->query('q', ''));

        if ($search === '') {
            return response()->json(['results' => []]);
        }

        $query = Ticket::with('user:id,name');

        if (! in_array($user->role, ['admin', 'agent'], true)) {
            $query->where('user_id', $user->id);
        }

        $idTerm = ltrim($search, '#');

        $query->where(function ($q) use ($search, $idTerm) {
            if (ctype_digit($idTerm)) {
                $q->orWhere('id', $idTerm);
            }

            $q->orWhere('title', 'like', '%'.$search.'%')
                ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%'));
        });

        $tickets = $query->latest()->limit(10)->get();

        $statusStyles = [
            'open'             => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'Abierto'],
            'pending_approval' => ['bg' => '#fef9c3', 'text' => '#854d0e', 'label' => 'Pendiente de Aprobación'],
            'assigned'         => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'En Proceso'],
            'in_progress'      => ['bg' => '#dbeafe', 'text' => '#1e40af', 'label' => 'En Proceso'],
            'on_hold'          => ['bg' => '#fed7aa', 'text' => '#9a3412', 'label' => 'En Espera'],
            'resolved'         => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Resuelto'],
            'closed'           => ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => 'Cerrado'],
            'rejected'         => ['bg' => '#fecaca', 'text' => '#7f1d1d', 'label' => 'Rechazado'],
            'cancelled'        => ['bg' => '#f3e8ff', 'text' => '#6b21a8', 'label' => 'Cancelado'],
        ];

        $results = $tickets->map(function (Ticket $ticket) use ($statusStyles) {
            $style = $statusStyles[$ticket->status] ?? ['bg' => '#e5e7eb', 'text' => '#374151', 'label' => ucfirst($ticket->status)];

            return [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'status_label' => $style['label'],
                'status_bg' => $style['bg'],
                'status_text' => $style['text'],
                'requester' => $ticket->user->name,
                'created_at' => $ticket->created_at->format('d/m/Y H:i'),
                'url' => route('tickets.show', $ticket),
            ];
        });

        return response()->json(['results' => $results]);
    }

    private function filteredTicketsQuery(Request $request)
    {
        $user = Auth::user();

        // 'category.approvalFlow' y 'approvals' alimentan el selector rápido de
        // estado (opción "Enviar a aprobación" y si ya pasó por aprobación),
        // sin disparar una query por fila.
        $query = Ticket::with(['category.approvalFlow', 'user', 'agent', 'approvals']);

        // admin y agent ven todos los tickets; el resto solo los propios
        if (! in_array($user->role, ['admin', 'agent'], true)) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === '0') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        return $query;
    }

    public function create(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->with(['subcategories' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        // Preselección al llegar desde el catálogo del navbar (?category_id=&subcategory_id=).
        $selectedCategoryId = $request->integer('category_id') ?: null;
        $selectedSubcategoryId = $request->integer('subcategory_id') ?: null;

        return view('tickets.create', compact('categories', 'selectedCategoryId', 'selectedSubcategoryId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            // Solo categorías/subcategorías activas: evita que alguien arme un POST
            // directo con una categoría ya desactivada en la UI.
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            // La subcategoría, si viene, debe pertenecer a la categoría elegida y estar activa.
            'subcategory_id' => ['nullable', Rule::exists('subcategories', 'id')->where('category_id', $request->input('category_id'))->where('is_active', true)],
            'priority'    => 'required|in:low,medium,high,urgent',
        ]);

        $category = Category::with(['approvalFlow.levels'])->find($request->category_id);
        $levels = $category?->approvalFlow?->levels ?? collect();

        $ticket = Ticket::create([
            'title'       => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->input('subcategory_id') ?: null,
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
            $firstLevel->approver?->notify(new ApprovalRequestedNotification($ticket, $firstLevel));
        }

        $ticket->user->notify(new TicketCreatedNotification($ticket));

        return redirect()->route('tickets.index')->with('success', 'Ticket creado exitosamente.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'category.approvalFlow.levels.approver',
            'subcategory',
            'user',
            'agent',
            'approvals.approvalLevel.approver',
            'approvals.approver',
            'approvals.approvedBy',
            'comments.user',
            'activityLogs.user',
            'attachments.user',
            'reminders.user',
            'timeLogs.user',
            'tags',
        ]);

        $agents = User::where('role', 'agent')->orderBy('name')->get();
        // Pool de aprobadores para "Enviar para su aprobación" cuando la
        // categoría no tiene flujo (mismo criterio que en Editar: cualquiera).
        $approvalCandidates = User::orderBy('name')->get();
        $allTags = Tag::orderBy('name')->get();

        // Panel izquierdo: tickets recientes del mismo solicitante (incluye
        // el actual, para poder resaltarlo como seleccionado en la lista).
        $relatedTickets = Ticket::where('user_id', $ticket->user_id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('tickets.show', compact('ticket', 'agents', 'relatedTickets', 'approvalCandidates', 'allTags'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->load([
            'agent',
            'category.approvalFlow.levels.approver',
            'approvals.approvalLevel',
            'approvals.approver',
            'approvals.approvedBy',
        ]);

        $categories = Category::all();
        $agents = User::where('role', 'agent')->orderBy('name')->get();
        // Pool de aprobadores para el override por ticket / asignación ad-hoc:
        // igual que en la config de flujos (admin.approval-flows), sin restringir por rol.
        $approvalCandidates = User::orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'categories', 'agents', 'approvalCandidates'));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        $oldStatus = $ticket->status;
        $oldAssignedTo = $ticket->assigned_to;

        $newAssignedTo = $request->input('assigned_to') ?: null;
        $assignedChanged = $newAssignedTo !== null && (int) $newAssignedTo !== (int) $oldAssignedTo;

        $data = [
            'status'      => $request->validated('status'),
            'priority'    => $request->validated('priority'),
            'assigned_to' => $newAssignedTo,
        ];

        if ($assignedChanged) {
            $data['status'] = 'assigned';
        }

        // Regla #9: resolved_at nunca antes de created_at (ya validado en el Form Request).
        if ($data['status'] === 'resolved' && ! $ticket->resolved_at) {
            $data['resolved_at'] = $request->input('resolved_at') ?: now();
        }

        if ($request->filled('amount')) {
            $data['amount'] = $request->validated('amount');
        }

        $ticket->update($data);

        if ($oldStatus !== $ticket->status) {
            $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, $ticket->status));
        }

        if ($assignedChanged) {
            $ticket->agent->notify(new TicketAssignedNotification($ticket));

            TicketActivityLog::record(
                $ticket,
                Auth::user(),
                'reassigned',
                "Reasignado de #{$oldAssignedTo} a #{$newAssignedTo}"
            );
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket actualizado.');
    }

    /**
     * Cambio de estado "simple" (sin dato adicional): Kanban (drag & drop) y
     * selector rápido de estado comparten este endpoint. Deliberadamente
     * separado de update(): no pasa por UpdateTicketRequest porque solo
     * envía "status", y la autorización aquí es más estricta que
     * TicketPolicy::update (que también permite al creador) — acá solo
     * puede mover el agente asignado o un admin. Las transiciones que
     * requieren datos adicionales (resolución, en espera, aprobación,
     * cancelación) NO pasan por acá — ver hold()/resolution()/cancel() y
     * ApprovalController::sendForApproval().
     */
    public function updateStatus(Request $request, Ticket $ticket, TicketStatusService $statusService)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && (int) $ticket->assigned_to !== (int) $user->id) {
            return response()->json(['message' => 'No tienes permiso para mover este ticket.'], 403);
        }

        $request->validate([
            'status' => ['required', 'in:in_progress'],
        ]);

        $newStatus = $request->input('status');
        $oldStatus = $ticket->status;

        if ($newStatus === $oldStatus) {
            return response()->json(['status' => $ticket->status, 'targets' => $statusService->validTargets($ticket)]);
        }

        if (! $statusService->isSimple($oldStatus, $newStatus)) {
            return response()->json(['message' => 'Esta transición de estado no está permitida desde el estado actual.'], 422);
        }

        $ticket->update(['status' => $newStatus]);

        TicketActivityLog::record($ticket, $user, 'status_changed', "Movido de {$oldStatus} a {$newStatus}.");

        $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, $newStatus));

        // Los targets se recalculan para el estado NUEVO: el selector rápido los
        // usa para encadenar otro cambio sin recargar (ver ticket-status-menu.blade.php).
        return response()->json(['status' => $ticket->status, 'targets' => $statusService->validTargets($ticket)]);
    }

    /**
     * Poner el ticket "En Espera" (on_hold): pausa operativa del agente, sin
     * relación con el flujo de aprobación (pending_approval). Requiere
     * comentario obligatorio, que además queda guardado como comentario
     * interno del ticket para trazabilidad (visible en la pestaña
     * Comentarios e Historial, igual que una nota interna).
     */
    public function hold(Request $request, Ticket $ticket, TicketStatusService $statusService)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && (int) $ticket->assigned_to !== (int) $user->id) {
            return response()->json(['message' => 'No tienes permiso para pausar este ticket.'], 403);
        }

        if (! $statusService->canTransition($ticket->status, 'on_hold')) {
            return response()->json(['message' => 'Este ticket no puede pasar a "En Espera" desde su estado actual.'], 422);
        }

        $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ], [
            'comment.required' => 'Debes indicar el motivo para poner el ticket en espera.',
        ]);

        $oldStatus = $ticket->status;
        $comment = $request->input('comment');

        $ticket->update(['status' => 'on_hold']);

        $ticket->comments()->create([
            'user_id' => $user->id,
            'comment' => $comment,
            'is_internal' => true,
        ]);

        TicketActivityLog::record($ticket, $user, 'on_hold', $comment);

        $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, 'on_hold'));

        return response()->json([
            'status' => $ticket->status,
            'targets' => $statusService->validTargets($ticket),
            'message' => 'Ticket puesto en espera.',
        ]);
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket eliminado.');
    }

    /**
     * Acción "Introducir resolución" del menú Acciones: documenta cómo se
     * resolvió y cierra el ticket como resuelto en un solo paso.
     */
    public function resolution(Request $request, Ticket $ticket, TicketStatusService $statusService)
    {
        $this->authorize('update', $ticket);

        // Único camino a "resolved" en la matriz de estados: in_progress o
        // assigned. Reemplaza la lista fija anterior por la misma fuente de
        // verdad que usan el Kanban y el selector rápido.
        if (! $statusService->canTransition($ticket->status, 'resolved')) {
            $message = 'Este ticket no puede resolverse en su estado actual.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['resolution' => $message]);
        }

        // Regla #7 (ya vigente en UpdateTicketRequest): no se puede resolver sin agente asignado.
        if (! $ticket->assigned_to) {
            $message = 'No puedes marcar el ticket como resuelto sin un agente asignado.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['resolution' => $message]);
        }

        // No se puede resolver con una aprobación (de flujo o ad-hoc) sin
        // votar todavía, aunque el status permita la transición.
        if ($ticket->hasPendingApproval()) {
            $message = 'Este ticket tiene una aprobación pendiente. No puede marcarse como resuelto hasta que se resuelva.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['resolution' => $message]);
        }

        $request->validate([
            'resolution' => ['required', 'string', 'max:5000'],
        ]);

        $oldStatus = $ticket->status;

        $ticket->update([
            'resolution' => $request->input('resolution'),
            'status' => 'resolved',
            'resolved_at' => $ticket->resolved_at ?? now(),
        ]);

        TicketActivityLog::record($ticket, Auth::user(), 'resolved', $request->input('resolution'));

        $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, 'resolved'));

        if ($request->wantsJson()) {
            return response()->json([
                'status' => $ticket->status,
                'targets' => $statusService->validTargets($ticket),
                'message' => 'Resolución registrada.',
            ]);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Resolución registrada.');
    }

    /**
     * Acción "Copiar esta solicitud": mismo solicitante y datos, ticket nuevo
     * en estado abierto sin importar si la categoría tiene flujo configurado.
     */
    public function duplicate(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $copy = Ticket::create([
            'title' => $ticket->title,
            'description' => $ticket->description,
            'category_id' => $ticket->category_id,
            'subcategory_id' => $ticket->subcategory_id,
            'priority' => $ticket->priority,
            'user_id' => $ticket->user_id,
            'status' => 'open',
        ]);

        TicketActivityLog::record($copy, Auth::user(), 'duplicated', "Copiado desde el ticket #{$ticket->id}.");
        TicketActivityLog::record($ticket, Auth::user(), 'duplicated', "Copiado como el ticket #{$copy->id}.");

        $copy->user->notify(new TicketCreatedNotification($copy));

        return redirect()->route('tickets.show', $copy)->with('success', "Ticket duplicado como #{$copy->id}.");
    }

    /**
     * Acción "Cancelar solicitud": requiere motivo obligatorio (regla del
     * pedido), distinto de "Eliminar ticket" que borra el registro completo.
     */
    public function cancel(Request $request, Ticket $ticket, TicketStatusService $statusService)
    {
        $this->authorize('update', $ticket);

        if (! $statusService->canTransition($ticket->status, 'cancelled')) {
            $message = 'Este ticket ya está en un estado final y no puede cancelarse.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['status' => $message]);
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Debes indicar el motivo de la cancelación.',
        ]);

        $oldStatus = $ticket->status;

        $ticket->update(['status' => 'cancelled']);

        TicketActivityLog::record($ticket, Auth::user(), 'cancelled', $request->input('reason'));

        $ticket->user->notify(new TicketStatusUpdatedNotification($ticket, $oldStatus, 'cancelled'));

        if ($request->wantsJson()) {
            return response()->json([
                'status' => $ticket->status,
                'targets' => $statusService->validTargets($ticket),
                'message' => 'Ticket cancelado.',
            ]);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket cancelado.');
    }
}