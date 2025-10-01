<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ReCaptcha\ReCaptcha;
use Illuminate\Support\Facades\Log;

class VerifyRecaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si reCAPTCHA está deshabilitado, continuar sin validación
        if (!config('recaptcha.enabled')) {
            return $next($request);
        }

        // Obtener el token de reCAPTCHA del request
        $recaptchaToken = $request->input('g-recaptcha-response');

        // Si no hay token, rechazar
        if (!$recaptchaToken) {
            return back()->withErrors([
                'recaptcha' => 'Por favor, complete la verificación de seguridad.'
            ])->withInput($request->except('password'));
        }

        try {
            // Crear instancia de reCAPTCHA con la clave secreta
            $recaptcha = new ReCaptcha(config('recaptcha.secret_key'));
            
            // Verificar el token con la IP del usuario
            $response = $recaptcha->setExpectedAction('login')
                                  ->setScoreThreshold(config('recaptcha.score_threshold'))
                                  ->verify($recaptchaToken, $request->ip());

            // Si la verificación falla
            if (!$response->isSuccess()) {
                Log::warning('reCAPTCHA verification failed', [
                    'ip' => $request->ip(),
                    'errors' => $response->getErrorCodes(),
                    'score' => $response->getScore()
                ]);

                return back()->withErrors([
                    'recaptcha' => 'Verificación de seguridad fallida. Por favor, intente nuevamente.'
                ])->withInput($request->except('password'));
            }

            // Verificar el score (solo para reCAPTCHA v3)
            $score = $response->getScore();
            if ($score < config('recaptcha.score_threshold')) {
                Log::warning('reCAPTCHA score too low', [
                    'ip' => $request->ip(),
                    'score' => $score,
                    'threshold' => config('recaptcha.score_threshold')
                ]);

                return back()->withErrors([
                    'recaptcha' => 'Su actividad parece sospechosa. Por favor, intente más tarde.'
                ])->withInput($request->except('password'));
            }

            // Log exitoso
            Log::info('reCAPTCHA verification successful', [
                'ip' => $request->ip(),
                'score' => $score
            ]);

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);

            // En caso de error del servicio, permitir el acceso pero registrar el error
            // Puedes cambiar esto a rechazar si prefieres mayor seguridad
            return back()->withErrors([
                'recaptcha' => 'Error en la verificación de seguridad. Por favor, intente nuevamente.'
            ])->withInput($request->except('password'));
        }

        return $next($request);
    }
}
