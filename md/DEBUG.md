# 🐛 Herramientas de Debug para Laravel

## Laravel Debugbar

Laravel Debugbar ya está instalado y configurado en tu aplicación. Esta herramienta te permite ver información detallada sobre:

- **Queries de Base de Datos** - Ver todas las consultas SQL ejecutadas
- **Tiempo de Ejecución** - Analizar el rendimiento de tu aplicación
- **Variables de Sesión** - Inspeccionar datos de sesión
- **Request/Response** - Ver detalles de las peticiones HTTP
- **Logs** - Visualizar logs en tiempo real
- **Views** - Ver qué templates se están renderizando
- **Cache** - Monitorear el uso de cache

### Configuración

En tu archivo `.env` de desarrollo:
```env
APP_DEBUG=true
DEBUGBAR_ENABLED=true
DEBUGBAR_CAPTURE_AJAX=true
```

En producción (`.env.production.example`):
```env
APP_DEBUG=false
DEBUGBAR_ENABLED=false
DEBUGBAR_CAPTURE_AJAX=false
```

### Uso Básico

La barra de debug aparecerá automáticamente en la parte inferior de tu aplicación cuando:
- `APP_DEBUG=true`
- `DEBUGBAR_ENABLED=true`
- Estás en entorno de desarrollo

### Comandos Útiles para Debug

```bash
# Ver logs en tiempo real
php artisan tail

# Limpiar logs
php artisan log:clear

# Ver información de la aplicación
php artisan about

# Inspeccionar rutas
php artisan route:list

# Ver eventos en tiempo real
php artisan tinker
```

### Debug en Controladores

Puedes usar estas funciones para debugging:

```php
// En cualquier controlador o método
dd($variable); // Dump and die - detiene la ejecución

dump($variable); // Solo muestra la variable sin detener

// Logging personalizado
Log::info('Debug message', ['data' => $variable]);
Log::error('Error occurred', ['error' => $exception]);

// Debug específico de Debugbar
\Debugbar::info('Custom message');
\Debugbar::error('Error message');
\Debugbar::warning('Warning message');
```

### Debug en Blade Templates

```php
<!-- En archivos .blade.php -->
@dump($variable)

@php
    dd($variable); // Solo en desarrollo
@endphp

<!-- Logging desde blade -->
@php
    Log::info('Template data', compact('user', 'data'));
@endphp
```

### Debug de Queries

```php
// Ver todas las queries ejecutadas
\DB::enableQueryLog();
// ... tu código aquí ...
$queries = \DB::getQueryLog();
dd($queries);

// Debug de una query específica
$users = User::where('active', 1)->toSql(); // Ver SQL sin ejecutar
dd($users);
```

### Herramientas Adicionales

#### 1. Laravel Tinker (Ya instalado)
```bash
php artisan tinker
```

Permite interactuar con tu aplicación desde la línea de comandos:
```php
// Ejemplos en tinker
User::count()
User::first()
config('app.name')
```

#### 2. Laravel Pail (Ya instalado)
```bash
php artisan pail
```

Monitor de logs en tiempo real con mejor formato.

### Configuración Avanzada de Debugbar

El archivo de configuración está en `config/debugbar.php`. Algunas opciones útiles:

```php
// Mostrar solo en ciertos entornos
'enabled' => env('DEBUGBAR_ENABLED', null),

// Capturar peticiones AJAX
'capture_ajax' => env('DEBUGBAR_CAPTURE_AJAX', true),

// Collectors específicos
'collectors' => [
    'phpinfo'         => true,  // Información de PHP
    'messages'        => true,  // Logs personalizados
    'time'           => true,  // Tiempo de ejecución
    'memory'         => true,  // Uso de memoria
    'exceptions'     => true,  // Excepciones
    'log'           => true,  // Logs de Laravel
    'db'            => true,  // Queries de DB
    'views'         => true,  // Templates renderizados
    'route'         => true,  // Información de rutas
    'auth'          => true,  // Usuario autenticado
    'gate'          => true,  // Autorizaciones
    'session'       => true,  // Datos de sesión
    'symfony_request' => true,  // Request de Symfony
    'mail'          => true,  // Emails enviados
],
```

## Tips de Debugging

### 1. Debug de Autenticación
```php
// Ver usuario actual
dd(auth()->user());
dd(auth()->check()); // true si está autenticado
dd(auth()->id()); // ID del usuario
```

### 2. Debug de Middleware
```php
// En tu middleware
public function handle($request, Closure $next)
{
    \Log::info('Middleware executed', [
        'route' => $request->route()->getName(),
        'user' => auth()->id()
    ]);
    
    return $next($request);
}
```

### 3. Debug de Validaciones
```php
// En FormRequest
public function failedValidation(Validator $validator)
{
    dd($validator->errors()->all());
}
```

### 4. Performance Debugging
```php
// Medir tiempo de ejecución
$start = microtime(true);
// ... tu código aquí ...
$end = microtime(true);
\Log::info('Execution time: ' . ($end - $start) . ' seconds');
```

## Checklist de Debug

- [ ] Debugbar habilitada en desarrollo
- [ ] Logs configurados correctamente
- [ ] Variables de entorno verificadas
- [ ] Queries optimizadas (revisar en Debugbar)
- [ ] Tiempo de respuesta aceptable
- [ ] Memoria utilizada bajo control
- [ ] No hay errores en logs
- [ ] Debugbar deshabilitada en producción

## Herramientas Externas Recomendadas

1. **Laravel Telescope** - Para aplicaciones más complejas
2. **Clockwork** - Alternativa a Debugbar
3. **Laravel Ray** - Herramienta de debug visual (de pago)
4. **Xdebug** - Para debug paso a paso con IDE