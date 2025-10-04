# Implementación de Menú "Grupos" con Permisos por Perfil

## Resumen
Se ha agregado la opción "Grupos" en el menú lateral de la sección ADMINISTRACIÓN para los perfiles específicos con permisos diferenciados según el tipo de usuario.

## Perfiles con Acceso

### Con Permisos de Lectura/Escritura (CRUD Completo)
Los siguientes perfiles pueden **crear, editar y eliminar** grupos:
- **Perfil 1**: Admin
- **Perfil 3**: Coordinator
- **Perfil 5**: Secretary
- **Perfil 7**: Organizer

### Con Permisos de Solo Lectura
Los siguientes perfiles pueden **solo ver** los grupos:
- **Perfil 2**: Supervisor
- **Perfil 4**: Subcoordinator
- **Perfil 6**: Subsecretary
- **Perfil 8**: Suborganizer

## Archivos Modificados

### 1. resources/views/layouts/app.blade.php
**Cambios realizados:**
- Agregado menú "Grupos" en la primera sección de ADMINISTRACIÓN (líneas ~199-203)
- Agregado menú "Grupos" en la segunda sección de ADMINISTRACIÓN (líneas ~222-226)

**Código agregado:**
```php
@if(auth()->user()->isCoordinator() || auth()->user()->isSubcoordinator() || auth()->user()->isSecretary() || auth()->user()->isSubsecretary() || auth()->user()->isOrganizer() || auth()->user()->isSuborganizer())
<li>
    <a href="{{ route('grupos.index') }}" class="nav-link {{ request()->routeIs('grupos.*') ? 'active' : '' }}">
        <i class="fas fa-layer-group"></i>Grupos
    </a>
</li>
@endif
```

### 2. app/Http/Controllers/GrupoController.php
**Cambios realizados:**
- **Método `index()`**: Modificado para permitir acceso a usuarios con `canAccessPeopleManagementMenu()`
- **Método `getData()`**: Modificado para permitir acceso a usuarios con `canAccessPeopleManagementMenu()`
- **Métodos `store()`, `update()`, `destroy()`**: Modificados para permitir acceso solo a usuarios con `canModify()`

**Permisos de acceso:**
```php
// Ver grupos (index y getData)
if (!Auth::user()->canAccessAdminMenu() && !Auth::user()->canAccessPeopleManagementMenu()) {
    abort(403, 'No tienes permisos para acceder a esta sección.');
}

// Crear/Editar/Eliminar grupos (store, update, destroy)
if (!Auth::user()->isAdmin() && !Auth::user()->canModify()) {
    return response()->json([
        'success' => false,
        'message' => 'No tienes permisos para realizar esta acción.'
    ], 403);
}
```

## Métodos del Modelo User Utilizados

### canAccessPeopleManagementMenu()
Retorna `true` para perfiles: 1, 2, 3, 4, 5, 6, 7, 8
- Permite acceso al menú de gestión de personas

### canModify()
Retorna `true` para perfiles: 1, 3, 5, 7 (Admin, Coordinator, Secretary, Organizer)
- Permite crear, editar y eliminar registros

### Métodos de verificación de perfil:
- `isCoordinator()` - Perfil 3
- `isSubcoordinator()` - Perfil 4
- `isSecretary()` - Perfil 5
- `isSubsecretary()` - Perfil 6
- `isOrganizer()` - Perfil 7
- `isSuborganizer()` - Perfil 8

## Comportamiento en la Interfaz

### Vista grupos/index.blade.php
Ya estaba configurada correctamente con:
```php
@if(Auth::user()->canModify())
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGrupoModal">
        <i class="fas fa-plus me-2"></i>Agregar Grupo
    </button>
@endif
```

### JavaScript (public/js/grupos-index.js)
Los botones de editar y eliminar solo se muestran si `canModify` es `true`:
```javascript
if (window.gruposIndexConfig.canModify) {
    return `
        <button class="btn btn-sm btn-primary edit-btn" data-id="${data}">
            <i class="fas fa-edit"></i>
        </button>
        <button class="btn btn-sm btn-danger delete-btn" data-id="${data}">
            <i class="fas fa-trash"></i>
        </button>
    `;
}
return '<span class="text-muted">Sin acciones</span>';
```

## Flujo de Permisos

### Usuarios con Perfil Coordinator, Secretary u Organizer (3, 5, 7):
1. ✅ Ven el menú "Grupos" en ADMINISTRACIÓN
2. ✅ Pueden acceder a la página de grupos
3. ✅ Ven el botón "Agregar Grupo"
4. ✅ Ven botones de Editar y Eliminar en cada grupo
5. ✅ Pueden crear, editar y eliminar grupos

### Usuarios con Perfil Subcoordinator, Subsecretary o Suborganizer (4, 6, 8):
1. ✅ Ven el menú "Grupos" en ADMINISTRACIÓN
2. ✅ Pueden acceder a la página de grupos
3. ❌ NO ven el botón "Agregar Grupo"
4. ❌ NO ven botones de Editar y Eliminar
5. ✅ Solo pueden visualizar los datos en la tabla

### Usuarios Admin y Supervisor (1, 2):
1. ✅ Ven el menú "Grupos" en ADMINISTRACIÓN
2. ✅ Pueden acceder a la página de grupos
3. Admin (1): Tiene acceso completo CRUD
4. Supervisor (2): Solo puede visualizar

## Validaciones de Seguridad

### Nivel de Controlador
Todos los métodos del `GrupoController` validan permisos:
- `index()` y `getData()`: Requieren `canAccessAdminMenu()` O `canAccessPeopleManagementMenu()`
- `store()`, `update()`, `destroy()`: Requieren `isAdmin()` O `canModify()`

### Nivel de Vista
La vista valida con `@if(Auth::user()->canModify())` antes de mostrar:
- Botón "Agregar Grupo"
- Pasar la variable `canModify` al JavaScript

### Nivel de JavaScript
El JavaScript valida `window.gruposIndexConfig.canModify` antes de renderizar:
- Botones de Editar
- Botones de Eliminar

## Testing Recomendado

### Probar con cada perfil:
1. **Admin (Perfil 1)**: Debería tener acceso completo
2. **Supervisor (Perfil 2)**: Solo debería poder ver
3. **Coordinator (Perfil 3)**: Debería tener acceso completo
4. **Subcoordinator (Perfil 4)**: Solo debería poder ver
5. **Secretary (Perfil 5)**: Debería tener acceso completo
6. **Subsecretary (Perfil 6)**: Solo debería poder ver
7. **Organizer (Perfil 7)**: Debería tener acceso completo
8. **Suborganizer (Perfil 8)**: Solo debería poder ver

### Verificaciones:
- [ ] El menú "Grupos" aparece para los perfiles correctos
- [ ] El menú NO aparece para otros perfiles
- [ ] Los usuarios con `canModify()` ven el botón "Agregar Grupo"
- [ ] Los usuarios sin `canModify()` NO ven el botón "Agregar Grupo"
- [ ] Los usuarios con `canModify()` ven botones Editar/Eliminar
- [ ] Los usuarios sin `canModify()` NO ven botones Editar/Eliminar
- [ ] Intentar acceso directo a rutas sin permisos retorna error 403
- [ ] Intentar crear/editar/eliminar vía AJAX sin permisos retorna error 403

## Conclusión

La implementación está completa y cumple con todos los requisitos:
- ✅ Menú "Grupos" visible para perfiles específicos
- ✅ Permisos diferenciados por perfil
- ✅ Validación en controlador, vista y JavaScript
- ✅ Seguridad implementada en todos los niveles
