# Configuración de Google reCAPTCHA v3

Este sistema utiliza **Google reCAPTCHA v3** para proteger el formulario de inicio de sesión contra bots y ataques automatizados.

## ¿Qué es reCAPTCHA v3?

reCAPTCHA v3 es la versión más moderna de Google reCAPTCHA que:
- ✅ **Es invisible** para los usuarios legítimos
- ✅ **No requiere interacción** (sin "selecciona los semáforos")
- ✅ **Analiza el comportamiento** del usuario en segundo plano
- ✅ **Asigna un score** de 0.0 (bot) a 1.0 (humano)
- ✅ **Funciona automáticamente** sin interrumpir la experiencia del usuario

## Cómo obtener las claves de reCAPTCHA

### Paso 1: Acceder a la consola de Google reCAPTCHA

1. Ve a: https://www.google.com/recaptcha/admin
2. Inicia sesión con tu cuenta de Google

### Paso 2: Registrar un nuevo sitio

1. Haz clic en el botón **"+"** (Agregar)
2. Completa el formulario:
   - **Etiqueta**: Nombre descriptivo (ej: "Sistema de Gestión JW - Login")
   - **Tipo de reCAPTCHA**: Selecciona **"reCAPTCHA v3"**
   - **Dominios**: Agrega tus dominios (ej: `midominio.com`, `localhost` para desarrollo)
   - **Propietarios**: Tu email de Google (opcional)
   - **Acepta los términos de servicio**
3. Haz clic en **"Enviar"**

### Paso 3: Obtener las claves

Después de registrar el sitio, verás dos claves:

1. **Clave del sitio (Site Key)**: Se usa en el frontend (JavaScript)
2. **Clave secreta (Secret Key)**: Se usa en el backend (PHP/Laravel)

### Paso 4: Configurar las claves en Laravel

Edita el archivo `.env` y reemplaza los valores:

```env
# Google reCAPTCHA v3 Configuration
RECAPTCHA_SITE_KEY=tu_clave_del_sitio_aqui
RECAPTCHA_SECRET_KEY=tu_clave_secreta_aqui
RECAPTCHA_ENABLED=true
RECAPTCHA_SCORE_THRESHOLD=0.5
```

### Paso 5: Limpiar caché de Laravel

```bash
php artisan config:clear
php artisan cache:clear
```

## Configuración del Score Threshold

El `RECAPTCHA_SCORE_THRESHOLD` determina qué tan estricta es la verificación:

- **0.9 - 1.0**: Muy estricto (puede bloquear usuarios legítimos)
- **0.7 - 0.8**: Estricto (recomendado para alta seguridad)
- **0.5 - 0.6**: Balanceado (recomendado) ⭐
- **0.3 - 0.4**: Permisivo (puede permitir algunos bots)
- **0.0 - 0.2**: Muy permisivo (no recomendado)

**Recomendación**: Empieza con **0.5** y ajusta según tu experiencia.

## Dominios para desarrollo y producción

### Desarrollo local:
```
localhost
127.0.0.1
```

### Producción:
```
tudominio.com
www.tudominio.com
```

**Importante**: Puedes agregar múltiples dominios en la misma configuración de reCAPTCHA.

## Deshabilitar reCAPTCHA temporalmente

Si necesitas deshabilitar la verificación (no recomendado en producción):

```env
RECAPTCHA_ENABLED=false
```

## Monitoreo y estadísticas

Google reCAPTCHA proporciona estadísticas en tiempo real:

1. Ve a: https://www.google.com/recaptcha/admin
2. Selecciona tu sitio
3. Ve a la pestaña **"Analytics"**

Aquí puedes ver:
- Número de verificaciones
- Distribución de scores
- Intentos bloqueados
- Patrones de tráfico

## Solución de problemas

### Error: "Invalid site key"
- Verifica que la `RECAPTCHA_SITE_KEY` sea correcta
- Asegúrate de que el dominio esté registrado en la consola de reCAPTCHA

### Error: "Invalid secret key"
- Verifica que la `RECAPTCHA_SECRET_KEY` sea correcta
- Ejecuta `php artisan config:clear`

### El badge de reCAPTCHA no aparece
- Verifica que el script de reCAPTCHA se esté cargando
- Revisa la consola del navegador para errores de JavaScript

### Score muy bajo para usuarios legítimos
- Reduce el `RECAPTCHA_SCORE_THRESHOLD` (ej: de 0.5 a 0.4)
- Revisa los logs en `storage/logs/laravel.log`

## Logs y seguridad

El sistema registra todas las verificaciones en el log de Laravel:

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log
```

Los eventos registrados incluyen:
- ✅ Verificaciones exitosas con score
- ⚠️ Verificaciones fallidas con razón
- ❌ Scores bajos con detalles
- 🔧 Errores del servicio de reCAPTCHA

## Características de seguridad implementadas

1. ✅ **Verificación en cada login**: Protección contra bots
2. ✅ **Score threshold configurable**: Ajusta según tus necesidades
3. ✅ **Logging completo**: Auditoría de intentos de acceso
4. ✅ **Mensajes de error claros**: Feedback al usuario
5. ✅ **Manejo de errores robusto**: Fallback en caso de problemas
6. ✅ **IP tracking**: Registra IPs de intentos sospechosos
7. ✅ **Token único por sesión**: Mayor seguridad

## Mejores prácticas

1. 🔐 **Nunca compartas** tu clave secreta
2. 📊 **Monitorea las estadísticas** regularmente
3. 🎯 **Ajusta el threshold** según tu tráfico
4. 📝 **Revisa los logs** periódicamente
5. 🔄 **Rota las claves** anualmente por seguridad
6. 🌐 **Actualiza dominios** cuando cambies de hosting
7. 🚀 **Prueba en desarrollo** antes de producción

## Soporte

Para más información sobre reCAPTCHA:
- Documentación oficial: https://developers.google.com/recaptcha/docs/v3
- FAQ: https://developers.google.com/recaptcha/docs/faq
- Soporte: https://support.google.com/recaptcha

---

**Nota**: reCAPTCHA v3 requiere conexión a internet para funcionar. En entornos sin internet, deshabilita la verificación con `RECAPTCHA_ENABLED=false`.
