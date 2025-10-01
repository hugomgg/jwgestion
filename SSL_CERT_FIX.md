# Solución para cURL Error 60: SSL Certificate Problem

## 🔴 Error Completo
```
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

## ⚠️ Causa
PHP/cURL en Windows no puede verificar certificados SSL porque no tiene un bundle de certificados CA (Certificate Authority) configurado.

## ✅ Solución Implementada

### Paso 1: Certificado Descargado
```
✓ Archivo: D:\PROGRAMAS\php\cacert.pem
✓ Descargado desde: https://curl.se/ca/cacert.pem
✓ Tamaño: ~200KB
✓ Actualizado: Certificados CA de confianza de Mozilla
```

### Paso 2: Configurar php.ini

**Archivo a editar**: `D:\PROGRAMAS\php\php.ini`

**Agregar o descomentar estas líneas**:

```ini
[curl]
curl.cainfo = "D:\PROGRAMAS\php\cacert.pem"

[openssl]
openssl.cafile = "D:\PROGRAMAS\php\cacert.pem"
```

## 📝 Pasos Manuales (Si el Script Falló)

### 1. Descargar Certificado CA Bundle

```powershell
# Opción 1: Con PowerShell
Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile "D:\PROGRAMAS\php\cacert.pem"

# Opción 2: Manual
# - Visita: https://curl.se/ca/cacert.pem
# - Guarda como: D:\PROGRAMAS\php\cacert.pem
```

### 2. Editar php.ini

```powershell
# Abrir php.ini con Notepad
notepad "D:\PROGRAMAS\php\php.ini"
```

**Buscar y modificar (o agregar al final)**:

```ini
; Busca estas líneas (pueden estar comentadas con ;)
;curl.cainfo =
;openssl.cafile =

; Reemplázalas por:
curl.cainfo = "D:\PROGRAMAS\php\cacert.pem"
openssl.cafile = "D:\PROGRAMAS\php\cacert.pem"
```

### 3. Verificar Configuración

```powershell
# Ver configuración actual
php -r "echo ini_get('curl.cainfo');"
php -r "echo ini_get('openssl.cafile');"

# Debe mostrar: D:\PROGRAMAS\php\cacert.pem
```

### 4. Reiniciar

- **Si usas terminal**: Cierra y reabre
- **Si usas servidor web**: Reinicia el servicio
  ```powershell
  # Para Apache/Nginx en Windows
  net stop <servicio>
  net start <servicio>
  ```

## 🧪 Probar la Solución

### Test Rápido con PHP

```powershell
php -r "file_get_contents('https://www.google.com'); echo 'SSL OK';"
```

**Resultado esperado**: `SSL OK`

### Test en Laravel

```powershell
# Limpiar cachés
php artisan config:clear
php artisan cache:clear

# Probar una petición HTTPS (ejemplo: reCAPTCHA)
# Visita tu página de login y prueba iniciar sesión
```

### Test Específico de reCAPTCHA

```php
// En tinker
php artisan tinker

// Ejecutar
$response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
    'secret' => config('recaptcha.secret_key'),
    'response' => 'test-token',
]);

echo $response->status();
// Debe mostrar: 200
```

## 🔧 Troubleshooting

### Problema: php.ini no se actualiza

```powershell
# Ver qué php.ini está usando PHP
php --ini

# Verificar la ruta mostrada en "Loaded Configuration File"
# Editar ESE archivo específicamente
```

### Problema: Ruta del certificado incorrecta

```powershell
# Verificar que el archivo existe
Test-Path "D:\PROGRAMAS\php\cacert.pem"
# Debe mostrar: True

# Ver tamaño del archivo
(Get-Item "D:\PROGRAMAS\php\cacert.pem").Length
# Debe ser ~200000-250000 bytes
```

### Problema: Cambios no toman efecto

```powershell
# 1. Verificar que editaste el php.ini correcto
php --ini

# 2. Reiniciar terminal completamente (cerrar y abrir)

# 3. Limpiar cachés de Laravel
php artisan config:clear
php artisan cache:clear

# 4. Verificar configuración actual
php -i | Select-String "curl.cainfo"
```

### Problema: Error persiste

**Opción alternativa**: Desactivar verificación SSL (SOLO para desarrollo)

En tu código Laravel:
```php
// SOLO PARA DESARROLLO - NO USAR EN PRODUCCIÓN
Http::withOptions([
    'verify' => false,
])->post('https://...');
```

O en `.env`:
```env
# SOLO DESARROLLO
CURL_VERIFY_SSL=false
```

## 📊 Verificación de Estado

### Checklist

- [ ] Certificado descargado en `D:\PROGRAMAS\php\cacert.pem`
- [ ] `php.ini` editado con rutas correctas
- [ ] Terminal reiniciada
- [ ] `php -r "echo ini_get('curl.cainfo');"` muestra la ruta
- [ ] Test de HTTPS funciona
- [ ] Laravel puede conectarse a APIs externas
- [ ] reCAPTCHA funciona en el login

### Comandos de Verificación

```powershell
# 1. Verificar php.ini en uso
php --ini

# 2. Verificar configuración
php -r "echo 'curl.cainfo: ' . ini_get('curl.cainfo') . PHP_EOL;"
php -r "echo 'openssl.cafile: ' . ini_get('openssl.cafile') . PHP_EOL;"

# 3. Test de conexión HTTPS
php -r "try { file_get_contents('https://www.google.com'); echo 'SSL: OK'; } catch (Exception $e) { echo 'ERROR: ' . $e->getMessage(); }"

# 4. Test en Laravel
php artisan tinker
> Http::get('https://www.google.com')->status()
```

## 🔐 Seguridad

### ¿Es seguro este método?

✅ **SÍ** - Este es el método oficial recomendado por PHP y cURL.

- El archivo `cacert.pem` contiene certificados CA de confianza de Mozilla
- Es actualizado regularmente por el equipo de cURL
- Es el mismo bundle usado por navegadores

### Actualizar el Certificado

Se recomienda actualizar cada 3-6 meses:

```powershell
# Re-descargar el certificado actualizado
Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile "D:\PROGRAMAS\php\cacert.pem"
```

## 📚 Referencias

- [PHP cURL Documentation](https://www.php.net/manual/en/book.curl.php)
- [cURL CA Certificate Bundle](https://curl.se/docs/caextract.html)
- [Stack Overflow: cURL error 60](https://stackoverflow.com/questions/24611640/curl-60-ssl-certificate-problem-unable-to-get-local-issuer-certificate)

## 🎯 Para Este Proyecto

### Dónde se usa HTTPS/SSL

1. **reCAPTCHA Verification**
   - `ForgotPasswordController` → Verifica tokens con Google
   - `ResetPasswordController` → Verifica tokens con Google  
   - `LoginController` → Verifica tokens con Google

2. **Composer** (al instalar paquetes)
   - Descarga paquetes desde Packagist via HTTPS

3. **NPM/Yarn** (al instalar dependencias)
   - Descarga paquetes desde registry via HTTPS

### Archivos Afectados

```
app/Http/Controllers/Auth/ForgotPasswordController.php
app/Http/Controllers/Auth/ResetPasswordController.php
app/Http/Controllers/Auth/LoginController.php

// Todos usan Http::post() para verificar reCAPTCHA:
$response = \Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [...]);
```

## ✅ Resumen

**Problema**: PHP no puede verificar certificados SSL
**Causa**: Falta el bundle de certificados CA
**Solución**: Descargar `cacert.pem` y configurar `php.ini`
**Resultado**: ✓ Todas las peticiones HTTPS funcionan

---

**Última actualización**: 1 de octubre de 2025
**Estado**: ✅ Certificado descargado en `D:\PROGRAMAS\php\cacert.pem`
**Siguiente paso**: Editar `php.ini` y reiniciar terminal
