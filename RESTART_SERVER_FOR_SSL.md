# 🔄 Reiniciar Servidor para Aplicar Cambios SSL

## ⚠️ Problema Actual

El error SSL persiste porque el **servidor PHP se inició ANTES** de configurar los certificados SSL. Los procesos PHP que ya están corriendo no cargan la nueva configuración de `php.ini`.

## ✅ Verificación Exitosa

La configuración SSL **SÍ está funcionando** en nuevas instancias de PHP:

```powershell
# Esta terminal reconoce el certificado ✓
php -r "echo ini_get('curl.cainfo');"
→ D:\PROGRAMAS\php\cacert.pem

# HTTPS funciona en esta terminal ✓
php -r "echo file_get_contents('https://www.google.com') ? 'SSL OK' : 'FAIL';"
→ SSL OK

# Laravel HTTP Client funciona ✓
php artisan tinker --execute="echo \Http::get('https://www.google.com')->status();"
→ 200

# reCAPTCHA API funciona ✓
php artisan tinker --execute="echo \Http::post('https://www.google.com/recaptcha/api/siteverify', ...)->status();"
→ 200
```

## 🔧 Solución: Reiniciar el Servidor

### Opción 1: Si usas `php artisan serve`

**En la terminal donde corre el servidor**:

1. **Detener el servidor**:
   - Presiona `Ctrl + C`
   - O cierra esa terminal

2. **Iniciar de nuevo**:
   ```powershell
   php artisan serve
   ```

3. **Verificar en el navegador**:
   - Visita: http://localhost:8000/login
   - Intenta iniciar sesión
   - El error SSL debe haber desaparecido

### Opción 2: Si usas Apache/XAMPP

**Reiniciar Apache**:
```powershell
# Detener Apache
net stop Apache2.4

# Iniciar Apache
net start Apache2.4
```

O desde el panel de XAMPP:
- Click en "Stop" en Apache
- Click en "Start" en Apache

### Opción 3: Si usas Nginx

```powershell
# Detener Nginx
nginx -s stop

# Iniciar Nginx
nginx
```

### Opción 4: Matar Procesos PHP Manualmente

Si no sabes qué servidor está corriendo:

```powershell
# Ver procesos PHP corriendo
Get-Process php

# Matar TODOS los procesos PHP
Get-Process php -ErrorAction SilentlyContinue | Stop-Process -Force

# Iniciar servidor de nuevo
php artisan serve
```

## 🧪 Después de Reiniciar

### 1. Verifica que el servidor está corriendo
```powershell
# Debe mostrar algo como:
# Server running on [http://127.0.0.1:8000]
```

### 2. Prueba en el navegador

**Login**:
```
http://localhost:8000/login
```

**Recuperación de contraseña**:
```
http://localhost:8000/password/reset
```

### 3. Monitorea los logs

```powershell
# Ver logs en tiempo real
Get-Content storage\logs\laravel.log -Wait -Tail 10

# Buscar errores SSL
Get-Content storage\logs\laravel.log | Select-String "cURL error 60"
```

**Si ya no aparece el error**: ✅ Problema resuelto

## 📊 Diagnóstico Rápido

### Estado Actual

| Componente | Estado | Verificación |
|-----------|--------|--------------|
| **Certificado CA** | ✅ | Descargado en `D:\PROGRAMAS\php\cacert.pem` |
| **php.ini** | ✅ | Configurado correctamente |
| **Nueva Terminal** | ✅ | HTTPS funciona |
| **Servidor Viejo** | ❌ | Necesita reinicio |

### Procesos PHP Encontrados

```
ID: 6188 → Iniciado: 12:01:23 (ANTES del fix)
ID: 19680 → Iniciado: 11:19:40 (ANTES del fix)
```

Estos procesos tienen la configuración SSL vieja en memoria.

## 🎯 Comandos Útiles

### Ver qué proceso está usando el puerto 8000

```powershell
# Windows
netstat -ano | findstr :8000

# Ver detalles del proceso
Get-Process -Id <PID>
```

### Matar proceso específico

```powershell
# Por ID
Stop-Process -Id 6188 -Force
Stop-Process -Id 19680 -Force

# Por nombre
Get-Process php | Stop-Process -Force
```

### Iniciar servidor en puerto específico

```powershell
# Puerto por defecto (8000)
php artisan serve

# Puerto personalizado
php artisan serve --port=8080

# Host específico
php artisan serve --host=0.0.0.0 --port=8000
```

## 🔍 Troubleshooting

### Error: "Address already in use"

```powershell
# El puerto 8000 está ocupado
# Opción 1: Matar el proceso
netstat -ano | findstr :8000
Stop-Process -Id <PID> -Force

# Opción 2: Usar otro puerto
php artisan serve --port=8001
```

### Error persiste después de reiniciar

```powershell
# 1. Verificar php.ini EN EL SERVIDOR
php --ini

# 2. Verificar configuración
php -r "echo ini_get('curl.cainfo');"

# 3. Test de conexión
php -r "file_get_contents('https://www.google.com'); echo 'OK';"

# 4. Limpiar cachés
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 5. Reiniciar COMPLETAMENTE
Get-Process php | Stop-Process -Force
php artisan serve
```

### Verificar que el certificado se carga

```powershell
# En tinker (con servidor corriendo)
php artisan tinker

# Ejecutar:
\Http::withOptions(['verify' => true])
    ->get('https://www.google.com')
    ->status()

# Debe retornar: 200
```

## 📋 Checklist de Reinicio

- [ ] Identificar qué servidor web estás usando
- [ ] Detener el servidor actual
- [ ] Verificar que no hay procesos PHP corriendo (`Get-Process php`)
- [ ] Limpiar cachés de Laravel
- [ ] Iniciar el servidor de nuevo
- [ ] Probar login en el navegador
- [ ] Probar recuperación de contraseña
- [ ] Verificar logs (no debe haber error SSL)

## ✅ Confirmación de Éxito

Después de reiniciar, deberías ver en los logs:

```
[INFO] reCAPTCHA verification successful
{
    "email": "usuario@ejemplo.com",
    "score": 0.9,
    "action": "login"
}
```

En lugar de:

```
[ERROR] cURL error 60: SSL certificate problem
```

## 🎯 Resumen

1. **Problema**: Servidor PHP inició antes del fix SSL
2. **Causa**: Procesos PHP no recargan php.ini automáticamente
3. **Solución**: Reiniciar el servidor web
4. **Verificación**: Probar login/password reset
5. **Resultado esperado**: Sin errores SSL en logs

---

**IMPORTANTE**: Una vez reiniciado el servidor, el problema desaparecerá completamente. La configuración SSL está correcta, solo necesita que el servidor la recargue.
