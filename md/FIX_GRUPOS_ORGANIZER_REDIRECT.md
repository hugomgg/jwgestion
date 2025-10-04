# Fix: Perfil Organizer Redireccionado a Usuarios al Acceder a Grupos

## Problema Identificado

Los usuarios con perfil **Organizer** (y otros perfiles no Admin) eran redireccionados automáticamente a la vista de usuarios cuando intentaban acceder a la vista de grupos.

### Causa Raíz

Las rutas de grupos estaban dentro del grupo de middleware `['auth', 'admin']` que **solo permite acceso al perfil Admin (perfil 1)**.

**Ubicación del problema en routes/web.php (líneas ~195-201):**
```php
// Rutas de escritura que requieren permisos de administrador (solo perfil 1)
Route::middleware(['auth', 'admin'])->group(function () {
    // ... otras rutas ...
    
    // Gestión de Grupos - Solo para administradores (perfil 1) - ESCRITURA
    Route::get('/grupos', [App\Http\Controllers\GrupoController::class, 'index'])->name('grupos.index');
    Route::get('/grupos/data', [App\Http\Controllers\GrupoController::class, 'getData'])->name('grupos.data');
    Route::post('/grupos', [App\Http\Controllers\GrupoController::class, 'store'])->name('grupos.store');
    Route::get('/grupos/{id}', [App\Http\Controllers\GrupoController::class, 'show'])->name('grupos.show');
    Route::put('/grupos/{id}', [App\Http\Controllers\GrupoController::class, 'update'])->name('grupos.update');
    Route::delete('/grupos/{id}', [App\Http\Controllers\GrupoController::class, 'destroy'])->name('grupos.destroy');
});
```

El middleware `'admin'` está definido en `app/Http/Middleware/AdminMiddleware.php` y solo permite usuarios con `$user->isAdmin()` (perfil 1).

## Solución Implementada

Se movieron las rutas de grupos **fuera del grupo middleware admin** y se creó un nuevo grupo con middleware `['auth']` que permite acceso a todos los usuarios autenticados. La validación de permisos específicos se maneja en el controlador.

### Cambios en routes/web.php

**1. Se eliminaron las rutas de grupos del grupo admin (líneas ~195-201)**

**2. Se agregaron las rutas de grupos en un nuevo grupo después del cierre del grupo admin:**

```php
// Gestión de Grupos - Para usuarios con acceso al menú de administración o gestión de personas
Route::middleware(['auth'])->group(function () {
    // Lectura de grupos (Admin, Supervisor, Coordinator, Subcoordinator, Secretary, Subsecretary, Organizer, Suborganizer)
    Route::get('/grupos', [App\Http\Controllers\GrupoController::class, 'index'])->name('grupos.index');
    Route::get('/grupos/data', [App\Http\Controllers\GrupoController::class, 'getData'])->name('grupos.data');
    Route::get('/grupos/{id}', [App\Http\Controllers\GrupoController::class, 'show'])->name('grupos.show');
    
    // Escritura de grupos (Admin, Coordinator, Secretary, Organizer)
    Route::post('/grupos', [App\Http\Controllers\GrupoController::class, 'store'])->name('grupos.store');
    Route::put('/grupos/{id}', [App\Http\Controllers\GrupoController::class, 'update'])->name('grupos.update');
    Route::delete('/grupos/{id}', [App\Http\Controllers\GrupoController::class, 'destroy'])->name('grupos.destroy');
});
```

### Validación de Permisos en GrupoController

El controlador `GrupoController` ya tenía implementada la validación de permisos correcta:

**Métodos de lectura (index, getData, show):**
```php
if (!Auth::user()->canAccessAdminMenu() && !Auth::user()->canAccessPeopleManagementMenu()) {
    abort(403, 'No tienes permisos para acceder a esta sección.');
}
```

**Métodos de escritura (store, update, destroy):**
```php
if (!Auth::user()->isAdmin() && !Auth::user()->canModify()) {
    return response()->json([
        'success' => false,
        'message' => 'No tienes permisos para realizar esta acción.'
    ], 403);
}
```

## Perfiles con Acceso

### Perfiles que pueden VER grupos:
| Perfil | ID | Método |
|--------|----|----|
| Admin | 1 | `canAccessAdminMenu()` |
| Supervisor | 2 | `canAccessAdminMenu()` |
| Coordinator | 3 | `canAccessPeopleManagementMenu()` |
| Subcoordinator | 4 | `canAccessPeopleManagementMenu()` |
| Secretary | 5 | `canAccessPeopleManagementMenu()` |
| Subsecretary | 6 | `canAccessPeopleManagementMenu()` |
| Organizer | 7 | `canAccessPeopleManagementMenu()` |
| Suborganizer | 8 | `canAccessPeopleManagementMenu()` |

### Perfiles que pueden CREAR/EDITAR/ELIMINAR grupos:
| Perfil | ID | Método |
|--------|----|----|
| Admin | 1 | `isAdmin()` |
| Coordinator | 3 | `canModify()` |
| Secretary | 5 | `canModify()` |
| Organizer | 7 | `canModify()` |

## Flujo de Autorización

### Antes del Fix:
1. Usuario Organizer hace clic en "Grupos" en el menú
2. Laravel intenta acceder a la ruta `/grupos`
3. El middleware `admin` verifica `$user->isAdmin()`
4. Como Organizer NO es Admin, el middleware `admin` redirige al usuario
5. Usuario termina en la vista de usuarios (redirección por defecto)

### Después del Fix:
1. Usuario Organizer hace clic en "Grupos" en el menú
2. Laravel intenta acceder a la ruta `/grupos`
3. El middleware `auth` verifica que el usuario esté autenticado
4. La ruta llama a `GrupoController@index`
5. El controlador verifica `canAccessAdminMenu()` O `canAccessPeopleManagementMenu()`
6. Como Organizer tiene `canAccessPeopleManagementMenu() = true`, se permite el acceso
7. Usuario ve la vista de grupos correctamente

## Testing

### Pruebas Realizadas:
- [x] Caché de rutas limpiada con `php artisan route:clear`
- [x] Caché de configuración limpiada con `php artisan config:clear`
- [x] Caché de aplicación limpiada con `php artisan cache:clear`
- [x] Sintaxis de routes/web.php verificada sin errores

### Pruebas Pendientes:
- [ ] Iniciar sesión como Organizer y acceder a `/grupos`
- [ ] Verificar que Organizer puede ver la lista de grupos
- [ ] Verificar que Organizer puede crear un grupo
- [ ] Verificar que Organizer puede editar un grupo
- [ ] Verificar que Organizer puede eliminar un grupo
- [ ] Verificar que Suborganizer puede ver pero NO crear/editar/eliminar
- [ ] Verificar que otros perfiles (Coordinator, Secretary) mantienen acceso
- [ ] Verificar que perfiles sin permiso (ej: perfil 9+) son rechazados con 403

## Archivos Modificados

1. **routes/web.php**
   - Líneas eliminadas: ~195-201 (rutas de grupos dentro del grupo admin)
   - Líneas agregadas: ~216-228 (nuevo grupo para rutas de grupos)

## Comandos Ejecutados

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

## Conclusión

El problema estaba en la **ubicación de las rutas** dentro del archivo de rutas. Al mover las rutas de grupos fuera del grupo con middleware `admin` y colocarlas en su propio grupo con middleware `auth`, los permisos específicos se delegan al controlador, donde ya estaban correctamente implementados usando `canAccessPeopleManagementMenu()` y `canModify()`.

Esta solución:
- ✅ Permite acceso a todos los perfiles autorizados
- ✅ Mantiene la seguridad mediante validación en el controlador
- ✅ Es consistente con el patrón usado en otras rutas (como usuarios)
- ✅ No requiere cambios en el controlador ni en los modelos
- ✅ Es escalable y fácil de mantener
