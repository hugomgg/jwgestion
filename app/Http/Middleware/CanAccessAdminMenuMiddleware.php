<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanAccessAdminMenuMiddleware
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
        
        // Permitir acceso a usuarios con perfil 1 (admin), 2 (supervisor), 3 (coordinador), 6 (subsecretario), 7 (organizador) y 8 (suborganizador)
        if (!$user->canAccessAdminMenu() && !$user->canAccessPeopleManagementMenu()) {
            return redirect()->route('home')->with('error', 'No tiene permisos para acceder a esta sección.');
        }

        // Para usuarios con perfil = 2 (Supervisor), permitir lectura y supervisión
        if ($user->isSupervisor()) {
            $method = $request->method();
            
            // Solo permitir métodos GET (lectura) para el perfil Supervisor
            if (!in_array($method, ['GET', 'HEAD'])) {
                return redirect()->back()->with('error', 'No tiene permisos para realizar esta acción. Solo puede supervisar y ver la información.');
            }
        }
        
        // Para usuarios con perfil = 3 (Coordinador) y perfil = 7 (Organizador), permitir gestión de usuarios, programas e informes
        if ($user->isCoordinator() || $user->isOrganizer()) {
            $method = $request->method();
            $path = $request->path();
            
            // Solo permitir operaciones en rutas de usuarios, programas, partes-programa e informes
            if (!str_starts_with($path, 'usuarios') && !str_starts_with($path, 'programas') && !str_starts_with($path, 'partes-programa') && !str_starts_with($path, 'informes')) {
                // Si no es ruta de usuarios, programas, partes-programa o informes, solo permitir GET
                if (!in_array($method, ['GET', 'HEAD'])) {
                    return redirect()->back()->with('error', 'No tiene permisos para realizar esta acción en esta sección.');
                }
            }
        }
        
        // Para usuarios con perfil = 4 (Subcoordinador), 5 (Secretario), 6 (Subsecretario) y 8 (Suborganizador), permitir gestión de informes
        if ($user->isSubcoordinator() || $user->isSecretary() || $user->isSubsecretary() || $user->isSuborganizer()) {
            $method = $request->method();
            $path = $request->path();
            
            // Permitir operaciones en rutas de informes
            if (str_starts_with($path, 'informes')) {
                // Permitir todas las operaciones CRUD en informes
                // (No se añade restricción, se permite pasar al siguiente middleware)
            } else {
                // Para otras rutas, solo permitir GET
                if (!in_array($method, ['GET', 'HEAD'])) {
                    return redirect()->back()->with('error', 'No tiene permisos para realizar esta acción en esta sección.');
                }
            }
        }
        
        // Para usuarios con perfiles de solo lectura (Publicador y Estudiante)
        if ($user->isReadOnly()) {
            $method = $request->method();
            
            // Solo permitir métodos GET (lectura)
            if (!in_array($method, ['GET', 'HEAD'])) {
                return redirect()->back()->with('error', 'No tiene permisos para realizar esta acción. Solo puede ver la información.');
            }
        }

        return $next($request);
    }
}