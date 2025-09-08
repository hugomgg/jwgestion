<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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

        // Verificar si el usuario tiene perfil de administrador (perfil = 1)
        $user = auth()->user();
        
        // Usar el método isAdmin() en lugar de comparación directa
        if (!$user->isAdmin()) {
            return redirect()->route('users.index')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
