<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Catálogo de categorías/subcategorías de solicitudes, inspirado en el estilo
 * de catálogo de ServiceDesk Plus (genérico, sin marcas ni sistemas propios).
 *
 * Se agrega junto a las categorías ya existentes (Hardware, Software, Red,
 * Accesos, Compras) en vez de reemplazarlas: esas tienen tickets y flujos de
 * aprobación reales enganchados. firstOrCreate hace el seeder idempotente.
 */
class TicketCatalogSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalog() as $categoryName => $data) {
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['description' => $data['description']]
            );

            foreach ($data['subcategories'] as $subcategoryName) {
                $category->subcategories()->firstOrCreate(['name' => $subcategoryName]);
            }
        }
    }

    protected function catalog(): array
    {
        return [
            'Infraestructura' => [
                'description' => 'Servicios e incidentes sobre la infraestructura tecnológica de la organización (red, servidores, directorio, correo, VPN, antivirus).',
                'subcategories' => [
                    'Servicio en Directorio Activo',
                    'Servicio de Antivirus',
                    'Servicio de Red',
                    'Servicio de VPN',
                    'Servicio de Servidores',
                    'Incidente de Antivirus',
                    'Incidente de Correo',
                    'Incidente de Navegación',
                    'Incidente de Red',
                    'Incidente de VPN',
                    'Incidente de Servidor',
                ],
            ],
            'Soporte Técnico' => [
                'description' => 'Incidentes de equipo y software de uso cotidiano del usuario final (computadora, impresora, correo, telefonía, escáner).',
                'subcategories' => [
                    'Incidente de Correo Electrónico',
                    'Incidente de Computadora',
                    'Incidente de Software',
                    'Incidente de Impresora',
                    'Incidente de Red',
                    'Incidente de Telefonía',
                    'Incidente de Escáner',
                ],
            ],
            'Soporte de Aplicaciones' => [
                'description' => 'Servicios e incidentes sobre sistemas internos y bases de datos.',
                'subcategories' => [
                    'Servicio en Sistema Interno',
                    'Incidente en Sistema Interno',
                    'Servicio en Base de Datos',
                    'Incidente en Base de Datos',
                ],
            ],
            'Gestión de Usuarios' => [
                'description' => 'Altas, bajas y cambios de permisos de cuentas de usuario.',
                'subcategories' => [
                    'Alta de Usuario',
                    'Baja de Usuario',
                    'Cambio de Permisos',
                ],
            ],
            'Seguridad y Vigilancia' => [
                'description' => 'Servicios e incidentes de CCTV y control de accesos físicos.',
                'subcategories' => [
                    'Servicio de CCTV',
                    'Incidente de CCTV',
                    'Servicio de Control de Accesos',
                    'Incidente de Control de Accesos',
                ],
            ],
            'Comunicaciones' => [
                'description' => 'Servicios e incidentes de videoconferencia y telefonía.',
                'subcategories' => [
                    'Servicio de Videoconferencia',
                    'Incidente de Videoconferencia',
                    'Servicio de Telefonía',
                    'Incidente de Telefonía',
                ],
            ],
        ];
    }
}
