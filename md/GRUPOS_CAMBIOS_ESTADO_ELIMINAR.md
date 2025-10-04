# Cambios en la Gestión de Grupos

## Resumen de Cambios

Se realizaron dos modificaciones principales en la gestión de grupos:

1. **Eliminación del botón "Eliminar grupo"**
2. **Cambio en la nomenclatura de estados:**
   - `1 = Activo` → `1 = Habilitado`
   - `0 = Inactivo` → `0 = Deshabilitado`

## Archivos Modificados

### 1. resources/views/grupos/index.blade.php

**Cambios en el filtro de estado (líneas ~17-23):**
```php
<select class="form-select" id="estadoFilter" style="width: auto;">
    <option value="">Todos</option>
    <option value="1">Habilitado</option>      // Antes: Activo
    <option value="0">Deshabilitado</option>   // Antes: Inactivo
</select>
```

**Cambios en el modal de agregar grupo (líneas ~94-100):**
```php
<select class="form-select" id="estado" name="estado" required>
    <option value="">Seleccionar estado...</option>
    <option value="1">Habilitado</option>      // Antes: Activo
    <option value="0">Deshabilitado</option>   // Antes: Inactivo
</select>
```

**Cambios en el modal de editar grupo (líneas ~146-152):**
```php
<select class="form-select" id="edit_estado" name="estado" required>
    <option value="">Seleccionar estado...</option>
    <option value="1">Habilitado</option>      // Antes: Activo
    <option value="0">Deshabilitado</option>   // Antes: Inactivo
</select>
```

### 2. public/js/grupos-index.js

**Cambios en el renderizado de la columna Estado (líneas ~26-34):**
```javascript
render: function(data, type, row) {
    if (data == 1) {
        return '<span class="badge bg-success">Habilitado</span>';    // Antes: Activo
    } else {
        return '<span class="badge bg-danger">Deshabilitado</span>';  // Antes: Inactivo
    }
}
```

**Eliminación del botón de eliminar en la columna Acciones (líneas ~47-75):**
```javascript
// ANTES:
if (window.gruposIndexConfig.canModify) {
    buttons += `
        <button type="button" class="btn btn-sm btn-warning edit-grupo"...>
            <i class="fas fa-edit"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger delete-grupo"...>
            <i class="fas fa-trash"></i>
        </button>`;
}

// AHORA:
if (window.gruposIndexConfig.canModify) {
    buttons += `
        <button type="button" class="btn btn-sm btn-warning edit-grupo"...>
            <i class="fas fa-edit"></i>
        </button>`;
    // Botón de eliminar removido
}
```

**Eliminación del evento de eliminar grupo (líneas ~287-312 eliminadas):**
```javascript
// TODO EL SIGUIENTE CÓDIGO FUE ELIMINADO:
$(document).on('click', '.delete-grupo', function() {
    const grupoId = $(this).data('grupo-id');
    const grupoRow = $(this).closest('tr');
    const grupoNombre = grupoRow.find('td:nth-child(2)').text();
    
    if (confirm(`¿Está seguro que desea eliminar el grupo "${grupoNombre}"?`)) {
        $.ajax({
            url: `/grupos/${grupoId}`,
            method: 'DELETE',
            // ... código AJAX ...
        });
    }
});
```

**Cambios en el modal de ver grupo (línea ~210):**
```javascript
$('#view_grupo_estado').html(
    grupo.estado == 1 
        ? '<span class="badge bg-success">Habilitado</span>'    // Antes: Activo
        : '<span class="badge bg-danger">Deshabilitado</span>'  // Antes: Inactivo
);
```

### 3. app/Http/Controllers/GrupoController.php

**Cambios en el método `store()` (líneas ~76-89):**
```php
$validator = Validator::make($request->all(), [
    'nombre' => 'required|string|max:255|unique:grupos,nombre',
    'congregacion_id' => 'required|integer|exists:congregaciones,id',
    'estado' => 'required|integer|in:0,1'
], [
    'nombre.required' => 'El nombre es obligatorio.',
    'nombre.unique' => 'Ya existe un grupo con este nombre.',
    'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
    'congregacion_id.required' => 'La congregación es obligatoria.',
    'congregacion_id.exists' => 'La congregación seleccionada no existe.',
    'estado.required' => 'El estado es obligatorio.',
    'estado.in' => 'El estado debe ser Habilitado o Deshabilitado.'  // Antes: Activo o Inactivo
]);
```

**Cambios en el método `update()` (líneas ~171-184):**
```php
$validator = Validator::make($request->all(), [
    'nombre' => 'required|string|max:255|unique:grupos,nombre,' . $id,
    'congregacion_id' => 'required|integer|exists:congregaciones,id',
    'estado' => 'required|integer|in:0,1'
], [
    'nombre.required' => 'El nombre es obligatorio.',
    'nombre.unique' => 'Ya existe un grupo con este nombre.',
    'nombre.max' => 'El nombre no puede tener más de 255 caracteres.',
    'congregacion_id.required' => 'La congregación es obligatoria.',
    'congregacion_id.exists' => 'La congregación seleccionada no existe.',
    'estado.required' => 'El estado es obligatorio.',
    'estado.in' => 'El estado debe ser Habilitado o Deshabilitado.'  // Antes: Activo o Inactivo
]);
```

**Nota:** El método `destroy()` NO fue eliminado del controlador, solo se quitó la interfaz de usuario (botón y evento JavaScript). El endpoint sigue existente pero no es accesible desde la UI.

## Razones de los Cambios

### 1. Eliminación del Botón "Eliminar"

- **Prevención de pérdida de datos:** Los grupos pueden tener usuarios asignados, y eliminarlos podría causar problemas de integridad referencial.
- **Mejor práctica:** En lugar de eliminar, se usa el campo `estado` para deshabilitar grupos.
- **Historial:** Se mantiene el historial de grupos creados, incluso si ya no se usan.

### 2. Cambio de Nomenclatura

- **Claridad semántica:** "Habilitado/Deshabilitado" es más claro que "Activo/Inactivo" en este contexto.
- **Consistencia:** Alinea la terminología con otros módulos del sistema.
- **Mejor UX:** Los usuarios entienden mejor que un grupo está "deshabilitado" temporalmente vs "inactivo" (que puede sonar permanente).

## Impacto en la Base de Datos

**NO hay cambios en la estructura de la base de datos:**
- El campo `grupos.estado` sigue siendo `TINYINT(1)`
- Los valores siguen siendo `1` (habilitado) y `0` (deshabilitado)
- Solo cambió la presentación en la interfaz de usuario

## Interfaz de Usuario Actual

### Columna Acciones - Antes:
```
[👁️ Ver] [✏️ Editar] [🗑️ Eliminar]
```

### Columna Acciones - Ahora:
```
[👁️ Ver] [✏️ Editar]
```

### Columna Estado - Antes:
```
🟢 Activo
🔴 Inactivo
```

### Columna Estado - Ahora:
```
🟢 Habilitado
🔴 Deshabilitado
```

## Funcionalidades Disponibles

| Acción | Admin | Coordinator | Secretary | Organizer | Subcoordinator | Subsecretary | Suborganizer |
|--------|-------|-------------|-----------|-----------|----------------|--------------|--------------|
| **Ver grupos** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Crear grupo** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Editar grupo** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Eliminar grupo** | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Cambiar estado** | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

## Testing Recomendado

### Casos de Prueba:

1. **Ver lista de grupos:**
   - [ ] Verificar que la columna Estado muestre "Habilitado" o "Deshabilitado"
   - [ ] Verificar que el filtro de estado muestre "Habilitado" y "Deshabilitado"
   - [ ] Verificar que el botón "Eliminar" NO aparezca en ningún grupo

2. **Crear grupo:**
   - [ ] Verificar que el select de estado muestre "Habilitado" y "Deshabilitado"
   - [ ] Crear un grupo habilitado
   - [ ] Crear un grupo deshabilitado

3. **Editar grupo:**
   - [ ] Verificar que el select de estado muestre "Habilitado" y "Deshabilitado"
   - [ ] Cambiar estado de habilitado a deshabilitado
   - [ ] Cambiar estado de deshabilitado a habilitado

4. **Ver detalles de grupo:**
   - [ ] Verificar que el badge de estado muestre "Habilitado" o "Deshabilitado"

5. **Filtrar por estado:**
   - [ ] Filtrar por "Habilitado" - debe mostrar solo grupos con estado=1
   - [ ] Filtrar por "Deshabilitado" - debe mostrar solo grupos con estado=0
   - [ ] Filtrar por "Todos" - debe mostrar todos los grupos

6. **Validación de errores:**
   - [ ] Al ingresar un estado inválido, el mensaje debe decir "El estado debe ser Habilitado o Deshabilitado"

## Archivos NO Modificados

Los siguientes archivos **NO fueron modificados** (el endpoint `destroy` sigue existiendo pero no es accesible desde la UI):

- `routes/web.php` - La ruta `DELETE /grupos/{id}` sigue definida
- `app/Models/Grupo.php` - El modelo no requiere cambios
- Base de datos - No se modificó la estructura de la tabla `grupos`

## Consideraciones Futuras

Si en el futuro se necesita eliminar grupos:

1. **Opción 1:** Volver a habilitar el botón de eliminar en el JavaScript
2. **Opción 2:** Crear una pantalla especial de "Administración Avanzada" con permisos restringidos solo para Admin
3. **Opción 3:** Implementar "soft deletes" en Laravel para marcar grupos como eliminados sin borrarlos físicamente

## Conclusión

Los cambios implementados:
- ✅ Mejoran la claridad de la interfaz
- ✅ Previenen eliminaciones accidentales
- ✅ Mantienen la consistencia terminológica
- ✅ No afectan la funcionalidad existente
- ✅ Son reversibles si se necesita cambiar en el futuro
