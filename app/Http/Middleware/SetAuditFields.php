<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetAuditFields
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Obtener el ID del usuario autenticado
        $userId = Auth::id();
        
        // Si no hay usuario autenticado, usar ID 1 por defecto
        if (!$userId) {
            $userId = 1;
        }
        
        // Agregar los campos de auditoría al request si no existen
        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('patch')) {
            
            // Para operaciones de creación
            if ($request->isMethod('post')) {
                $request->merge([
                    'creador_id' => $userId,
                    'modificador_id' => $userId,
                    'creado_por_timestamp' => now(),
                    'modificado_por_timestamp' => now(),
                ]);
            }
            
            // Para operaciones de actualización
            if ($request->isMethod('put') || $request->isMethod('patch')) {
                $request->merge([
                    'modificador_id' => $userId,
                    'modificado_por_timestamp' => now(),
                ]);
            }
        }

        return $next($request);
    }
}