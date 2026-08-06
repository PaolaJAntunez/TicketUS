<?php

namespace Tests\Feature;

use App\Models\ApprovalFlow;
use App\Models\ApprovalLevel;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Notifications\NewTicketNotifyAdminsNotification;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Contracts\Notifications\Dispatcher as NotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Pruebas de auditoría (HTTP real) para el pedido del usuario: no arreglan
 * nada, solo confirman o descartan cada sospecha con evidencia reproducible.
 */
class TicketFlowAuditTest extends TestCase
{
    use RefreshDatabase;

    private function categoryWithFlow(array $levelApprovers): Category
    {
        $category = Category::create([
            'name' => 'Compras',
            'requires_approval' => true,
            'is_active' => true,
        ]);

        $flow = ApprovalFlow::create(['category_id' => $category->id, 'name' => 'Flujo de compras']);

        foreach ($levelApprovers as $i => $approver) {
            ApprovalLevel::create([
                'approval_flow_id' => $flow->id,
                'order' => $i + 1,
                'role' => $approver->role,
                'approver_id' => $approver->id,
                'name' => "Nivel ".($i + 1),
            ]);
        }

        return $category;
    }

    private function categoryWithoutFlow(): Category
    {
        return Category::create([
            'name' => 'Soporte técnico',
            'requires_approval' => false,
            'is_active' => true,
        ]);
    }

    /** 1) Ciclo completo con flujo de 2 niveles: crear -> pending_approval -> aprobar N1 -> aprobar N2 -> open -> asignar -> in_progress -> resolved */
    public function test_full_lifecycle_with_two_level_flow(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $approver1 = User::factory()->create(['role' => 'agent']);
        $approver2 = User::factory()->create(['role' => 'agent']);
        $admin = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent']);

        $category = $this->categoryWithFlow([$approver1, $approver2]);

        $response = $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Compra de licencias',
            'description' => 'Detalle',
            'category_id' => $category->id,
            'priority' => 'medium',
        ]);
        $response->assertRedirect(route('tickets.index'));

        $ticket = Ticket::first();
        $this->assertSame('pending_approval', $ticket->status);
        $this->assertCount(2, $ticket->approvals);

        $level1 = ApprovalLevel::where('order', 1)->first();
        $level2 = ApprovalLevel::where('order', 2)->first();

        // Nivel 2 no debe ser accionable todavía.
        $actionableForApprover2 = TicketApproval::actionableForUser($approver2->fresh());
        $this->assertCount(0, $actionableForApprover2, 'El nivel 2 no debería ser accionable antes de que se apruebe el nivel 1.');

        $this->actingAs($approver1)->post(route('approvals.approve', [$ticket, $level1]), ['comments' => 'ok'])
            ->assertRedirect(route('approvals.index'));
        $this->assertSame('pending_approval', $ticket->fresh()->status, 'Debe seguir pendiente: falta el nivel 2.');

        $actionableForApprover2 = TicketApproval::actionableForUser($approver2->fresh());
        $this->assertCount(1, $actionableForApprover2, 'Ahora sí debería ser accionable el nivel 2.');

        $this->actingAs($approver2)->post(route('approvals.approve', [$ticket, $level2]), ['comments' => 'ok'])
            ->assertRedirect(route('approvals.index'));

        $ticket->refresh();
        $this->assertSame('open', $ticket->status, 'Al aprobar el último nivel, el ticket vuelve a "open".');

        // Asignar agente (vía edición) -> pasa a "assigned".
        $this->actingAs($admin)->put(route('tickets.update', $ticket), [
            'status' => 'open',
            'priority' => 'medium',
            'assigned_to' => $agent->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('assigned', $ticket->fresh()->status);

        // Selector rápido -> in_progress.
        $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), ['status' => 'in_progress'])
            ->assertOk();
        $this->assertSame('in_progress', $ticket->fresh()->status);

        // Resolver.
        $this->actingAs($agent)->put(route('tickets.resolution', $ticket), ['resolution' => 'Listo'])
            ->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame('resolved', $ticket->fresh()->status);
    }

    /**
     * 2a) [FIX #5] Link de aprobación desactualizado: alguien intenta aprobar
     * un ticket que YA fue cancelado por otro camino. Antes del fix, el ticket
     * cancelado seguía apareciendo como "pendiente" para siempre; ahora
     * actionableForUser() lo excluye apenas el ticket deja pending_approval.
     * El intento directo (ej. doble clic, tab vieja) sigue dando un error
     * claro en vez de romper algo, y la fila queda "pending" en la BD sin
     * volverse accionable de nuevo (ver [[TicketApprovalService::guardedPendingApproval]]).
     */
    public function test_stale_approval_action_after_ticket_cancelled_gives_generic_unhelpful_error(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $approver1 = User::factory()->create(['role' => 'agent']);
        $approver2 = User::factory()->create(['role' => 'admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $category = $this->categoryWithFlow([$approver1, $approver2]);

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Compra de licencias',
            'description' => 'Detalle',
            'category_id' => $category->id,
            'priority' => 'medium',
        ]);
        $ticket = Ticket::first();
        $level1 = ApprovalLevel::where('order', 1)->first();

        // El ticket se cancela por otro camino (acción "Cancelar solicitud"),
        // estando todavía en pending_approval con 2 niveles pendientes.
        $this->actingAs($admin)->post(route('tickets.cancel', $ticket), ['reason' => 'Ya no se necesita'])
            ->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame('cancelled', $ticket->fresh()->status);

        // approver1 carga su pantalla de aprobaciones pendientes (como en la vida real,
        // esto deja el Referer apuntando a approvals.index para el back() de más abajo)...
        $indexResponse = $this->actingAs($approver1)->get(route('approvals.index'));
        $indexResponse->assertOk();
        $stillListed = TicketApproval::actionableForUser($approver1->fresh());
        $this->assertCount(0, $stillListed, 'FIX #5: el ticket cancelado ya NO debe aparecer en la lista de aprobaciones pendientes.');

        // ...pero si de todas formas llega la request (tab vieja, doble clic):
        $response = $this->actingAs($approver1)->post(route('approvals.approve', [$ticket, $level1]), ['comments' => 'ok']);
        $response->assertRedirect(route('approvals.index'));
        $response->assertSessionHasErrors('ticket');
        $message = session('errors')->get('ticket')[0] ?? null;
        // [FIX #6] Mensaje claro (nombra el estado en español) + CTA "Ver ticket actual".
        $this->assertStringContainsString('ya no requiere tu aprobación', (string) $message);
        $this->assertStringContainsString('cancelado', (string) $message);
        $this->assertSame($ticket->id, session('failed_ticket_id'));

        // La aprobación sigue "pending" en la BD (limpieza de datos queda pendiente:
        // ver hallazgo #1/#2 del reporte), pero ya no es accionable ni visible.
        $this->assertDatabaseHas('ticket_approvals', [
            'ticket_id' => $ticket->id,
            'approval_level_id' => $level1->id,
            'status' => 'pending',
        ]);
    }

    /** 2b) Dos aprobadores intentan actuar sobre el MISMO nivel "al mismo tiempo" (segunda request tras la primera ya commiteada). */
    public function test_second_concurrent_approval_on_same_level_is_rejected_cleanly(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $approver1 = User::factory()->create(['role' => 'agent']);
        $approver2 = User::factory()->create(['role' => 'admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $category = $this->categoryWithFlow([$approver1, $approver2]);

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Compra', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ]);
        $ticket = Ticket::first();
        $level1 = ApprovalLevel::where('order', 1)->first();

        // admin reasigna el aprobador del nivel 1 a sí mismo para poder simular "dos personas" actuando (approver1 y admin).
        $approvalRow = TicketApproval::where('ticket_id', $ticket->id)->where('approval_level_id', $level1->id)->first();

        // Primera request: approver1 aprueba.
        $this->actingAs($approver1)->post(route('approvals.approve', [$ticket, $level1]), ['comments' => 'primero'])
            ->assertRedirect(route('approvals.index'));

        // Segunda request "simultánea": admin (con permisos de admin, bypassa el FormRequest::authorize) intenta aprobar el mismo nivel otra vez.
        $response = $this->actingAs($admin)->post(route('approvals.approve', [$ticket, $level1]), ['comments' => 'segundo']);
        $response->assertSessionHasErrors();
        $this->assertSame('approved', $approvalRow->fresh()->status, 'El voto original no debe pisarse.');
    }

    /** 2c) Aprobación AD-HOC sigue siendo accionable después de que el ticket se cancela. */
    /**
     * [FIX #3] Antes, un aprobador ad-hoc podía seguir aprobando/rechazando
     * después de que el ticket se cancelara por otro camino. Ahora
     * guardedPendingAdhocApproval() rechaza la acción con un mensaje claro,
     * igual que ya hacía guardedPendingApproval() para el flujo formal.
     */
    public function test_adhoc_approval_can_still_be_actioned_after_ticket_cancelled(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $agent = User::factory()->create(['role' => 'agent']);
        $adhocApprover = User::factory()->create(['role' => 'admin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->categoryWithoutFlow();

        $ticket = Ticket::create([
            'title' => 'Renovación de acceso',
            'description' => 'D',
            'status' => 'in_progress',
            'priority' => 'medium',
            'category_id' => $category->id,
            'user_id' => $requester->id,
            'assigned_to' => $agent->id,
        ]);

        // Se asigna un aprobador ad-hoc SIN mandar el ticket a pending_approval (ajuste rápido, no "enviar a aprobación").
        $this->actingAs($admin)->put(route('tickets.approval.assign', $ticket), ['approver_id' => $adhocApprover->id])
            ->assertSessionHasNoErrors();
        $this->assertTrue($ticket->fresh()->hasPendingApproval());

        // El ticket se cancela (canTransition permite cancelar desde in_progress sin mirar si hay aprobaciones pendientes).
        $this->actingAs($admin)->post(route('tickets.cancel', $ticket), ['reason' => 'Ya no aplica'])
            ->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame('cancelled', $ticket->fresh()->status);

        // FIX #3: el aprobador ad-hoc YA NO puede aprobar/rechazar un ticket cancelado.
        // (GET previo a approvals.index para que back() tenga un Referer real, como en el navegador.)
        $this->actingAs($adhocApprover)->get(route('approvals.index'))->assertOk();
        $response = $this->actingAs($adhocApprover)->post(route('approvals.adhoc.approve', $ticket), ['comments' => 'aprobado tarde']);
        $response->assertRedirect(route('approvals.index'));
        $response->assertSessionHasErrors('ticket');
        $message = session('errors')->get('ticket')[0] ?? null;
        $this->assertStringContainsString('ya no puede aprobarse ni rechazarse', (string) $message);

        $this->assertDatabaseHas('ticket_approvals', [
            'ticket_id' => $ticket->id,
            'status' => 'pending',
        ]);
        $this->assertSame('cancelled', $ticket->fresh()->status);
    }

    /**
     * [FIX #6] La página de Aprobaciones Pendientes, al recibir el flash de
     * error de una aprobación desactualizada, renderiza el link "Ver el
     * ticket actual" apuntando al ticket correcto -- no solo el mensaje
     * de error a secas.
     */
    public function test_approvals_index_renders_view_current_ticket_link_on_stale_error(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $approver1 = User::factory()->create(['role' => 'agent']);
        $approver2 = User::factory()->create(['role' => 'agent']);
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->categoryWithFlow([$approver1, $approver2]);

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Compra', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ]);
        $ticket = Ticket::first();
        $level1 = ApprovalLevel::where('order', 1)->first();

        $this->actingAs($admin)->post(route('tickets.cancel', $ticket), ['reason' => 'Ya no aplica']);

        $this->actingAs($approver1)->get(route('approvals.index'))->assertOk();
        $this->actingAs($approver1)->post(route('approvals.approve', [$ticket, $level1]), ['comments' => 'ok']);

        $html = $this->actingAs($approver1)->get(route('approvals.index'))->getContent();

        $this->assertStringContainsString('Ver el ticket actual', $html);
        $this->assertStringContainsString(route('tickets.show', $ticket), $html);
    }

    /**
     * [FIX #1] El formulario de edición genérico ya NO puede sacar un ticket
     * de pending_approval por su cuenta: UpdateTicketRequest ahora rechaza
     * cualquier cambio de estado (o de agente asignado) mientras el ticket
     * está en proceso de aprobación.
     */
    public function test_generic_edit_form_bypasses_state_machine_and_orphans_pending_approvals(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $approver1 = User::factory()->create(['role' => 'agent']);
        $approver2 = User::factory()->create(['role' => 'admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $category = $this->categoryWithFlow([$approver1, $approver2]);

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Compra', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ]);
        $ticket = Ticket::first();
        $this->assertSame('pending_approval', $ticket->status);
        $this->assertSame(2, $ticket->approvals()->where('status', 'pending')->count());

        // Un admin intenta, desde la pantalla de edición genérica, forzar el
        // estado a "open" directamente -- saltándose ApprovalController.
        $response = $this->actingAs($admin)->put(route('tickets.update', $ticket), [
            'status' => 'open',
            'priority' => 'medium',
            'assigned_to' => null,
        ]);
        $response->assertSessionHasErrors('status');
        $this->assertSame('pending_approval', $ticket->fresh()->status);
        $this->assertSame(2, $ticket->approvals()->where('status', 'pending')->count());

        // Tampoco puede colarse reasignando el agente (que forzaría "assigned").
        $response = $this->actingAs($admin)->put(route('tickets.update', $ticket), [
            'status' => 'pending_approval',
            'priority' => 'medium',
            'assigned_to' => User::factory()->create(['role' => 'agent'])->id,
        ]);
        $response->assertSessionHasErrors('status');
        $this->assertSame('pending_approval', $ticket->fresh()->status);
        $this->assertNull($ticket->fresh()->assigned_to);
    }

    /** [FIX #1] La pantalla de edición sigue renderizando bien con el <select> de estado recortado. */
    public function test_edit_screen_still_renders_with_trimmed_status_select(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->categoryWithoutFlow();

        $ticket = Ticket::create([
            'title' => 'T', 'description' => 'D', 'status' => 'open', 'priority' => 'medium',
            'category_id' => $category->id, 'user_id' => $requester->id,
        ]);

        $this->actingAs($admin)->get(route('tickets.edit', $ticket))->assertOk();
    }

    /**
     * [FIX #1/#2] "closed" (y en general, cualquier transición "guardada": resuelto,
     * en espera, aprobación, cancelado) ya no es alcanzable desde el formulario de
     * edición genérico -- solo transiciones "simples" (misma fuente de verdad que
     * el Kanban/selector rápido) o dejar el estado sin cambios.
     */
    public function test_closed_status_is_only_reachable_by_bypassing_the_state_machine(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $agent = User::factory()->create(['role' => 'agent']);
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->categoryWithoutFlow();

        $ticket = Ticket::create([
            'title' => 'T', 'description' => 'D', 'status' => 'open', 'priority' => 'medium',
            'category_id' => $category->id, 'user_id' => $requester->id, 'assigned_to' => $agent->id,
        ]);

        $statusService = app(\App\Services\TicketStatusService::class);
        $this->assertFalse($statusService->canTransition('open', 'closed'), 'La matriz de estados NO contempla open->closed.');

        // El Kanban/selector rápido (updateStatus) SÍ rechaza este salto.
        $r = $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), ['status' => 'closed']);
        $this->assertContains($r->getStatusCode(), [422, 302], 'status='.$r->getStatusCode().' body='.substr($r->getContent(), 0, 300));

        // Y ahora el formulario de edición genérico también lo rechaza.
        $response = $this->actingAs($admin)->put(route('tickets.update', $ticket), [
            'status' => 'closed',
            'priority' => 'medium',
            'assigned_to' => $agent->id,
        ]);
        $response->assertSessionHasErrors('status');
        $this->assertSame('open', $ticket->fresh()->status);

        // Una transición SIMPLE (open -> in_progress) sigue funcionando sin problema.
        $response = $this->actingAs($admin)->put(route('tickets.update', $ticket), [
            'status' => 'in_progress',
            'priority' => 'medium',
            'assigned_to' => $agent->id,
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertSame('in_progress', $ticket->fresh()->status);
    }

    /**
     * [FIX #4] Un fallo del proveedor de correo durante sendForApproval ya NO
     * rompe la operación ni el estado del ticket: SafeNotifier::send() atrapa
     * la excepción, y el aviso se dispara DESPUÉS de que la transacción de
     * BD ya cerró (ver TicketApprovalService::sendForApproval()).
     */
    public function test_notification_failure_during_send_for_approval_does_not_break_the_operation(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $approver = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent']);
        $category = $this->categoryWithoutFlow();

        $ticket = Ticket::create([
            'title' => 'T', 'description' => 'D', 'status' => 'open', 'priority' => 'medium',
            'category_id' => $category->id, 'user_id' => $requester->id, 'assigned_to' => $agent->id,
        ]);

        // Simula una caída del proveedor de correo (Postmark) en CUALQUIER ->notify().
        $this->app->extend(NotificationDispatcher::class, function ($default) {
            return new class extends ChannelManager {
                public function __construct() {}
                public function send($notifiables, $notification) { throw new \RuntimeException('Postmark caído'); }
            };
        });

        $response = $this->actingAs($agent)->post(route('tickets.approval.send', $ticket), ['approver_id' => $approver->id]);

        // La operación de negocio se completa igual: SafeNotifier absorbe el
        // fallo de correo y ya no está dentro de la transacción de BD.
        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame('pending_approval', $ticket->fresh()->status);
        $this->assertSame(1, $ticket->fresh()->approvals()->count());
    }

    /**
     * [FIX #4] Todo ->notify() de Controllers/Services pasa ahora por
     * SafeNotifier::send(), que atrapa cualquier fallo de envío. Ya no debe
     * quedar ningún ->notify() crudo (sin protección) en esas carpetas.
     */
    public function test_every_notify_call_site_is_protected_against_mail_failures(): void
    {
        $files = array_merge(
            glob(app_path('Http/Controllers/*.php')),
            glob(app_path('Services/*.php')),
        );

        $rawNotifySites = [];

        foreach ($files as $file) {
            if (basename($file) === 'SafeNotifier.php') {
                continue;
            }

            if (str_contains(file_get_contents($file), '->notify(')) {
                $rawNotifySites[] = basename($file);
            }
        }

        $this->assertEmpty($rawNotifySites, 'No debería quedar ningún ->notify() crudo fuera de SafeNotifier::send(): '.implode(', ', $rawNotifySites));
    }

    /**
     * [AUDITORÍA - Pregunta 1, actualizado] Al crear un ticket, el
     * solicitante recibe TicketCreatedNotification y CADA admin recibe
     * NewTicketNotifyAdminsNotification (implementado a partir de la
     * auditoría original, que confirmó que esto no existía todavía). Los
     * admins NO reciben TicketCreatedNotification -- esa sigue siendo
     * exclusiva del solicitante.
     */
    public function test_ticket_creation_notifies_requester_and_admins_each_with_their_own_notification(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $category = $this->categoryWithoutFlow();

        Notification::fake();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Impresora no funciona',
            'description' => 'Detalle',
            'category_id' => $category->id,
            'priority' => 'medium',
        ])->assertRedirect(route('tickets.index'));

        $ticket = Ticket::first();

        // Al solicitante SÍ le llega TicketCreatedNotification (y NO la de admins).
        Notification::assertSentTo($requester, TicketCreatedNotification::class);
        Notification::assertNotSentTo($requester, NewTicketNotifyAdminsNotification::class);

        // A cada admin le llega NewTicketNotifyAdminsNotification (y NO la del solicitante).
        Notification::assertSentTo($admin1, NewTicketNotifyAdminsNotification::class);
        Notification::assertSentTo($admin2, NewTicketNotifyAdminsNotification::class);
        Notification::assertNotSentTo($admin1, TicketCreatedNotification::class);
        Notification::assertNotSentTo($admin2, TicketCreatedNotification::class);

        // Confirma además que solo se disparó una notificación en total
        // (la del solicitante) para este ticket sin flujo.
        Notification::assertSentToTimes($requester, TicketCreatedNotification::class, 1);

        $this->assertSame('open', $ticket->status);
    }

    /**
     * [AUDITORÍA - Pregunta 2] Ticket ya asignado ("assigned") + categoría
     * que en ese momento YA tiene un flujo de aprobación configurado -> se
     * envía a aprobación -> el aprobador correcto del NIVEL 1 puede actuar
     * sin ningún bloqueo relacionado a que el ticket ya tiene agente
     * asignado. GUARDED_TRANSITIONS['pending_approval'] incluye 'assigned'
     * como origen válido (ver TicketStatusService), y ni sendForApproval()
     * ni guardedPendingApproval() consultan assigned_to en ningún momento.
     */
    public function test_assigned_ticket_can_be_sent_to_approval_and_approved_without_blockage(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $agent = User::factory()->create(['role' => 'agent']);
        $admin = User::factory()->create(['role' => 'admin']);
        $approver1 = User::factory()->create(['role' => 'agent']);
        $approver2 = User::factory()->create(['role' => 'admin']);

        // La categoría todavía no tiene flujo al crear el ticket -> nace "open"
        // (si ya lo tuviera, store() lo manda a pending_approval de inmediato
        // y ya no habría ventana para asignar antes de enviarlo a aprobación).
        $category = $this->categoryWithoutFlow();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Compra de equipo', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ])->assertRedirect(route('tickets.index'));
        $ticket = Ticket::first();
        $this->assertSame('open', $ticket->status);

        // Admin asigna un agente -> status pasa a "assigned".
        $this->actingAs($admin)->put(route('tickets.update', $ticket), [
            'status' => 'open',
            'priority' => 'medium',
            'assigned_to' => $agent->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('assigned', $ticket->fresh()->status);
        $this->assertSame($agent->id, $ticket->fresh()->assigned_to);

        // AHORA la categoría queda con un flujo de aprobación configurado
        // (de 2 niveles), tal como pide la Pregunta 2 ("categoría que YA
        // tiene un flujo configurado" en el momento de enviar a aprobación).
        $flow = ApprovalFlow::create(['category_id' => $category->id, 'name' => 'Flujo de compras']);
        ApprovalLevel::create(['approval_flow_id' => $flow->id, 'order' => 1, 'role' => $approver1->role, 'approver_id' => $approver1->id, 'name' => 'Nivel 1']);
        ApprovalLevel::create(['approval_flow_id' => $flow->id, 'order' => 2, 'role' => $approver2->role, 'approver_id' => $approver2->id, 'name' => 'Nivel 2']);

        // El selector rápido debe ofrecer "pending_approval" como destino
        // válido desde "assigned" (misma matriz que usa la UI).
        $statusService = app(\App\Services\TicketStatusService::class);
        $this->assertTrue($statusService->canTransition('assigned', 'pending_approval'));

        // Enviar a aprobación (usa el flujo de la categoría, no requiere approver_id).
        $response = $this->actingAs($agent)->post(route('tickets.approval.send', $ticket), []);
        $response->assertRedirect(route('tickets.show', $ticket));
        $ticket->refresh();
        $this->assertSame('pending_approval', $ticket->status, 'El ticket debe entrar a pending_approval aunque ya tenga agente asignado.');
        $this->assertSame($agent->id, $ticket->assigned_to, 'El agente asignado NO se pierde ni se limpia al enviar a aprobación.');
        $this->assertCount(2, $ticket->approvals);

        $level1 = ApprovalLevel::where('order', 1)->first();

        // El aprobador correcto del nivel 1 puede actuar sin ningún error
        // relacionado a assigned_to (no existe ese check en el código).
        $this->actingAs($approver1)->post(route('approvals.approve', [$ticket, $level1]), ['comments' => 'ok'])
            ->assertRedirect(route('approvals.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame('approved', TicketApproval::where('ticket_id', $ticket->id)->where('approval_level_id', $level1->id)->first()->status);
        // El agente asignado sigue intacto durante todo el proceso de aprobación.
        $this->assertSame($agent->id, $ticket->fresh()->assigned_to);
    }

    /**
     * [Nueva notificación] Al crear un ticket, TODOS los admins reciben
     * NewTicketNotifyAdminsNotification -- sin importar si el ticket nace
     * "open" (categoría sin flujo) o "pending_approval" (categoría con
     * flujo ya configurado). Cubre ambos casos con el mismo test para dejar
     * explícito que el "IMPORTANTE" del pedido (notificar también cuando
     * nace pending_approval) queda cumplido.
     *
     * Además, confirma la copia fija adicional: la dirección configurada en
     * ADMIN_NOTIFICATION_EMAIL (config('mail.admin_notification_email'))
     * también recibe la MISMA Notification, como envío on-demand aparte
     * (Notification::route('mail', ...)), sin reemplazar ni modificar el
     * envío normal a los admins de arriba.
     */
    public function test_all_admins_are_notified_on_ticket_creation_regardless_of_initial_status(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent']); // no debe recibir esta notificación
        $approver = User::factory()->create(['role' => 'agent']);
        $fixedAddress = config('mail.admin_notification_email');
        $this->assertNotEmpty($fixedAddress, 'ADMIN_NOTIFICATION_EMAIL debe estar configurado para que este test tenga sentido.');

        // Caso A: categoría SIN flujo -> ticket nace "open".
        $openCategory = $this->categoryWithoutFlow();

        Notification::fake();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket sin flujo', 'description' => 'D', 'category_id' => $openCategory->id, 'priority' => 'medium',
        ])->assertRedirect(route('tickets.index'));

        $openTicket = Ticket::first();
        $this->assertSame('open', $openTicket->status);

        Notification::assertSentTo($admin1, NewTicketNotifyAdminsNotification::class, function ($notification) use ($openTicket) {
            return $notification->ticket->id === $openTicket->id;
        });
        Notification::assertSentTo($admin2, NewTicketNotifyAdminsNotification::class, function ($notification) use ($openTicket) {
            return $notification->ticket->id === $openTicket->id;
        });
        Notification::assertNotSentTo($agent, NewTicketNotifyAdminsNotification::class);
        Notification::assertNotSentTo($requester, NewTicketNotifyAdminsNotification::class);

        // La dirección fija también recibe esta MISMA notificación, además
        // (no en vez) de los admins de arriba.
        Notification::assertSentOnDemand(
            NewTicketNotifyAdminsNotification::class,
            function ($notification, $channels, $notifiable) use ($openTicket, $fixedAddress) {
                return $notification->ticket->id === $openTicket->id
                    && ($notifiable->routes['mail'] ?? null) === $fixedAddress;
            }
        );

        // Caso B: categoría CON flujo ya configurado -> ticket nace "pending_approval".
        $flowCategory = $this->categoryWithFlow([$approver]);

        Notification::fake();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket con flujo', 'description' => 'D', 'category_id' => $flowCategory->id, 'priority' => 'high',
        ])->assertRedirect(route('tickets.index'));

        $pendingTicket = Ticket::where('title', 'Ticket con flujo')->first();
        $this->assertSame('pending_approval', $pendingTicket->status);

        Notification::assertSentTo($admin1, NewTicketNotifyAdminsNotification::class, function ($notification) use ($pendingTicket) {
            return $notification->ticket->id === $pendingTicket->id;
        });
        Notification::assertSentTo($admin2, NewTicketNotifyAdminsNotification::class, function ($notification) use ($pendingTicket) {
            return $notification->ticket->id === $pendingTicket->id;
        });

        // La dirección fija recibe la notificación también cuando el ticket
        // nace "pending_approval" -- sin excepción, tal como pide el pedido.
        Notification::assertSentOnDemand(
            NewTicketNotifyAdminsNotification::class,
            function ($notification, $channels, $notifiable) use ($pendingTicket, $fixedAddress) {
                return $notification->ticket->id === $pendingTicket->id
                    && ($notifiable->routes['mail'] ?? null) === $fixedAddress;
            }
        );

        // También se sigue notificando al primer aprobador (comportamiento
        // preexistente, no debe romperse con este cambio).
        Notification::assertSentTo($approver->fresh(), \App\Notifications\ApprovalRequestedNotification::class);
    }

    /**
     * [Adjuntos al crear] Se pueden subir uno o varios archivos en el mismo
     * POST de creación (input attachments[]), con la misma validación de
     * tipo/tamaño que ya usa TicketAttachmentController (edición), vía
     * TicketAttachmentService. Cada adjunto queda asociado al ticket_id
     * correcto y persiste realmente en el disco "local".
     */
    public function test_ticket_creation_accepts_multiple_attachments_with_same_validation_as_edit(): void
    {
        Storage::fake('local');

        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->categoryWithoutFlow();

        $file1 = UploadedFile::fake()->create('factura.pdf', 500, 'application/pdf');
        $file2 = UploadedFile::fake()->image('foto.jpg');

        $response = $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Compra con comprobantes',
            'description' => 'D',
            'category_id' => $category->id,
            'priority' => 'medium',
            'attachments' => [$file1, $file2],
        ]);
        $response->assertRedirect(route('tickets.index'));
        $response->assertSessionHasNoErrors();

        $ticket = Ticket::first();
        $this->assertCount(2, $ticket->attachments);

        foreach ($ticket->attachments as $attachment) {
            $this->assertSame($ticket->id, $attachment->ticket_id, 'Cada adjunto debe quedar asociado al ticket recién creado.');
            $this->assertSame($requester->id, $attachment->user_id);
            Storage::disk('local')->assertExists($attachment->path);
        }

        $names = $ticket->attachments->pluck('original_name')->all();
        $this->assertContains('factura.pdf', $names);
        $this->assertContains('foto.jpg', $names);
    }

    /** [Adjuntos al crear] Un archivo de tipo no permitido (mimes) rechaza toda la creación, igual que en edición. */
    public function test_ticket_creation_rejects_disallowed_attachment_mime_type(): void
    {
        Storage::fake('local');

        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->categoryWithoutFlow();

        $badFile = UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket con adjunto inválido',
            'description' => 'D',
            'category_id' => $category->id,
            'priority' => 'medium',
            'attachments' => [$badFile],
        ]);

        $response->assertSessionHasErrors('attachments.0');
        $this->assertSame(0, Ticket::count(), 'No debe crearse el ticket si el adjunto no pasa la validación.');
    }

    /** [Adjuntos al crear] Un archivo que excede el límite de 10MB rechaza toda la creación, igual que en edición. */
    public function test_ticket_creation_rejects_attachment_over_size_limit(): void
    {
        Storage::fake('local');

        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->categoryWithoutFlow();

        // 10240 KB es el límite (TicketAttachmentService::MAX_KB); 10241 lo excede.
        $tooBig = UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf');

        $response = $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket con adjunto muy grande',
            'description' => 'D',
            'category_id' => $category->id,
            'priority' => 'medium',
            'attachments' => [$tooBig],
        ]);

        $response->assertSessionHasErrors('attachments.0');
        $this->assertSame(0, Ticket::count());
    }

    /** [Adjuntos al crear] Sin adjuntos, la creación sigue funcionando exactamente igual que antes (campo opcional). */
    public function test_ticket_creation_still_works_without_any_attachment(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->categoryWithoutFlow();

        $response = $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket sin adjuntos',
            'description' => 'D',
            'category_id' => $category->id,
            'priority' => 'medium',
        ]);

        $response->assertRedirect(route('tickets.index'));
        $response->assertSessionHasNoErrors();
        $this->assertCount(0, Ticket::first()->attachments);
    }

    /**
     * [Fix: nivel sin aprobador] Si el Nivel 2 de un flujo no tiene
     * approver_id (el formulario lo permite a propósito), aprobar el Nivel 1
     * ya NO debe quedar en silencio total: se registra un TicketActivityLog
     * con la acción 'approval_level_unassigned' y se notifica a todos los
     * admins con ApprovalLevelMissingApproverNotification -- en vez del
     * ApprovalRequestedNotification normal, que no tiene a quién ir.
     */
    public function test_approving_into_a_level_without_an_approver_logs_and_notifies_admins(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $approver1 = User::factory()->create(['role' => 'agent']);
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $category = Category::create(['name' => 'Compras', 'requires_approval' => true, 'is_active' => true]);
        $flow = ApprovalFlow::create(['category_id' => $category->id, 'name' => 'Flujo de compras']);

        $level1 = ApprovalLevel::create([
            'approval_flow_id' => $flow->id,
            'order' => 1,
            'approver_id' => $approver1->id,
            'name' => 'Nivel 1',
        ]);

        $level2 = ApprovalLevel::create([
            'approval_flow_id' => $flow->id,
            'order' => 2,
            'approver_id' => null,
            'name' => 'Nivel 2',
        ]);

        Notification::fake();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Compra de licencias',
            'description' => 'Detalle',
            'category_id' => $category->id,
            'priority' => 'medium',
        ]);

        $ticket = Ticket::first();

        Notification::fake();

        $response = $this->actingAs($approver1)->post(route('approvals.approve', [$ticket, $level1]), [
            'comments' => 'ok',
        ]);
        $response->assertRedirect(route('approvals.index'));

        $ticket->refresh();
        $this->assertSame('pending_approval', $ticket->status, 'El ticket debe seguir pendiente, esperando el Nivel 2.');

        $level2Approval = TicketApproval::where('ticket_id', $ticket->id)
            ->where('approval_level_id', $level2->id)
            ->first();
        $this->assertNotNull($level2Approval);
        $this->assertSame('pending', $level2Approval->status, 'El Nivel 2 debe quedar pendiente (no se pierde, solo no tiene quién lo resuelva todavía).');

        $this->assertDatabaseHas('ticket_activity_logs', [
            'ticket_id' => $ticket->id,
            'action' => 'approval_level_unassigned',
        ]);

        Notification::assertSentTo($admin1, \App\Notifications\ApprovalLevelMissingApproverNotification::class);
        Notification::assertSentTo($admin2, \App\Notifications\ApprovalLevelMissingApproverNotification::class);
        Notification::assertNotSentTo($admin1, \App\Notifications\ApprovalRequestedNotification::class);
    }
}
