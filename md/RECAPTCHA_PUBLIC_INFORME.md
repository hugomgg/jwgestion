# Implementación de reCAPTCHA en Formulario Público de Informes

## Resumen
Se ha implementado Google reCAPTCHA v3 en el formulario público de ingreso de informes (`/informe/{congregacion_id}`) para proteger contra envíos automatizados y spam.

## Componentes Implementados

### 1. Vista Blade (`resources/views/public/informe.blade.php`)

#### Campo oculto para token reCAPTCHA
```blade
@if(config('recaptcha.enabled'))
<input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-informe">
@endif
```

#### Script de Google reCAPTCHA
```blade
@if(config('recaptcha.enabled'))
<!-- Google reCAPTCHA -->
<script src="https://www.google.com/recaptcha/api.js?render={{ config('recaptcha.site_key') }}"></script>
@endif
```

#### Configuración JavaScript
```blade
window.publicInformeConfig = {
    congregacionId: {{ $congregacion->id }},
    getUsersByGrupoUrl: '{{ route("public.informe.usuarios-por-grupo", $congregacion->id) }}',
    storeUrl: '{{ route("public.informe.store", $congregacion->id) }}',
    csrfToken: '{{ csrf_token() }}',
    recaptchaEnabled: {{ config('recaptcha.enabled') ? 'true' : 'false' }},
    recaptchaSiteKey: '{{ config('recaptcha.site_key') }}'
};
```

#### Aviso de privacidad
```blade
@if(config('recaptcha.enabled'))
<div class="mt-2">
    <small class="text-muted">
        Este sitio está protegido por reCAPTCHA y aplican la
        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Política de Privacidad</a> y los
        <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Términos de Servicio</a> de Google.
    </small>
</div>
@endif
```

### 2. JavaScript (`public/js/public-informe.js`)

#### Inicialización al cargar la página
```javascript
// Al final de $(document).ready()
if (config.recaptchaEnabled && typeof grecaptcha !== 'undefined') {
    grecaptcha.ready(function() {
        grecaptcha.execute(config.recaptchaSiteKey, {action: 'informe_submit'})
            .then(function(token) {
                $('#g-recaptcha-response-informe').val(token);
            })
            .catch(function(error) {
                console.error('Error al inicializar reCAPTCHA:', error);
            });
    });
}
```

#### Generación de token al enviar formulario
```javascript
$('#informeForm').on('submit', function(e) {
    e.preventDefault();
    
    if (config.recaptchaEnabled && typeof grecaptcha !== 'undefined') {
        grecaptcha.ready(function() {
            grecaptcha.execute(config.recaptchaSiteKey, {action: 'informe_submit'})
                .then(function(token) {
                    $('#g-recaptcha-response-informe').val(token);
                    submitFormData();
                })
                .catch(function(error) {
                    console.error('Error al obtener token de reCAPTCHA:', error);
                    showAlert('Error de verificación de seguridad. Por favor, recargue la página e intente nuevamente.', 'error');
                });
        });
    } else {
        submitFormData();
    }
});
```

#### Regeneración de token después de envío
```javascript
// En complete del AJAX
if (config.recaptchaEnabled && typeof grecaptcha !== 'undefined') {
    grecaptcha.ready(function() {
        grecaptcha.execute(config.recaptchaSiteKey, {action: 'informe_submit'})
            .then(function(token) {
                $('#g-recaptcha-response-informe').val(token);
            });
    });
}
```

### 3. Middleware (`app/Http/Middleware/VerifyRecaptchaInforme.php`)

Middleware especializado para validar reCAPTCHA en formularios públicos con respuestas JSON:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ReCaptcha\ReCaptcha;
use Illuminate\Support\Facades\Log;

class VerifyRecaptchaInforme
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si reCAPTCHA está deshabilitado, continuar sin validación
        if (!config('recaptcha.enabled')) {
            return $next($request);
        }

        $recaptchaToken = $request->input('g-recaptcha-response');

        if (!$recaptchaToken) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, complete la verificación de seguridad.',
                'errors' => ['recaptcha' => ['...']]
            ], 422);
        }

        $recaptcha = new ReCaptcha(config('recaptcha.secret_key'));
        $response = $recaptcha->setExpectedAction('informe_submit')
                              ->setScoreThreshold(config('recaptcha.score_threshold'))
                              ->verify($recaptchaToken, $request->ip());

        // Validaciones de score y errores...
        
        return $next($request);
    }
}
```

**Características clave:**
- Usa acción `informe_submit` (específica para este formulario)
- Retorna respuestas JSON (no redirecciones)
- Valida score threshold
- Registra intentos sospechosos en logs

### 4. Registro de Middleware (`bootstrap/app.php`)

```php
$middleware->alias([
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
    // ... otros middlewares
    'recaptcha' => \App\Http\Middleware\VerifyRecaptcha::class,
    'recaptcha.informe' => \App\Http\Middleware\VerifyRecaptchaInforme::class,
]);
```

### 5. Aplicación en Ruta (`routes/web.php`)

```php
// Rutas públicas para ingreso de informes
Route::get('/informe/{congregacion_id}', [App\Http\Controllers\PublicInformeController::class, 'show'])
    ->name('public.informe.show');
    
Route::post('/informe/{congregacion_id}', [App\Http\Controllers\PublicInformeController::class, 'store'])
    ->middleware('recaptcha.informe')
    ->name('public.informe.store');
    
Route::get('/informe/{congregacion_id}/usuarios-por-grupo', [App\Http\Controllers\PublicInformeController::class, 'getUsersByGrupo'])
    ->name('public.informe.usuarios-por-grupo');
```

## Configuración Requerida

### Variables de entorno (`.env`)
```env
RECAPTCHA_ENABLED=true
RECAPTCHA_SITE_KEY=tu_site_key_aqui
RECAPTCHA_SECRET_KEY=tu_secret_key_aqui
RECAPTCHA_SCORE_THRESHOLD=0.5
```

### Archivo de configuración (`config/recaptcha.php`)
```php
return [
    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    'enabled' => env('RECAPTCHA_ENABLED', true),
    'score_threshold' => env('RECAPTCHA_SCORE_THRESHOLD', 0.5),
];
```

## Flujo de Verificación

1. **Carga de página**: Se genera un token reCAPTCHA con acción `informe_submit`
2. **Usuario completa formulario**: Token permanece válido en campo oculto
3. **Usuario envía formulario**: 
   - JavaScript intercepta el submit
   - Genera nuevo token fresco
   - Incluye token en datos AJAX
4. **Servidor recibe POST**:
   - Middleware `recaptcha.informe` valida el token
   - Verifica acción `informe_submit`
   - Valida score >= threshold
   - Registra resultado en logs
5. **Respuesta al cliente**:
   - Si válido: procesa informe
   - Si inválido: retorna error 422 con mensaje
6. **Después de envío**: Se regenera token para próximo envío

## Validaciones

### Frontend
- Verifica que `grecaptcha` esté definido
- Maneja errores de ejecución de reCAPTCHA
- Muestra mensajes de error al usuario
- Previene envíos sin token

### Backend
- Valida presencia de token
- Verifica acción esperada (`informe_submit`)
- Valida score >= 0.5 (configurable)
- Registra intentos sospechosos
- Retorna errores JSON estructurados

## Diferencias con login.blade.php

| Aspecto | Login | Informe Público |
|---------|-------|-----------------|
| **Middleware** | `VerifyRecaptcha` | `VerifyRecaptchaInforme` |
| **Acción** | `login` | `informe_submit` |
| **Respuesta error** | `back()->withErrors()` | `response()->json()` |
| **Campo ID** | `g-recaptcha-response` | `g-recaptcha-response-informe` |
| **Context** | Autenticación | Formulario público AJAX |

## Logs

El middleware registra tres tipos de eventos:

### Éxito
```
INFO: reCAPTCHA verification successful (informe)
{
    "ip": "192.168.1.100",
    "score": 0.9
}
```

### Score bajo
```
WARNING: reCAPTCHA score too low (informe)
{
    "ip": "192.168.1.100",
    "score": 0.3,
    "threshold": 0.5
}
```

### Error de verificación
```
ERROR: reCAPTCHA verification error (informe)
{
    "error": "Connection timeout",
    "ip": "192.168.1.100"
}
```

## Pruebas

### Deshabilitar reCAPTCHA
```env
RECAPTCHA_ENABLED=false
```

### Ajustar sensibilidad
```env
# Más estricto (menos false positives, más false negatives)
RECAPTCHA_SCORE_THRESHOLD=0.7

# Menos estricto (más permisivo)
RECAPTCHA_SCORE_THRESHOLD=0.3
```

## Solución de Problemas

### Token no se genera
- Verificar que `config.recaptchaSiteKey` sea válido
- Verificar que el script de Google se cargue correctamente
- Revisar consola del navegador por errores

### Validación falla siempre
- Verificar `.env` tenga las claves correctas
- Verificar que las claves sean de reCAPTCHA v3
- Revisar logs Laravel para detalles

### Score muy bajo
- Puede ser comportamiento sospechoso real
- Ajustar threshold en `.env`
- Revisar IP y comportamiento en logs

## Seguridad

✅ **Implementado:**
- Token único por envío
- Validación server-side
- Registro de intentos sospechosos
- Acción específica por formulario
- Respuestas JSON sin exposición de detalles internos

⚠️ **Consideraciones:**
- reCAPTCHA no es 100% infalible
- Bots avanzados pueden evadir con scores altos
- Combinar con rate limiting si es necesario
- Monitorear logs regularmente

## Mantenimiento

1. **Renovar claves**: Cada 6-12 meses
2. **Revisar logs**: Semanalmente para patrones anómalos
3. **Ajustar threshold**: Según tasa de false positives
4. **Actualizar biblioteca**: `google/recaptcha` vía Composer

## Referencias

- [Google reCAPTCHA v3 Documentation](https://developers.google.com/recaptcha/docs/v3)
- [Laravel Middleware Documentation](https://laravel.com/docs/middleware)
- Middleware original: `app/Http/Middleware/VerifyRecaptcha.php`
- Vista de referencia: `resources/views/auth/login.blade.php`
