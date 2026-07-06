<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TicketsExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Ticket::with('category');

        // Buscar por título
        if ($this->request->filled('search')) {
            $query->where('title', 'like', '%' . $this->request->search . '%');
        }

        // Categoría
        if ($this->request->filled('category')) {
            $query->where('category_id', $this->request->category);
        }

        // Prioridad
        if ($this->request->filled('priority')) {
            $query->where('priority', $this->request->priority);
        }

        // Estado
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        // Fecha desde
        if ($this->request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $this->request->from_date);
        }

        // Fecha hasta
        if ($this->request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $this->request->to_date);
        }

        return $query->get()->map(function ($ticket) {
            return [
                $ticket->id,
                $ticket->title,
                $ticket->category->name ?? '',
                ucfirst($ticket->priority),
                ucfirst(str_replace('_', ' ', $ticket->status)),
                $ticket->created_at->format('d/m/Y'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Título',
            'Categoría',
            'Prioridad',
            'Estado',
            'Fecha'
        ];
    }
}