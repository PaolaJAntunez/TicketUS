<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Hoja genérica reutilizable: cada sección del dashboard (Resumen, Por
 * Categoría, Por Agente, etc.) arma sus propias filas —incluyendo la fila
 * de encabezados— y las pasa aquí, en vez de tener una clase de hoja por
 * sección.
 */
class DashboardSheet implements FromArray, WithTitle
{
    public function __construct(private string $title, private array $rows)
    {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return $this->title;
    }
}
