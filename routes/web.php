<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('tickets.index')
        : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/soluciones', function () {
    return view('solutions.index');
})->middleware(['auth', 'verified'])->name('solutions.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
use App\Http\Controllers\TicketController;

Route::middleware('auth')->group(function () {
    Route::resource('tickets', TicketController::class);
});
use App\Http\Controllers\TicketCommentController;

Route::post('tickets/{ticket}/comments', [TicketCommentController::class, 'store'])
    ->name('tickets.comments.store');

use App\Http\Controllers\Admin\AdminController;

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');

    Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
    Route::get('/categories/create', [AdminController::class, 'createCategory'])->name('categories.create');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

    Route::get('/approval-flows', [AdminController::class, 'approvalFlows'])->name('approval-flows.index');
    Route::get('/approval-flows/create', [AdminController::class, 'createApprovalFlow'])->name('approval-flows.create');
    Route::post('/approval-flows', [AdminController::class, 'storeApprovalFlow'])->name('approval-flows.store');
    Route::get('/approval-flows/{approvalFlow}/edit', [AdminController::class, 'editApprovalFlow'])->name('approval-flows.edit');
    Route::put('/approval-flows/{approvalFlow}', [AdminController::class, 'updateApprovalFlow'])->name('approval-flows.update');
    Route::delete('/approval-flows/{approvalFlow}', [AdminController::class, 'destroyApprovalFlow'])->name('approval-flows.destroy');
    Route::post('/approval-flows/{approvalFlow}/levels', [AdminController::class, 'storeApprovalLevel'])->name('approval-flows.levels.store');
    Route::delete('/approval-levels/{approvalLevel}', [AdminController::class, 'destroyApprovalLevel'])->name('approval-levels.destroy');
});

use App\Http\Controllers\ApprovalController;

Route::middleware('auth')->group(function () {
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{ticket}/{level}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{ticket}/{level}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
});

require __DIR__.'/auth.php';
