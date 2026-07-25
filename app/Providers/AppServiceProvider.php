<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', fn (User $user) => $user->role === 'admin');

        // El botón "Nueva Solicitud" del navbar vive en todas las páginas
        // autenticadas, así que se alimenta vía composer en vez de que cada
        // controlador tenga que pasar el catálogo. Solo categorías con
        // subcategorías: las 5 categorías planas originales (Hardware, etc.)
        // se siguen eligiendo desde el formulario normal, no desde el catálogo.
        View::composer('layouts.navigation', function ($view) {
            $view->with(
                'catalogCategories',
                Category::where('is_active', true)
                    ->with(['subcategories' => fn ($q) => $q->where('is_active', true)])
                    ->whereHas('subcategories', fn ($q) => $q->where('is_active', true))
                    ->orderBy('name')
                    ->get()
            );
        });
    }
}
