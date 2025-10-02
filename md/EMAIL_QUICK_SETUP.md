# Configuración Rápida de Email - Guía Paso a Paso

## 🚀 Configuración en 5 Minutos

### Opción 1: Gmail (Más Común)

**1. Preparar Gmail:**
- Ir a: https://myaccount.google.com/security
- Activar "Verificación en 2 pasos"
- Ir a "Contraseñas de aplicaciones": https://myaccount.google.com/apppasswords
- Seleccionar "Correo" y "Otro (nombre personalizado)"
- Escribir: "Laravel App"
- Copiar la contraseña de 16 caracteres (sin espacios)

**2. Editar archivo `.env`:**

Reemplazar estas líneas:
```env
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Por estas:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=contraseña-de-16-caracteres-aqui
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**3. Ejecutar comandos:**
```bash
php artisan config:clear
php artisan cache:clear
```

**4. Probar:**
- Ir a: http://localhost/password/reset
- Ingresar un email de usuario registrado
- Revisar tu bandeja de entrada

---

### Opción 2: Mailtrap (Para Desarrollo/Pruebas)

**1. Crear cuenta gratuita:**
- Ir a: https://mailtrap.io
- Registrarse (gratis)
- Crear un nuevo inbox
- Copiar las credenciales SMTP

**2. Editar archivo `.env`:**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu-username-mailtrap
MAIL_PASSWORD=tu-password-mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tuapp.com
MAIL_FROM_NAME="${APP_NAME}"
```

**3. Ejecutar comandos:**
```bash
php artisan config:clear
php artisan cache:clear
```

**4. Probar:**
- Ir a: http://localhost/password/reset
- Ingresar cualquier email
- Ver el correo en Mailtrap

---

### Opción 3: Mantener LOG (Solo para Ver el Token)

Si no quieres configurar email real, puedes ver el token en los logs:

**1. No cambiar nada en `.env` (dejar `MAIL_MAILER=log`)**

**2. Después de solicitar recuperación de contraseña:**

En Windows PowerShell:
```powershell
Get-Content storage\logs\laravel.log -Tail 50 | Select-String "password"
```

En Windows CMD:
```cmd
type storage\logs\laravel.log | findstr "password"
```

**3. Buscar una línea similar a:**
```
http://localhost/password/reset/4f1g23c21f45d80ef0d7c8c3b6fc8ca9d2d7e1d5?email=usuario@email.com
```

**4. Copiar y pegar ese enlace en el navegador**

---

## ✅ Verificación

### Probar que funciona:

**Método 1: Desde la interfaz web**
1. Ir a http://localhost/password/reset
2. Ingresar email de un usuario
3. Verificar mensaje de éxito
4. Revisar email o logs

**Método 2: Desde Tinker (Prueba técnica)**
```bash
php artisan tinker
```

Ejecutar:
```php
Mail::raw('Email de prueba', function($message) {
    $message->to('tu-email@example.com')
            ->subject('Prueba de Email - Laravel');
});
```

Si no hay errores, el sistema está configurado correctamente.

---

## 🐛 Solución de Problemas Comunes

### Error: "Connection refused"
**Causa**: Puerto bloqueado o host incorrecto
**Solución**: Verificar firewall o usar puerto 465 con SSL

### Error: "Authentication failed"
**Causa**: Credenciales incorrectas
**Solución**: 
- Gmail: Usar App Password, NO la contraseña normal
- Verificar que el username sea el email completo

### Error: "SSL certificate problem"
**Causa**: Problema con certificados SSL
**Solución temporal (solo desarrollo)**:
```env
MAIL_ENCRYPTION=null
MAIL_PORT=587
```

### No recibo el email
**Verificar**:
1. Carpeta de spam/correo no deseado
2. El email del usuario existe en la base de datos
3. El usuario tiene `email` válido (no NULL)
4. Los logs en `storage/logs/laravel.log`

---

## 📊 Verificar Usuarios con Email

**En Tinker:**
```bash
php artisan tinker
```

```php
// Ver usuarios con email
User::whereNotNull('email')->get(['id', 'name', 'email']);

// Actualizar email de un usuario
$user = User::find(1);
$user->email = 'nuevo-email@example.com';
$user->save();
```

---

## 🔐 Configuración Recomendada por Ambiente

### Desarrollo Local:
```env
MAIL_MAILER=log
# O usar Mailtrap
```

### Staging/Testing:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
# Credenciales de Mailtrap
```

### Producción:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
# O servidor SMTP dedicado
# Usar variables de entorno seguras
```

---

## 📝 Checklist Final

- [ ] Decidir qué método usar (Gmail/Mailtrap/LOG)
- [ ] Actualizar variables en `.env`
- [ ] Ejecutar `php artisan config:clear`
- [ ] Verificar que usuarios tengan email válido
- [ ] Probar recuperación desde la interfaz
- [ ] Verificar recepción del email o revisar logs
- [ ] Probar que el enlace de recuperación funcione
- [ ] Documentar las credenciales usadas (en lugar seguro)

---

## 🎯 Siguiente Paso

Una vez configurado el email, prueba el flujo completo:

1. http://localhost/password/reset
2. Ingresar email de usuario
3. Hacer clic en el enlace del email
4. Cambiar la contraseña
5. Iniciar sesión con la nueva contraseña

---

**¿Necesitas ayuda?** Revisa el archivo `PASSWORD_RESET_SETUP.md` para documentación detallada.
