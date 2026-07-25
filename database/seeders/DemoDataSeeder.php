<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use App\Models\ApprovalLevel;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Ticket;
use App\Models\TicketApproval;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Datos ficticios de volumen para demo/pruebas del dashboard (gráficas,
 * indicadores de SLA, carga por agente, aprobaciones). No se registra en
 * DatabaseSeeder a propósito, para no mezclarse con el seed de producción:
 *
 *   php artisan db:seed --class=DemoDataSeeder
 *
 * Es aditivo: no borra ni toca datos existentes (usuarios/tickets/categorías
 * reales). Sí crea 2 flujos de aprobación nuevos ("Compras" y "Gestión de
 * Usuarios") porque esas categorías todavía no tenían uno — sin eso, la
 * única categoría con aprobación configurada sería "Accesos", y la demo de
 * aprobaciones ("pendiente/aprobado/rechazado") se vería muy pobre. Si esas
 * categorías ya tienen flujo configurado cuando se corre, no se tocan.
 */
class DemoDataSeeder extends Seeder
{
    /** @var array<int, string> */
    protected array $usedEmails = [];

    protected array $positions = [
        'Analista de Soporte', 'Técnico de TI', 'Coordinador de Operaciones',
        'Especialista en Redes', 'Asistente Administrativo', 'Analista de Sistemas',
        'Supervisor de TI', 'Ejecutivo de Cuenta', 'Analista Financiero',
    ];

    protected array $approvalComments = [
        'Aprobado, procede sin observaciones.',
        'Validado con el área correspondiente, se autoriza.',
        'Cumple con la política vigente, aprobado.',
        'Autorizado por presupuesto disponible.',
        null, null, // a veces se aprueba sin comentario
    ];

    protected array $rejectionComments = [
        'No cumple con la política actual, se rechaza.',
        'Falta información para poder autorizar.',
        'Excede el presupuesto asignado para el área.',
        'Se rechaza: ya existe una solicitud similar en curso.',
    ];

    public function run(): void
    {
        $this->command?->info('Generando datos de demo...');

        $admins = $this->createUsers(3, 'admin', fn () => 'Administrador de Sistemas');
        $agents = $this->createUsers(8, 'agent', fn () => fake()->randomElement($this->positions));
        $approvers = $this->createUsers(4, 'user', fn () => 'Jefe de Área');
        $requesters = $this->createUsers(15, 'user', fn () => fake()->randomElement($this->positions));

        $flowLevelsByCategory = $this->setupDemoApprovalFlows($approvers);

        $categories = Category::with('subcategories')->get();
        $agentWeights = $this->buildAgentWeights($agents);

        $tickets = collect();
        $ticketCount = fake()->numberBetween(80, 120);

        for ($i = 0; $i < $ticketCount; $i++) {
            $ticket = $this->createTicket($categories, $requesters, $agents, $agentWeights, $flowLevelsByCategory);
            $tickets->push($ticket);
        }

        $this->seedComments($tickets);

        $this->command?->info(sprintf(
            'Listo: %d admins, %d agentes, %d aprobadores, %d solicitantes, %d tickets, %d comentarios, %d registros de aprobación.',
            $admins->count(), $agents->count(), $approvers->count(), $requesters->count(),
            $tickets->count(), TicketComment::count(), TicketApproval::count()
        ));
    }

    /**
     * @return Collection<int, User>
     */
    protected function createUsers(int $count, string $role, \Closure $positionPicker): Collection
    {
        return collect(range(1, $count))->map(function () use ($role, $positionPicker) {
            $name = fake('es_ES')->name();

            return User::factory()->create([
                'name' => $name,
                'email' => $this->makeEmail($name),
                'role' => $role,
                'position' => $positionPicker(),
                'department' => fake()->randomElement(User::DEPARTMENTS),
                'is_active' => true,
            ]);
        });
    }

    protected function makeEmail(string $name): string
    {
        $base = Str::slug($name, '.');
        $email = $base.'@empresa.com';
        $suffix = 1;

        while (in_array($email, $this->usedEmails, true) || User::where('email', $email)->exists()) {
            $email = $base.$suffix.'@empresa.com';
            $suffix++;
        }

        $this->usedEmails[] = $email;

        return $email;
    }

    /**
     * Flujos de aprobación disponibles para la demo, indexados por category_id
     * -> Collection<ApprovalLevel> (orden ascendente). Reutiliza "Accesos" si
     * ya tenía uno configurado; crea "Compras" y "Gestión de Usuarios" si no
     * tenían (son las únicas dos categorías con requires_approval=true sin
     * flujo real, o candidatas razonables para tenerlo).
     *
     * @param  Collection<int, User>  $approvers
     * @return Collection<int, Collection<int, ApprovalLevel>>
     */
    protected function setupDemoApprovalFlows(Collection $approvers): Collection
    {
        $flowLevelsByCategory = collect();

        $existingAccesos = Category::where('name', 'Accesos')->with('approvalFlow.levels')->first();
        if ($existingAccesos?->approvalFlow) {
            $flowLevelsByCategory->put($existingAccesos->id, $existingAccesos->approvalFlow->levels->sortBy('order')->values());
        }

        $demoFlows = [
            'Compras' => 'Aprobación de Compras (Demo)',
            'Gestión de Usuarios' => 'Aprobación de Accesos de Usuario (Demo)',
        ];

        $approverIndex = 0;
        foreach ($demoFlows as $categoryName => $flowName) {
            $category = Category::where('name', $categoryName)->first();

            if (! $category || $category->approvalFlow()->exists()) {
                continue;
            }

            $category->update(['requires_approval' => true]);

            $flow = ApprovalFlow::create([
                'category_id' => $category->id,
                'name' => $flowName,
            ]);

            $levels = collect([1, 2])->map(function (int $order) use ($flow, $approvers, &$approverIndex) {
                $level = ApprovalLevel::create([
                    'approval_flow_id' => $flow->id,
                    'order' => $order,
                    'name' => "Nivel {$order}",
                    'approver_id' => $approvers[$approverIndex % $approvers->count()]->id,
                ]);
                $approverIndex++;

                return $level;
            });

            $flowLevelsByCategory->put($category->id, $levels);
        }

        return $flowLevelsByCategory;
    }

    /**
     * Pesos no uniformes por agente (algunos con más carga que otros).
     *
     * @param  Collection<int, User>  $agents
     * @return array<int, int> user_id => peso
     */
    protected function buildAgentWeights(Collection $agents): array
    {
        // Una distribución fija con "estrellas" y "poco cargados", barajada
        // para que no siempre sea el mismo agente el más ocupado.
        $shape = [10, 8, 7, 5, 4, 3, 2, 1];
        shuffle($shape);

        return $agents->values()->mapWithKeys(fn (User $agent, int $i) => [$agent->id => $shape[$i] ?? 1])->all();
    }

    protected function weightedPick(array $weights): string|int
    {
        $total = array_sum($weights);
        $rand = mt_rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $key => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $key;
            }
        }

        return array_key_last($weights);
    }

    protected function weightedPickUser(array $userWeights): int
    {
        return (int) $this->weightedPick($userWeights);
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @param  Collection<int, User>  $requesters
     * @param  Collection<int, User>  $agents
     * @param  array<int, int>  $agentWeights
     * @param  Collection<int, Collection<int, ApprovalLevel>>  $flowLevelsByCategory
     */
    protected function createTicket(
        Collection $categories,
        Collection $requesters,
        Collection $agents,
        array $agentWeights,
        Collection $flowLevelsByCategory
    ): Ticket {
        $category = $categories->random();
        $subcategory = $category->subcategories->isNotEmpty() && fake()->boolean(80)
            ? $category->subcategories->random()
            : null;

        $priority = $this->weightedPick(['low' => 25, 'medium' => 40, 'high' => 25, 'urgent' => 10]);

        // "en espera" (pending_approval/rejected) solo tiene sentido si la
        // categoría elegida tiene flujo — si no, se fuerza una categoría que
        // sí tenga, para no perder ese % pedido por completo.
        $statusBucket = $this->weightedPick([
            'resolved_bucket' => 60,
            'in_process_bucket' => 20,
            'open' => 10,
            'approval_bucket' => 5,
            'cancelled' => 5,
        ]);

        if ($statusBucket === 'approval_bucket' && ! $flowLevelsByCategory->has($category->id)) {
            $category = Category::find($flowLevelsByCategory->keys()->random());
            $subcategory = null;
        }

        $status = match ($statusBucket) {
            'resolved_bucket' => $this->weightedPick(['resolved' => 70, 'closed' => 30]),
            'in_process_bucket' => $this->weightedPick(['assigned' => 50, 'in_progress' => 50]),
            'approval_bucket' => $this->weightedPick(['pending_approval' => 80, 'rejected' => 20]),
            default => $statusBucket,
        };

        $isTerminal = in_array($status, ['resolved', 'closed', 'cancelled', 'rejected'], true);

        // Tickets terminados: repartidos en los últimos 4 meses (para la
        // tendencia mensual). Tickets aún accionables: más recientes (los
        // viejos ya se habrían resuelto en la vida real) — de ahí sale una
        // mezcla realista de vencidos/no vencidos.
        $createdAt = $isTerminal
            ? Carbon::now()->subDays(fake()->numberBetween(0, 120))
            : Carbon::now()->subDays(fake()->numberBetween(0, 35));
        $createdAt->setTime(fake()->numberBetween(7, 19), fake()->numberBetween(0, 59));

        $slaHours = config("sla.hours.{$priority}", config('sla.default_hours'));
        $dueDate = $createdAt->copy()->addHours($slaHours);

        $resolvedAt = null;
        if (in_array($status, ['resolved', 'closed'], true)) {
            $resolvedAt = $createdAt->copy()->addHours(fake()->numberBetween(1, 120));
            if ($resolvedAt->isFuture()) {
                $resolvedAt = Carbon::now();
            }
        }

        $requester = $requesters->random();

        $assignedTo = null;
        $assignChance = $status === 'open' ? 25 : 90;
        if (fake()->boolean($assignChance)) {
            $assignedTo = $this->weightedPickUser($agentWeights);
        }

        $ticket = Ticket::factory()->make([
            'title' => $this->ticketTitle($category, $subcategory),
            'description' => $this->ticketDescription($category, $subcategory),
            'status' => $status,
            'priority' => $priority,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory?->id,
            'user_id' => $requester->id,
            'assigned_to' => $assignedTo,
            'resolved_at' => $resolvedAt,
            'due_date' => $dueDate,
        ]);
        $ticket->created_at = $createdAt;
        $ticket->updated_at = $resolvedAt ?? $createdAt;
        $ticket->save();

        if ($flowLevelsByCategory->has($category->id)) {
            $this->seedApprovalsForTicket($ticket, $flowLevelsByCategory->get($category->id));
        }

        return $ticket;
    }

    protected function ticketTitle(Category $category, ?Subcategory $subcategory): string
    {
        $base = $subcategory?->name ?? $category->name;
        $issue = fake()->randomElement([
            'no enciende', 'no responde', 'presenta lentitud', 'muestra error de conexión',
            'se congela constantemente', 'no permite iniciar sesión', 'requiere configuración inicial',
            'necesita mantenimiento preventivo', 'genera un error desconocido', 'dejó de funcionar esta mañana',
            'solicita renovación de acceso', 'no carga correctamente', 'requiere actualización',
            'presenta fallas intermitentes', 'necesita revisión urgente',
        ]);

        return "{$base}: {$issue}";
    }

    protected function ticketDescription(Category $category, ?Subcategory $subcategory): string
    {
        $base = $subcategory?->name ?? $category->name;

        $opener = fake()->randomElement([
            'Se reporta un problema relacionado con', 'Buenas, tengo un inconveniente con',
            'Estamos presentando dificultades con', 'Solicito apoyo con', 'Desde esta mañana tengo problemas con',
        ]);

        $closer = fake()->randomElement([
            'Agradezco su pronta atención.', 'Es urgente ya que afecta el trabajo diario.',
            'Quedo atento a cualquier actualización.', 'Favor confirmar el tiempo estimado de solución.',
            'Ya intenté reiniciar el equipo sin éxito.', 'Necesito esto resuelto antes de fin de semana.',
        ]);

        return "{$opener} {$base}. {$closer}";
    }

    /**
     * @param  Collection<int, ApprovalLevel>  $levels
     */
    protected function seedApprovalsForTicket(Ticket $ticket, Collection $levels): void
    {
        $levels = $levels->sortBy('order')->values();

        match (true) {
            $ticket->status === 'pending_approval' => $this->seedPendingApproval($ticket, $levels),
            $ticket->status === 'rejected' => $this->seedRejectedApproval($ticket, $levels),
            in_array($ticket->status, ['resolved', 'closed', 'assigned', 'in_progress'], true)
                => $this->seedFullyApproved($ticket, $levels),
            default => $this->seedUntouchedApproval($ticket, $levels), // open, cancelled
        };
    }

    /**
     * @param  Collection<int, ApprovalLevel>  $levels
     */
    protected function seedPendingApproval(Ticket $ticket, Collection $levels): void
    {
        $approvedThrough = fake()->numberBetween(0, max(0, $levels->count() - 1));

        foreach ($levels as $i => $level) {
            $isApproved = $i < $approvedThrough;

            TicketApproval::create([
                'ticket_id' => $ticket->id,
                'approval_level_id' => $level->id,
                'status' => $isApproved ? 'approved' : 'pending',
                'approved_by' => $isApproved ? $level->approver_id : null,
                'comments' => $isApproved ? fake()->randomElement($this->approvalComments) : null,
                'approved_at' => $isApproved ? $ticket->created_at->copy()->addHours(fake()->numberBetween(1, 48)) : null,
            ]);
        }
    }

    /**
     * @param  Collection<int, ApprovalLevel>  $levels
     */
    protected function seedRejectedApproval(Ticket $ticket, Collection $levels): void
    {
        $rejectAtIndex = fake()->numberBetween(0, $levels->count() - 1);

        foreach ($levels as $i => $level) {
            if ($i < $rejectAtIndex) {
                TicketApproval::create([
                    'ticket_id' => $ticket->id,
                    'approval_level_id' => $level->id,
                    'status' => 'approved',
                    'approved_by' => $level->approver_id,
                    'comments' => fake()->randomElement($this->approvalComments),
                    'approved_at' => $ticket->created_at->copy()->addHours(fake()->numberBetween(1, 48)),
                ]);
            } elseif ($i === $rejectAtIndex) {
                TicketApproval::create([
                    'ticket_id' => $ticket->id,
                    'approval_level_id' => $level->id,
                    'status' => 'rejected',
                    'approved_by' => $level->approver_id,
                    'comments' => fake()->randomElement($this->rejectionComments),
                    'approved_at' => $ticket->created_at->copy()->addHours(fake()->numberBetween(1, 48)),
                ]);
            } else {
                // Nunca se llegó a este nivel (el rechazo es terminal).
                TicketApproval::create([
                    'ticket_id' => $ticket->id,
                    'approval_level_id' => $level->id,
                    'status' => 'pending',
                ]);
            }
        }
    }

    /**
     * @param  Collection<int, ApprovalLevel>  $levels
     */
    protected function seedFullyApproved(Ticket $ticket, Collection $levels): void
    {
        foreach ($levels as $level) {
            TicketApproval::create([
                'ticket_id' => $ticket->id,
                'approval_level_id' => $level->id,
                'status' => 'approved',
                'approved_by' => $level->approver_id,
                'comments' => fake()->randomElement($this->approvalComments),
                'approved_at' => $ticket->created_at->copy()->addHours(fake()->numberBetween(1, 48)),
            ]);
        }
    }

    /**
     * @param  Collection<int, ApprovalLevel>  $levels
     */
    protected function seedUntouchedApproval(Ticket $ticket, Collection $levels): void
    {
        foreach ($levels as $level) {
            TicketApproval::create([
                'ticket_id' => $ticket->id,
                'approval_level_id' => $level->id,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * @param  Collection<int, Ticket>  $tickets
     */
    protected function seedComments(Collection $tickets): void
    {
        foreach ($tickets as $ticket) {
            $count = fake()->numberBetween(1, 4);
            $windowEnd = $ticket->resolved_at ?? Carbon::now();
            if ($windowEnd->lt($ticket->created_at)) {
                $windowEnd = $ticket->created_at->copy()->addHour();
            }

            for ($i = 0; $i < $count; $i++) {
                $useAgent = $ticket->assigned_to && fake()->boolean(60);
                $authorId = $useAgent ? $ticket->assigned_to : $ticket->user_id;
                $createdAt = Carbon::instance(fake()->dateTimeBetween($ticket->created_at, $windowEnd));

                $comment = TicketComment::factory()->make([
                    'ticket_id' => $ticket->id,
                    'user_id' => $authorId,
                    'is_internal' => $useAgent && fake()->boolean(20),
                ]);
                $comment->created_at = $createdAt;
                $comment->updated_at = $createdAt;
                $comment->save();
            }
        }
    }
}
