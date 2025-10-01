<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // Validar reCAPTCHA si está habilitado
        if (config('recaptcha.enabled')) {
            $this->validateRecaptcha($request);
        }

        // Verificar si el usuario existe antes de intentar enviar
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'No pudimos encontrar un usuario con ese correo electrónico.']);
        }

        // Verificar si el usuario está activo
        if ($user->estado != 1) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Esta cuenta está deshabilitada. Por favor contacte al administrador.']);
        }

        // Intentar enviar el enlace de recuperación
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }

    /**
     * Get the response for a successful password reset link.
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return back()->with('status', 'Te hemos enviado un enlace de recuperación por correo electrónico. Por favor revisa tu bandeja de entrada.');
    }

    /**
     * Get the response for a failed password reset link.
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        // Log para debugging
        \Log::info('Password reset failed', [
            'email' => $request->email,
            'response' => $response,
            'user_exists' => User::where('email', $request->email)->exists(),
        ]);

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Hubo un problema al enviar el enlace de recuperación. Por favor, intente nuevamente o contacte al administrador.']);
    }

    /**
     * Validate reCAPTCHA token.
     */
    protected function validateRecaptcha(Request $request)
    {
        $request->validate([
            'recaptcha_token' => 'required',
        ], [
            'recaptcha_token.required' => 'La verificación reCAPTCHA es requerida.',
        ]);

        $recaptchaToken = $request->input('recaptcha_token');
        $secretKey = config('recaptcha.secret_key');

        // Verificar el token con Google reCAPTCHA
        $response = \Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $recaptchaToken,
            'remoteip' => $request->ip(),
        ]);

        $result = $response->json();

        // Verificar si la respuesta es exitosa
        if (!$result['success']) {
            \Log::warning('reCAPTCHA verification failed', [
                'email' => $request->email,
                'error_codes' => $result['error-codes'] ?? [],
                'ip' => $request->ip(),
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'recaptcha_token' => ['La verificación de seguridad falló. Por favor, recarga la página e intenta nuevamente.'],
            ]);
        }

        // Verificar el score (para reCAPTCHA v3)
        if (isset($result['score'])) {
            $threshold = config('recaptcha.score_threshold', 0.5);
            
            if ($result['score'] < $threshold) {
                \Log::warning('reCAPTCHA score too low', [
                    'email' => $request->email,
                    'score' => $result['score'],
                    'threshold' => $threshold,
                    'ip' => $request->ip(),
                ]);

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'recaptcha_token' => ['La verificación de seguridad falló. Si crees que esto es un error, contacta al administrador.'],
                ]);
            }
        }

        // Verificar que la acción sea correcta
        if (isset($result['action']) && $result['action'] !== 'password_reset') {
            \Log::warning('reCAPTCHA action mismatch', [
                'expected' => 'password_reset',
                'received' => $result['action'],
                'email' => $request->email,
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'recaptcha_token' => ['La verificación de seguridad es inválida.'],
            ]);
        }

        \Log::info('reCAPTCHA verification successful', [
            'email' => $request->email,
            'score' => $result['score'] ?? 'N/A',
            'action' => $result['action'] ?? 'N/A',
        ]);
    }
}
