<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('tickets.index')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
require __DIR__.'/auth.php';
