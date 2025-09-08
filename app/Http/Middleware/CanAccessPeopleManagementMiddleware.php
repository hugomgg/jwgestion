<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanAccessPeopleManagementMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder.');
        }

        $user = auth()->user();
        
        // Permitir acceso a usuarios con perfil 3 (coordinador)
        if (!$user->canAccessPeopleManagementMenu()) {
            return redirect()->route('home')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}