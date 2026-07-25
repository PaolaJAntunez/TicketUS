<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba de extremo a extremo (vía el ciclo HTTP real: rutas, middleware,
 * FormRequests, controllers y policies) de la única regla añadida sobre el
 * comportamiento original: un agente no puede asignar un ticket a OTRO
 * usuario, solo a sí mismo. Todo lo demás (editar prioridad/estatus,
 * gestionar el flujo de aprobación, etc.) sigue igual que antes.
 */
class TicketAgentPermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        return Category::create([
            'name' => 'Soporte técnico',
            'requires_approval' => false,
            'is_active' => true,
        ]);
    }

    public function test_agent_cannot_assign_ticket_to_another_agent_but_can_self_claim_it(): void
    {
        $agent1 = User::factory()->create(['role' => 'agent']);
        $agent2 = User::factory()->create(['role' => 'agent']);
        $requester = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'title' => 'No enciende la impresora',
            'description' => 'Detalle del problema.',
            'status' => 'open',
            'priority' => 'medium',
            'category_id' => $this->category()->id,
            'user_id' => $requester->id,
            'assigned_to' => null,
        ]);

        // Intento 1: agente intenta asignar el ticket a OTRO agente.
        $response = $this->actingAs($agent1)->put(route('tickets.update', $ticket), [
            'status' => 'open',
            'priority' => 'medium',
            'assigned_to' => $agent2->id,
        ]);

        $response->assertSessionHasErrors('assigned_to');
        $this->assertNull($ticket->fresh()->assigned_to, 'El ticket no debería haber quedado asignado.');

        // Intento 2: el agente SÍ puede tomar el ticket para sí mismo (única
        // excepción: autoasignarse un ticket que está sin asignar).
        $response = $this->actingAs($agent1)->put(route('tickets.update', $ticket), [
            'status' => 'open',
            'priority' => 'medium',
            'assigned_to' => $agent1->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame($agent1->id, $ticket->fresh()->assigned_to);

        // Intento 3: una vez tomado, ni el propio agente ni otro pueden
        // reasignarlo — a partir de acá es exclusivo del admin.
        $response = $this->actingAs($agent1)->put(route('tickets.update', $ticket), [
            'status' => $ticket->fresh()->status,
            'priority' => 'medium',
            'assigned_to' => $agent2->id,
        ]);

        $response->assertSessionHasErrors('assigned_to');
        $this->assertSame($agent1->id, $ticket->fresh()->assigned_to);
    }

    public function test_agent_can_manage_status_of_a_ticket_already_assigned_to_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent']);
        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->category();

        // El ticket llega ya asignado al agente (como si el admin lo hubiera
        // asignado antes) — lo que se prueba acá es el manejo de ESTATUS.
        $ticket = Ticket::create([
            'title' => 'El sistema no responde',
            'description' => 'Detalle del problema.',
            'status' => 'open',
            'priority' => 'medium',
            'category_id' => $category->id,
            'user_id' => $requester->id,
            'assigned_to' => $agent->id,
        ]);

        // a) Cambiar de estado vía el endpoint del Kanban / selector rápido.
        $response = $this->actingAs($agent)
            ->patch(route('tickets.status.update', $ticket), ['status' => 'in_progress']);

        $response->assertOk();
        $this->assertSame('in_progress', $ticket->fresh()->status);

        // b) Ponerlo en espera con comentario obligatorio.
        $response = $this->actingAs($agent)
            ->patch(route('tickets.hold', $ticket), ['comment' => 'Esperando repuesto del proveedor.']);

        $response->assertOk();
        $this->assertSame('on_hold', $ticket->fresh()->status);

        // Vuelve a in_progress para poder resolverlo (resolved solo es
        // alcanzable desde in_progress/assigned).
        $this->actingAs($agent)->patch(route('tickets.status.update', $ticket), ['status' => 'in_progress']);

        // c) Resolverlo con texto de resolución.
        $response = $this->actingAs($agent)
            ->put(route('tickets.resolution', $ticket), ['resolution' => 'Se reemplazó la pieza dañada.']);

        $response->assertRedirect(route('tickets.show', $ticket));
        $ticket->refresh();
        $this->assertSame('resolved', $ticket->status);
        $this->assertSame('Se reemplazó la pieza dañada.', $ticket->resolution);

        // d) Enviar OTRO ticket a aprobación (el anterior ya quedó en un
        // estado final). La categoría no tiene flujo, así que requiere
        // elegir un aprobador ad-hoc; esto NO es "cambiar el flujo de
        // aprobación" (eso es ApprovalController::assign, bloqueado abajo),
        // sino iniciar el proceso sobre un ticket que el agente ya gestiona.
        $ticket2 = Ticket::create([
            'title' => 'Solicita renovación de acceso',
            'description' => 'Detalle del problema.',
            'status' => 'open',
            'priority' => 'medium',
            'category_id' => $category->id,
            'user_id' => $requester->id,
            'assigned_to' => $agent->id,
        ]);

        $response = $this->actingAs($agent)
            ->post(route('tickets.approval.send', $ticket2), ['approver_id' => $admin->id]);

        $response->assertRedirect(route('tickets.show', $ticket2));
        $this->assertSame('pending_approval', $ticket2->fresh()->status);
    }

    public function test_agent_can_still_reassign_an_existing_approval_level_approver(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $otherAgent = User::factory()->create(['role' => 'agent']);
        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->category();

        $ticket = Ticket::create([
            'title' => 'Necesita revisión urgente',
            'description' => 'Detalle del problema.',
            'status' => 'open',
            'priority' => 'medium',
            'category_id' => $category->id,
            'user_id' => $requester->id,
            'assigned_to' => $agent->id,
        ]);

        $response = $this->actingAs($agent)
            ->put(route('tickets.approval.assign', $ticket), ['approver_id' => $otherAgent->id]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('tickets.edit', $ticket));
    }

    public function test_agent_can_edit_priority_and_status_of_a_ticket_assigned_to_another_agent(): void
    {
        $agentA = User::factory()->create(['role' => 'agent']);
        $agentB = User::factory()->create(['role' => 'agent']);
        $requester = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'title' => 'Ticket de otro agente',
            'description' => 'Detalle del problema.',
            'status' => 'open',
            'priority' => 'medium',
            'category_id' => $this->category()->id,
            'user_id' => $requester->id,
            'assigned_to' => $agentB->id,
        ]);

        $response = $this->actingAs($agentA)->put(route('tickets.update', $ticket), [
            'status' => 'open',
            'priority' => 'high',
            'assigned_to' => $agentB->id,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('high', $ticket->fresh()->priority);
    }

    public function test_admin_can_assign_ticket_to_any_agent(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent']);
        $requester = User::factory()->create(['role' => 'user']);

        $ticket = Ticket::create([
            'title' => 'Requiere configuración inicial',
            'description' => 'Detalle del problema.',
            'status' => 'open',
            'priority' => 'medium',
            'category_id' => $this->category()->id,
            'user_id' => $requester->id,
            'assigned_to' => null,
        ]);

        $response = $this->actingAs($admin)->put(route('tickets.update', $ticket), [
            'status' => 'open',
            'priority' => 'medium',
            'assigned_to' => $agent->id,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('tickets.show', $ticket));

        $ticket->refresh();
        $this->assertSame($agent->id, $ticket->assigned_to);
        $this->assertSame('assigned', $ticket->status);
    }
}
