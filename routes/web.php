<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketCommentController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketReminderController;
use App\Http\Controllers\TicketTagController;
use App\Http\Controllers\TicketTimeLogController;
use Illuminate\Support\Facades\Route;

// --- RUTAS PÚBLICAS / REDIRECCIÓN INICIAL ---
Route::get('/', function () {
    return redirect()->route('login');
});

// --- DASHBOARD ---
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard/report/pdf', [DashboardController::class, 'downloadPdf'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.report.pdf');

Route::get('/dashboard/report/excel', [DashboardController::class, 'downloadExcel'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.report.excel');

// --- SOLUCIONES ---
Route::get('/soluciones', function () {
    return view('solutions.index');
})->middleware(['auth', 'verified'])->name('solutions.index');

// --- RUTAS GENERALES BAJO AUTENTICACIÓN ---
Route::middleware('auth')->group(function () {
    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Reportes de Tickets
    Route::get('/reports/tickets/excel', [ReportController::class, 'excel'])->name('reports.tickets.excel');
    Route::get('/reports/tickets/pdf', [ReportController::class, 'pdf'])->name('reports.tickets.pdf');

    // Vistas Estáticas Adicionales & FAQs Nueva
    Route::get('/feedback', function () { return view('feedback'); })->name('feedback');
    Route::get('/settings', function () { return view('settings'); })->name('settings');
    Route::get('/faqs', function () { return view('faqs'); })->name('faqs');
});

// --- TICKETS ---
Route::middleware('auth')->group(function () {
    // Deben registrarse antes que el resource: si no, "tickets/{ticket}" (show)
    // captura "kanban"/"search" como si fueran el id del ticket.
    Route::get('tickets/kanban', [TicketController::class, 'kanban'])->name('tickets.kanban');
    Route::get('tickets/search', [TicketController::class, 'search'])->name('tickets.search');

    Route::resource('tickets', TicketController::class);
    Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status.update');
    Route::patch('tickets/{ticket}/hold', [TicketController::class, 'hold'])->name('tickets.hold');
    Route::post('tickets/{ticket}/reopen', [ApprovalController::class, 'reopen'])->name('tickets.reopen');
    Route::put('tickets/{ticket}/approval', [ApprovalController::class, 'assign'])->name('tickets.approval.assign');

    // --- Menú "Acciones" de la vista de detalle ---
    Route::put('tickets/{ticket}/resolution', [TicketController::class, 'resolution'])->name('tickets.resolution');
    Route::post('tickets/{ticket}/duplicate', [TicketController::class, 'duplicate'])->name('tickets.duplicate');
    Route::post('tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');
    Route::post('tickets/{ticket}/send-for-approval', [ApprovalController::class, 'sendForApproval'])->name('tickets.approval.send');

    Route::post('tickets/{ticket}/attachments', [TicketAttachmentController::class, 'store'])->name('tickets.attachments.store');
    Route::get('tickets/{ticket}/attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])->name('tickets.attachments.download');

    Route::post('tickets/{ticket}/reminders', [TicketReminderController::class, 'store'])->name('tickets.reminders.store');

    Route::post('tickets/{ticket}/time-logs/start', [TicketTimeLogController::class, 'start'])->name('tickets.timelogs.start');
    Route::post('tickets/{ticket}/time-logs/stop', [TicketTimeLogController::class, 'stop'])->name('tickets.timelogs.stop');

    Route::post('tickets/{ticket}/tags', [TicketTagController::class, 'store'])->name('tickets.tags.store');
    Route::delete('tickets/{ticket}/tags/{tag}', [TicketTagController::class, 'destroy'])->name('tickets.tags.destroy');

    // --- Comentarios de tickets (nota: antes vivía fuera del grupo 'auth') ---
    Route::post('tickets/{ticket}/comments', [TicketCommentController::class, 'store'])->name('tickets.comments.store');
});

// --- APROBACIONES ---
Route::middleware('auth')->group(function () {
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    // Las rutas /adhoc deben registrarse antes que /{level}: de lo contrario {level}
    // (un wildcard) captura el literal "adhoc" y nunca se llega a estas.
    Route::post('/approvals/{ticket}/adhoc/approve', [ApprovalController::class, 'approveAdhoc'])->name('approvals.adhoc.approve');
    Route::post('/approvals/{ticket}/adhoc/reject', [ApprovalController::class, 'rejectAdhoc'])->name('approvals.adhoc.reject');
    Route::post('/approvals/{ticket}/{level}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{ticket}/{level}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
});

// --- CREACIÓN DE USUARIOS (Gate/middleware dedicado: mensaje de error en vez de 403 crudo) ---
Route::prefix('admin')->middleware(['auth', 'manage-users'])->name('admin.')->group(function () {
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
});

// --- MÓDULO DE ADMINISTRACIÓN ---
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Gestión de Usuarios
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');

    // Gestión de Categorías y Subcategorías (pantalla con modales Alpine +
    // fetch, sin recargar página: store/update/toggle/destroy responden JSON).
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::patch('/categories/{category}/toggle', [AdminController::class, 'toggleCategory'])->name('categories.toggle');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

    Route::post('/categories/{category}/subcategories', [AdminController::class, 'storeSubcategory'])->name('subcategories.store');
    Route::put('/subcategories/{subcategory}', [AdminController::class, 'updateSubcategory'])->name('subcategories.update');
    Route::patch('/subcategories/{subcategory}/toggle', [AdminController::class, 'toggleSubcategory'])->name('subcategories.toggle');
    Route::delete('/subcategories/{subcategory}', [AdminController::class, 'destroySubcategory'])->name('subcategories.destroy');

    // Flujos de Aprobación
    Route::get('/approval-flows', [AdminController::class, 'approvalFlows'])->name('approval-flows.index');
    Route::get('/approval-flows/create', [AdminController::class, 'createApprovalFlow'])->name('approval-flows.create');
    Route::post('/approval-flows', [AdminController::class, 'storeApprovalFlow'])->name('approval-flows.store');
    Route::get('/approval-flows/{approvalFlow}/edit', [AdminController::class, 'editApprovalFlow'])->name('approval-flows.edit');
    Route::put('/approval-flows/{approvalFlow}', [AdminController::class, 'updateApprovalFlow'])->name('approval-flows.update');
    Route::delete('/approval-flows/{approvalFlow}', [AdminController::class, 'destroyApprovalFlow'])->name('approval-flows.destroy');
});

// --- AUTENTICACIÓN SHIFT (Laravel Breeze/Jetstream) ---
require __DIR__.'/auth.php';
