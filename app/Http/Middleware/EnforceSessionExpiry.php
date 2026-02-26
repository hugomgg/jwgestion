<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceSessionExpiry
{
    /**
     * Máximo de segundos que un Admin o Supervisor puede estar
     * autenticado con "remember me" sin forzar re-login.
     * 8 horas = 28800 segundos.
     */
    const MAX_SESSION_SECONDS = 28800;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $loginAt = $request->session()->get('auth.login_at');

            if ($loginAt && (time() - $loginAt) > self::MAX_SESSION_SECONDS) {
                // Sesión expirada: forzar logout para todos los usuarios
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Su sesión ha expirado por seguridad. Por favor, inicie sesión nuevamente.'
                ]);
            }
        }

        return $next($request);
    }
}
