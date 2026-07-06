<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\TicketsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Ticket;

class ReportController extends Controller
{
    public function excel(Request $request)
    {
        return Excel::download(
            new TicketsExport($request),
            'Reporte_Tickets.xlsx'
        );
    }

    public function pdf(Request $request)
{
    $query = Ticket::with('category');

    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    if ($request->filled('priority')) {
        $query->where('priority', $request->priority);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    $tickets = $query->get();

    $pdf = Pdf::loadView('reports.tickets', compact('tickets'));

    return $pdf->download('Reporte_Tickets.pdf');
}
}