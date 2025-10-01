<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        // Validar reCAPTCHA si está habilitado
        if (config('recaptcha.enabled')) {
            $this->validateRecaptcha($request);
        }

        // Validar los campos del formulario
        $request->validate($this->rules(), $this->validationErrorMessages());

        // Intentar resetear la contraseña
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // Retornar respuesta
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
    }

    /**
     * Get the password reset validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'password.required' => 'La contraseña es requerida.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ];
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        \Log::info('Password reset successful', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        return redirect($this->redirectPath())
            ->with('status', '¡Tu contraseña ha sido restablecida exitosamente! Ya puedes iniciar sesión con tu nueva contraseña.');
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        \Log::warning('Password reset failed', [
            'email' => $request->email,
            'response' => $response,
            'ip' => $request->ip(),
        ]);

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $this->getResetFailedMessage($response)]);
    }

    /**
     * Get the password reset failed message.
     *
     * @param  string  $response
     * @return string
     */
    protected function getResetFailedMessage($response)
    {
        switch ($response) {
            case Password::INVALID_USER:
                return 'No pudimos encontrar un usuario con ese correo electrónico.';
            case Password::INVALID_TOKEN:
                return 'Este enlace de recuperación es inválido o ha expirado. Por favor, solicita un nuevo enlace.';
            default:
                return 'Hubo un problema al restablecer tu contraseña. Por favor, intenta nuevamente.';
        }
    }

    /**
     * Validate reCAPTCHA token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     * @throws \Illuminate\Validation\ValidationException
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
            \Log::warning('reCAPTCHA verification failed on password reset', [
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
                \Log::warning('reCAPTCHA score too low on password reset', [
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
        if (isset($result['action']) && $result['action'] !== 'reset_password') {
            \Log::warning('reCAPTCHA action mismatch on password reset', [
                'expected' => 'reset_password',
                'received' => $result['action'],
                'email' => $request->email,
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'recaptcha_token' => ['La verificación de seguridad es inválida.'],
            ]);
        }

        \Log::info('reCAPTCHA verification successful on password reset', [
            'email' => $request->email,
            'score' => $result['score'] ?? 'N/A',
            'action' => $result['action'] ?? 'N/A',
        ]);
    }
}
