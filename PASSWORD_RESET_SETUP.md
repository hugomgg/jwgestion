# Configuración del Sistema de Recuperación de Contraseñas

## 🔍 Diagnóstico del Problema

El sistema de recuperación de contraseñas **NO está enviando emails** porque la configuración actual usa el driver `log`, que solo guarda los correos en archivos de registro en lugar de enviarlos realmente.

**Configuración actual en `.env`:**
```env
MAIL_MAILER=log
```

Esto hace que los emails se guarden en: `storage/logs/laravel.log` en lugar de enviarse.

---

## ✅ Soluciones Disponibles

### Opción 1: Configurar SMTP Real (Recomendado para Producción)

#### 1.1 Usando Gmail

**Actualizar `.env`:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Pasos para obtener App Password de Gmail:**
1. Ve a tu cuenta de Google → Seguridad
2. Activa la verificación en 2 pasos
3. Ve a "Contraseñas de aplicaciones"
4. Genera una contraseña para "Correo"
5. Usa esa contraseña de 16 caracteres en `MAIL_PASSWORD`

#### 1.2 Usando Mailtrap (Recomendado para Desarrollo)

**Actualizar `.env`:**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu-username-mailtrap
MAIL_PASSWORD=tu-password-mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Pasos:**
1. Registrarse en https://mailtrap.io (gratis)
2. Crear un inbox
3. Copiar las credenciales SMTP

#### 1.3 Usando Servidor SMTP Propio

**Actualizar `.env`:**
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.tudominio.com
MAIL_PORT=587
MAIL_USERNAME=usuario@tudominio.com
MAIL_PASSWORD=tu-contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

### Opción 2: Mantener `log` para Desarrollo Local

Si solo quieres probar sin configurar SMTP real:

1. Los emails se guardan en `storage/logs/laravel.log`
2. Busca líneas que contengan el enlace de recuperación
3. Copia el token del enlace

**Ejemplo de lo que verás en el log:**
```
local.INFO: Reset password link: http://localhost/password/reset/4f1g23c21f45d80ef0d7c8c3b6fc8ca9d2d7e1d5
```

---

## 🔧 Implementación de la Solución

### Paso 1: Actualizar el archivo `.env`

Elige una de las opciones anteriores y actualiza tu archivo `.env` con las credenciales correspondientes.

### Paso 2: Limpiar cachés

```bash
php artisan config:clear
php artisan cache:clear
```

### Paso 3: Verificar la configuración

Crea un comando de prueba para verificar que el email funciona:

```bash
php artisan tinker
```

Luego ejecuta:
```php
Mail::raw('Prueba de correo', function($message) {
    $message->to('tu-email@example.com')->subject('Test');
});
```

---

## 📝 Personalizar el Email de Recuperación (Opcional)

### Crear notificación personalizada

**1. Crear la notificación:**

```bash
php artisan make:notification ResetPasswordNotification
```

**2. Editar `app/Notifications/ResetPasswordNotification.php`:**

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Recuperación de Contraseña - ' . config('app.name'))
            ->greeting('¡Hola!')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta.')
            ->action('Restablecer Contraseña', $url)
            ->line('Este enlace expirará en ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' minutos.')
            ->line('Si no solicitaste restablecer tu contraseña, puedes ignorar este correo.')
            ->salutation('Saludos, ' . config('app.name'));
    }
}
```

**3. Actualizar el modelo `User`:**

Agrega este método al modelo `app/Models/User.php`:

```php
use App\Notifications\ResetPasswordNotification;

public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordNotification($token));
}
```

---

## 🧪 Pruebas

### Probar recuperación de contraseña:

1. Ve a `http://localhost/password/reset`
2. Ingresa un email de usuario existente
3. Verifica:
   - **Con SMTP configurado**: Revisa tu bandeja de entrada
   - **Con driver `log`**: Revisa `storage/logs/laravel.log`

### Verificar logs:

```bash
# Windows PowerShell
Get-Content storage\logs\laravel.log -Tail 50

# Windows CMD
type storage\logs\laravel.log | more
```

---

## 📋 Checklist de Configuración

- [ ] Decidir qué método de envío usar (Gmail, Mailtrap, SMTP propio)
- [ ] Actualizar variables en `.env`
- [ ] Limpiar cachés con `php artisan config:clear`
- [ ] Probar envío de email con `tinker`
- [ ] Probar recuperación de contraseña desde la interfaz
- [ ] Verificar que el enlace funciona correctamente
- [ ] (Opcional) Personalizar el template del email
- [ ] Documentar las credenciales en un lugar seguro

---

## 🔒 Seguridad

### Recomendaciones:

1. **Nunca** commitear el archivo `.env` con credenciales reales
2. Usar variables de entorno en producción
3. Activar verificación en 2 pasos si usas Gmail
4. Usar contraseñas de aplicación, no la contraseña de tu cuenta
5. Rotar las credenciales periódicamente
6. Configurar límites de intentos (rate limiting) en las rutas de recuperación

### Rate Limiting en `app/Http/Controllers/Auth/ForgotPasswordController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Get the response for a successful password reset link.
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return back()->with('status', 'Te hemos enviado un enlace de recuperación por correo electrónico.');
    }

    /**
     * Get the response for a failed password reset link.
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'No pudimos encontrar un usuario con ese correo electrónico.']);
    }
}
```

---

## 🐛 Troubleshooting

### Problema: "Connection refused"
**Solución**: Verifica que el puerto SMTP esté abierto en tu firewall

### Problema: "Authentication failed"
**Solución**: Verifica username y password, usa App Password si es Gmail

### Problema: "No se recibe el email"
**Solución**: 
1. Verifica la carpeta de spam
2. Revisa `storage/logs/laravel.log` para errores
3. Usa `php artisan queue:work` si usas colas

### Problema: "SSL certificate problem"
**Solución**: En desarrollo, puedes desactivar la verificación SSL (NO en producción):

```php
// config/mail.php - solo para desarrollo
'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
        'port' => env('MAIL_PORT', 587),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'timeout' => null,
        'verify_peer' => false, // Solo para desarrollo
    ],
],
```

---

## 📚 Recursos Adicionales

- [Laravel Mail Documentation](https://laravel.com/docs/11.x/mail)
- [Laravel Password Reset Documentation](https://laravel.com/docs/11.x/passwords)
- [Mailtrap Documentation](https://mailtrap.io/blog/laravel-send-email/)
- [Gmail SMTP Settings](https://support.google.com/mail/answer/7126229)

---

**Última actualización**: 1 de octubre de 2025
**Versión de Laravel**: 12.x
