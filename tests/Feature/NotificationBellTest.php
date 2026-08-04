<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\NewTicketNotifyAdminsNotification;
use App\Notifications\TicketCreatedNotification;
use App\Services\SafeNotifier;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas HTTP reales de la bandeja de notificaciones del navbar (campana):
 * confirma que las Notification existentes ahora también quedan guardadas
 * en la tabla "notifications" (canal 'database') y que los endpoints de
 * listar/marcar leída/marcar todas funcionan de punta a punta.
 */
class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    private function categoryWithoutFlow(): Category
    {
        return Category::create([
            'name' => 'Soporte técnico',
            'requires_approval' => false,
            'is_active' => true,
        ]);
    }

    /** Crear un ticket deja una notificación de BD real para el solicitante y para cada admin. */
    public function test_creating_a_ticket_persists_database_notifications_for_requester_and_admins(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $category = $this->categoryWithoutFlow();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Impresora no funciona',
            'description' => 'Detalle',
            'category_id' => $category->id,
            'priority' => 'medium',
        ])->assertRedirect(route('tickets.index'));

        $ticket = Ticket::first();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $requester->id,
            'type' => TicketCreatedNotification::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => NewTicketNotifyAdminsNotification::class,
        ]);

        $requesterNotification = $requester->fresh()->notifications()->first();
        $this->assertSame('ticket_created', $requesterNotification->data['type']);
        $this->assertSame(route('tickets.show', $ticket), $requesterNotification->data['url']);
        $this->assertNull($requesterNotification->read_at);
    }

    /** GET /notifications devuelve las últimas notificaciones y el contador de no leídas, tal cual las consume la campana. */
    public function test_notifications_index_endpoint_returns_items_and_unread_count(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->categoryWithoutFlow();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket A', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ]);
        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket B', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ]);

        $response = $this->actingAs($requester)->getJson(route('notifications.index'));
        $response->assertOk();

        $response->assertJsonStructure([
            'unread_count',
            'notifications' => [['id', 'type', 'icon', 'title', 'message', 'url', 'read', 'created_at']],
        ]);
        $this->assertSame(2, $response->json('unread_count'));
        $this->assertCount(2, $response->json('notifications'));
        $this->assertFalse($response->json('notifications.0.read'));
    }

    /** POST /notifications/{id}/read marca una sola notificación como leída y redirige a su ticket. */
    public function test_marking_single_notification_as_read_updates_read_at_and_redirects_to_ticket(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->categoryWithoutFlow();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket con notificación', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ]);
        $ticket = Ticket::first();
        $notification = $requester->fresh()->notifications()->first();

        $response = $this->actingAs($requester)->post(route('notifications.read', $notification->id));
        $response->assertRedirect(route('tickets.show', $ticket));

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0, $requester->fresh()->unreadNotifications()->count());
    }

    /** Un usuario no puede marcar como leída la notificación de otro usuario. */
    public function test_cannot_mark_another_users_notification_as_read(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $stranger = User::factory()->create(['role' => 'user']);
        $category = $this->categoryWithoutFlow();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket privado', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ]);
        $notification = $requester->fresh()->notifications()->first();

        $this->actingAs($stranger)->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    /** POST /notifications/read-all marca TODAS las notificaciones pendientes del usuario, sin tocar las de otros. */
    public function test_mark_all_as_read_only_affects_the_authenticated_users_notifications(): void
    {
        $requester = User::factory()->create(['role' => 'user']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $category = $this->categoryWithoutFlow();

        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket 1', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ]);
        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket 2', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ]);

        $this->assertSame(2, $requester->fresh()->unreadNotifications()->count());
        $this->assertGreaterThan(0, $otherAdmin->fresh()->unreadNotifications()->count());

        $this->actingAs($requester)->postJson(route('notifications.read-all'))->assertOk();

        $this->assertSame(0, $requester->fresh()->unreadNotifications()->count());
        $this->assertGreaterThan(0, $otherAdmin->fresh()->unreadNotifications()->count(), 'No debe afectar las notificaciones de otros usuarios.');
    }

    /**
     * [Regresión] Laravel corta el loop de canales de via() en la primera
     * excepción no atrapada: con ['mail', 'database'] (orden original), un
     * fallo de Postmark (ej. dirección demo marcada "inactive/bounced", muy
     * común en este proyecto) impedía que el canal 'database' llegara a
     * ejecutarse -- la campana se quedaba vacía en silencio para esos
     * usuarios. Por eso el orden real es ['database', 'mail']: la
     * notificación en BD debe sobrevivir SIEMPRE, aunque el mailer falle.
     */
    public function test_database_notification_persists_even_if_mail_channel_throws(): void
    {
        $this->app->bind(MailFactory::class, function () {
            return new class implements MailFactory
            {
                public function mailer($name = null)
                {
                    return new class implements MailerContract
                    {
                        public function to($users) { return $this; }
                        public function cc($users) { return $this; }
                        public function bcc($users) { return $this; }
                        public function raw($text, $callback) { throw new \RuntimeException('Postmark: inactive recipient'); }
                        public function send($view, array $data = [], $callback = null) { throw new \RuntimeException('Postmark: inactive recipient'); }
                        public function sendNow($mailable, array $data = [], $callback = null) { throw new \RuntimeException('Postmark: inactive recipient'); }
                    };
                }
            };
        });

        $requester = User::factory()->create(['role' => 'user']);
        $category = $this->categoryWithoutFlow();

        // store() usa SafeNotifier: el fallo del mailer no debe tumbar la
        // creación del ticket ni la respuesta HTTP.
        $this->actingAs($requester)->post(route('tickets.store'), [
            'title' => 'Ticket con mailer roto', 'description' => 'D', 'category_id' => $category->id, 'priority' => 'medium',
        ])->assertRedirect(route('tickets.index'));

        $this->assertSame(1, Ticket::count());

        // Lo que este test existe para confirmar: pese al fallo del mailer,
        // SÍ quedó la notificación de BD para que la campana la muestre.
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $requester->id,
            'type' => TicketCreatedNotification::class,
        ]);
        $this->assertSame(1, $requester->fresh()->unreadNotifications()->count());
    }
}
