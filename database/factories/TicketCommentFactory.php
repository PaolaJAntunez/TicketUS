<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TicketComment>
 */
class TicketCommentFactory extends Factory
{
    /**
     * Frases cortas escritas a mano (no Faker\Lorem, que ignora el locale y
     * siempre genera texto pseudo-latín).
     */
    protected array $phrases = [
        'Ya revisé el caso, quedo pendiente de más información.',
        'Se reinició el equipo y el problema persiste.',
        'Contacté al proveedor para dar seguimiento.',
        '¿Podrías confirmar si el problema sigue presente?',
        'Se agenda visita técnica para mañana.',
        'Actualizamos el driver correspondiente.',
        'El usuario confirma que el problema fue resuelto.',
        'Escalado a nivel 2 para revisión adicional.',
        'Se solicita más información sobre el error mostrado.',
        'Verifiqué la conexión de red, todo funciona correctamente.',
        'Reemplazamos el cable dañado.',
        'Pendiente de aprobación del presupuesto para continuar.',
        'Gracias por el reporte, lo atenderemos a la brevedad.',
        'Se realizó respaldo antes de aplicar el cambio.',
        'El ticket fue reasignado al área correspondiente.',
        'Quedo atento a cualquier novedad adicional.',
        'Se coordinó con el usuario un horario para la visita.',
        'Ya se aplicó la solución temporal mientras se resuelve de fondo.',
    ];

    public function definition(): array
    {
        return [
            'comment' => fake()->randomElement($this->phrases),
            'is_internal' => false,
        ];
    }
}
