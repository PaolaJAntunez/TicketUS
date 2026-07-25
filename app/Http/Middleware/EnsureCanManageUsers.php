<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageUsers
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->can('manage-users')) {
            return redirect()->route('dashboard')->with('error', 'No tienes permisos para realizar esta acción.');
        }

        return $next($request);
    }
}
