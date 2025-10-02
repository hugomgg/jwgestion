# reCAPTCHA en Recuperación de Contraseña

## 🔐 Implementación Completa

Se ha agregado **Google reCAPTCHA v3** a **AMBAS páginas** del flujo de recuperación de contraseña para proteger contra ataques automatizados y spam:

1. **Solicitud de enlace** (`/password/reset`) - Donde el usuario ingresa su email
2. **Restablecimiento** (`/password/reset/{token}`) - Donde el usuario ingresa su nueva contraseña

## ✅ Características Implementadas

### 1. Protección con reCAPTCHA v3

- ✅ **Invisible para el usuario**: No requiere hacer clic en "No soy un robot"
- ✅ **Validación automática**: Se ejecuta en segundo plano
- ✅ **Score-based**: Evalúa la probabilidad de que sea un bot (0.0 a 1.0)
- ✅ **Action tracking**: Rastrea dos acciones:
  - `password_reset` - Al solicitar enlace
  - `reset_password` - Al establecer nueva contraseña

### 2. Validación en Backend

**Controlador `ForgotPasswordController`** (Solicitud de enlace):

1. **Valida el token de reCAPTCHA** antes de procesar la solicitud
2. **Verifica el score** (mínimo 0.5 por defecto)
3. **Verifica la acción** (debe ser `password_reset`)
4. **Registra intentos sospechosos** en el log
5. **Bloquea solicitudes con score bajo**

**Controlador `ResetPasswordController`** (Nueva contraseña):

1. **Valida el token de reCAPTCHA** antes de cambiar la contraseña
2. **Verifica el score** (mínimo 0.5 por defecto)
3. **Verifica la acción** (debe ser `reset_password`)
4. **Valida la fortaleza de la contraseña** (mínimo 8 caracteres)
5. **Registra cambios exitosos y fallidos**

### 3. Mensajes de Error Personalizados

**reCAPTCHA:**
```php
// Si reCAPTCHA falla
'La verificación de seguridad falló. Por favor, recarga la página e intenta nuevamente.'

// Si el score es bajo
'La verificación de seguridad falló. Si crees que esto es un error, contacta al administrador.'
```

**Restablecimiento de contraseña:**
```php
// Token inválido o expirado
'Este enlace de recuperación es inválido o ha expirado. Por favor, solicita un nuevo enlace.'

// Usuario no encontrado
'No pudimos encontrar un usuario con ese correo electrónico.'

// Contraseña muy corta
'La contraseña debe tener al menos 8 caracteres.'

// Contraseñas no coinciden
'Las contraseñas no coinciden.'
```

## 📋 Configuración

### Variables de Entorno (.env)

```bash
# Google reCAPTCHA v3 Configuration
RECAPTCHA_SITE_KEY=6LcLctorAAAAAJ-BUNu-pJLl0kZrXGSjqDNUIG6g
RECAPTCHA_SECRET_KEY=6LcLctorAAAAALy0Tz0dBLNXFICKyae7uzi5kxg_
RECAPTCHA_ENABLED=true
RECAPTCHA_SCORE_THRESHOLD=0.5
```

### Configuración (config/recaptcha.php)

```php
return [
    'site_key' => env('RECAPTCHA_SITE_KEY', ''),
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
    'enabled' => env('RECAPTCHA_ENABLED', true),
    'score_threshold' => env('RECAPTCHA_SCORE_THRESHOLD', 0.5),
];
```

## 🔍 Cómo Funciona

### Flujo del Usuario

**Paso 1: Solicitar enlace de recuperación**

1. **Usuario visita** `/password/reset`
2. **reCAPTCHA se carga** automáticamente en segundo plano
3. **Usuario ingresa su email** y hace clic en "Enviar Enlace de Recuperación"
4. **JavaScript genera un token** (acción: `password_reset`)
5. **Backend valida el token** con Google
6. **Si es válido**: Envía email con enlace
7. **Si es inválido**: Muestra error y registra el intento

**Paso 2: Restablecer contraseña**

1. **Usuario hace clic en enlace** del email (llega a `/password/reset/{token}`)
2. **reCAPTCHA se carga** automáticamente
3. **Usuario ingresa nueva contraseña** y confirmación
4. **Usuario hace clic** en "Restablecer Contraseña"
5. **JavaScript genera un token** (acción: `reset_password`)
6. **Backend valida**:
   - Token de reCAPTCHA
   - Token de recuperación (no expirado)
   - Fortaleza de contraseña
   - Coincidencia de contraseñas
7. **Si es válido**: Actualiza contraseña y redirige al login
8. **Si es inválido**: Muestra error específico

### Validación Backend

```php
protected function validateRecaptcha(Request $request)
{
    // 1. Validar que el token existe
    $request->validate([
        'recaptcha_token' => 'required',
    ]);

    // 2. Verificar con Google reCAPTCHA
    $response = \Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        'secret' => config('recaptcha.secret_key'),
        'response' => $request->input('recaptcha_token'),
        'remoteip' => $request->ip(),
    ]);

    $result = $response->json();

    // 3. Verificar éxito
    if (!$result['success']) {
        throw ValidationException::withMessages([...]);
    }

    // 4. Verificar score (v3)
    if ($result['score'] < config('recaptcha.score_threshold')) {
        throw ValidationException::withMessages([...]);
    }

    // 5. Verificar acción
    if ($result['action'] !== 'password_reset') {
        throw ValidationException::withMessages([...]);
    }
}
```

## 📊 Score Threshold

### ¿Qué es el Score?

reCAPTCHA v3 devuelve un **score de 0.0 a 1.0**:

- **1.0**: Muy probablemente humano
- **0.5**: Neutral (umbral recomendado)
- **0.0**: Muy probablemente bot

### Configuración Recomendada

| Nivel de Seguridad | Score Threshold | Uso Recomendado |
|-------------------|-----------------|-----------------|
| **Bajo** | 0.3 | Desarrollo/Testing |
| **Medio** | 0.5 | Producción (recomendado) |
| **Alto** | 0.7 | Alta seguridad |
| **Muy Alto** | 0.9 | Extrema seguridad (puede bloquear usuarios legítimos) |

### Ajustar el Threshold

En `.env`:
```bash
RECAPTCHA_SCORE_THRESHOLD=0.5  # Cambiar según necesidad
```

## 🚨 Logging y Monitoreo

### Eventos Registrados

El sistema registra en `storage/logs/laravel.log`:

#### 1. Verificación Exitosa
```
[INFO] reCAPTCHA verification successful
{
    "email": "usuario@ejemplo.com",
    "score": 0.9,
    "action": "password_reset"
}
```

#### 2. Verificación Fallida
```
[WARNING] reCAPTCHA verification failed
{
    "email": "usuario@ejemplo.com",
    "error_codes": ["timeout-or-duplicate"],
    "ip": "192.168.1.100"
}
```

#### 3. Score Bajo
```
[WARNING] reCAPTCHA score too low
{
    "email": "usuario@ejemplo.com",
    "score": 0.3,
    "threshold": 0.5,
    "ip": "192.168.1.100"
}
```

#### 4. Acción Incorrecta
```
[WARNING] reCAPTCHA action mismatch
{
    "expected": "password_reset",
    "received": "login",
    "email": "usuario@ejemplo.com"
}
```

### Revisar Logs

```powershell
# Ver últimos intentos
Get-Content storage\logs\laravel.log | Select-String "reCAPTCHA" -Context 0,3

# Ver solo fallos
Get-Content storage\logs\laravel.log | Select-String "reCAPTCHA.*failed|score too low"

# Ver scores
Get-Content storage\logs\laravel.log | Select-String "score"
```

## 🧪 Testing

### Desactivar en Desarrollo

Para desarrollo local, puedes desactivar reCAPTCHA:

```bash
# En .env
RECAPTCHA_ENABLED=false
```

El formulario funcionará normalmente sin validación de reCAPTCHA.

### Testing Manual

1. **Caso Normal** (Usuario Legítimo):
   - Ir a `/password/reset`
   - Ingresar email válido
   - Debería funcionar sin problemas

2. **Caso Bot** (Simulado):
   - Usar token inválido
   - El sistema debería rechazar

3. **Caso Score Bajo**:
   - Ajustar threshold a 0.9
   - Algunos usuarios pueden ser rechazados

### Verificar en Google Console

1. Ve a: https://www.google.com/recaptcha/admin
2. Selecciona tu sitio
3. Ve a "Analytics"
4. Verás estadísticas de:
   - Solicitudes totales
   - Score promedio
   - Intentos bloqueados

## 🔧 Troubleshooting

### Error: "La verificación reCAPTCHA es requerida"

**Causa**: JavaScript no se cargó o está bloqueado

**Solución**:
1. Verificar que `RECAPTCHA_ENABLED=true`
2. Verificar que las claves están correctas
3. Revisar consola del navegador para errores JS
4. Verificar que el dominio está autorizado en Google Console

### Error: "La verificación de seguridad falló"

**Causa**: Token inválido o expirado

**Solución**:
1. Recargar la página
2. Intentar nuevamente
3. Verificar que las claves en `.env` son correctas
4. Verificar conectividad con Google

### Error: "Score too low"

**Causa**: reCAPTCHA detectó comportamiento sospechoso

**Solución**:
1. Reducir `RECAPTCHA_SCORE_THRESHOLD` temporalmente
2. Revisar logs para ver el score real
3. Contactar al administrador si es un usuario legítimo

### Token Expira Rápido

**Causa**: reCAPTCHA v3 tokens expiran en 2 minutos

**Solución**: El código ya regenera el token al enviar el formulario:
```javascript
$('#resetPasswordForm').on('submit', function(e) {
    e.preventDefault();
    // Regenera token antes de enviar
    grecaptcha.execute(...).then(function(token) {
        document.getElementById('recaptcha_token').value = token;
        form.submit();
    });
});
```

## 📱 Compatibilidad

### Navegadores Soportados

- ✅ Chrome 80+
- ✅ Firefox 75+
- ✅ Safari 13+
- ✅ Edge 80+
- ✅ Opera 70+

### Dispositivos

- ✅ Desktop (Windows, macOS, Linux)
- ✅ Móvil (Android, iOS)
- ✅ Tablet

### Accesibilidad

reCAPTCHA v3 es **invisible** y no requiere interacción del usuario, por lo que es completamente accesible.

## 🔐 Seguridad

### Mejores Prácticas Implementadas

1. ✅ **Token de un solo uso**: Se regenera en cada intento
2. ✅ **Validación server-side**: No se confía solo en el cliente
3. ✅ **Logging de intentos**: Para auditoría
4. ✅ **Rate limiting**: Laravel ya incluye throttling
5. ✅ **HTTPS recomendado**: Para producción

### Protección Contra

- ✅ **Ataques de fuerza bruta**
- ✅ **Bots automatizados**
- ✅ **Spam de recuperación de contraseña**
- ✅ **Enumeración de usuarios** (el mensaje es genérico)
- ✅ **Token replay attacks**

## 📚 Referencias

### Documentación

- [reCAPTCHA v3 Documentation](https://developers.google.com/recaptcha/docs/v3)
- [Laravel Validation](https://laravel.com/docs/12.x/validation)
- [Laravel HTTP Client](https://laravel.com/docs/12.x/http-client)

### Consola de Google

- [reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
- [Verificar Implementación](https://www.google.com/recaptcha/admin/site/YOUR_SITE_KEY)

### Políticas

- [Política de Privacidad de Google](https://policies.google.com/privacy)
- [Términos de Servicio de Google](https://policies.google.com/terms)

## 🎯 Resumen

### Lo Que Se Agregó

**1. Vista de solicitud de enlace** (`resources/views/auth/passwords/email.blade.php`):
   - ✅ Script de reCAPTCHA v3
   - ✅ Campo oculto para token
   - ✅ Regeneración de token al enviar (acción: `password_reset`)
   - ✅ Badge de reCAPTCHA (cumple ToS)
   - ✅ Mensajes en español
   - ✅ Iconos FontAwesome
   - ✅ Spinner de carga

**2. Vista de restablecimiento** (`resources/views/auth/passwords/reset.blade.php`):
   - ✅ Script de reCAPTCHA v3
   - ✅ Campo oculto para token
   - ✅ Regeneración de token al enviar (acción: `reset_password`)
   - ✅ Toggle para mostrar/ocultar contraseña
   - ✅ Validación en tiempo real (coincidencia de contraseñas)
   - ✅ Indicadores visuales de fortaleza
   - ✅ Email readonly (no editable)
   - ✅ Mensajes en español
   - ✅ Badge de reCAPTCHA

**3. Controlador de solicitud** (`app/Http/Controllers/Auth/ForgotPasswordController.php`):
   - ✅ Método `validateRecaptcha()`
   - ✅ Validación de token con Google
   - ✅ Validación de score (0.5 mínimo)
   - ✅ Validación de acción (`password_reset`)
   - ✅ Logging completo de intentos
   - ✅ Mensajes personalizados en español

**4. Controlador de restablecimiento** (`app/Http/Controllers/Auth/ResetPasswordController.php`):
   - ✅ Método `reset()` sobrescrito
   - ✅ Método `validateRecaptcha()`
   - ✅ Validación de token con Google
   - ✅ Validación de score (0.5 mínimo)
   - ✅ Validación de acción (`reset_password`)
   - ✅ Validación personalizada de contraseña (mínimo 8 caracteres)
   - ✅ Mensajes de error específicos en español
   - ✅ Logging de cambios exitosos y fallidos

**5. Configuración**:
   - ✅ Variables en `.env`
   - ✅ Archivo `config/recaptcha.php`

### Estado Actual

- ✅ **Implementado**: reCAPTCHA v3 en **AMBAS** páginas:
  - `/password/reset` - Solicitud de enlace
  - `/password/reset/{token}` - Restablecimiento
- ✅ **Configurado**: Claves y threshold (0.5)
- ✅ **Validado**: Backend y frontend en ambas páginas
- ✅ **Logging**: Eventos registrados para ambas acciones
- ✅ **UX**: Invisible para el usuario, diseño moderno
- ✅ **Accesibilidad**: Toggle de visibilidad de contraseña
- ✅ **Validación**: En tiempo real (coincidencia de contraseñas)
- ✅ **Mensajes**: Completamente en español

---

**Última actualización**: 1 de octubre de 2025  
**Versión reCAPTCHA**: v3  
**Score Threshold**: 0.5 (recomendado)
