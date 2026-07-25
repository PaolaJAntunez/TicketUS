<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Frases cortas tipo "asunto" para no depender de Faker\Lorem (que genera
     * texto pseudo-latín sin importar el locale configurado).
     */
    protected array $issues = [
        'no enciende',
        'no responde',
        'presenta lentitud',
        'muestra error de conexión',
        'se congela constantemente',
        'no permite iniciar sesión',
        'requiere configuración inicial',
        'necesita mantenimiento preventivo',
        'genera un error desconocido',
        'dejó de funcionar esta mañana',
        'solicita renovación de acceso',
        'no carga correctamente',
        'requiere actualización',
        'presenta fallas intermitentes',
        'necesita revisión urgente',
    ];

    public function definition(): array
    {
        return [
            'title' => 'Solicitud de soporte: '.fake()->randomElement($this->issues),
            'description' => 'Se reporta un inconveniente. '.fake()->randomElement([
                'Agradezco su pronta atención.',
                'Es urgente ya que afecta el trabajo diario.',
                'Quedo atento a cualquier actualización.',
                'Ya intenté reiniciar el equipo sin éxito.',
            ]),
            'status' => 'open',
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
        ];
    }
}
