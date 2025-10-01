# Actualización AJAX - Gestión de Usuarios

## Resumen de Cambios

Se ha modificado el sistema de gestión de usuarios para que la creación y edición se realicen mediante AJAX sin recargar la página.

## Archivos Modificados

### 1. `app/Http/Controllers/UserController.php`

#### Método `store()` (línea ~350)
- **Cambio**: Ahora devuelve datos completos del usuario recién creado con todas sus relaciones
- **Respuesta incluye**:
  - Datos básicos del usuario (id, name, estado)
  - Nombres de relaciones (perfil, congregación, grupo, nombramiento, servicio, estado espiritual)
  - Asignaciones del usuario

```php
return response()->json([
    'success' => true,
    'message' => 'Usuario creado exitosamente.',
    'user' => $userWithRelations,
    'asignaciones' => $asignacionesData
]);
```

#### Método `update()` (línea ~560)
- **Cambio**: Similar al método `store()`, devuelve datos completos del usuario actualizado
- **Respuesta incluye**: Los mismos datos que el método `store()`

### 2. `public/js/users-index.js`

#### Nueva función `buildUserRowHtml(user, asignaciones)` (línea ~445)
- **Propósito**: Construye dinámicamente el HTML de una fila de la tabla
- **Características**:
  - Respeta los permisos del usuario actual
  - Maneja diferentes configuraciones de columnas según el perfil
  - Genera badges para estado, nombramiento, servicio, etc.
  - Agrega botones de acciones según permisos

#### Método `$('#addUserForm').on('submit')` (línea ~520)
- **Cambio**: Eliminado `window.location.reload()`
- **Nuevo comportamiento**:
  - Crea una nueva fila HTML con los datos del usuario
  - Agrega la fila a DataTables sin recargar
  - Reinicializa tooltips de Bootstrap

#### Método `$('#editUserForm').on('submit')` (línea ~670)
- **Cambio**: Eliminado `window.location.reload()`
- **Nuevo comportamiento**:
  - Encuentra la fila existente por `data-user-id`
  - Actualiza el contenido HTML de la fila existente (no la elimina)
  - Invalida la caché de DataTables para esa fila
  - Redibuja la tabla manteniendo filtros y posición
  - Reinicializa tooltips de Bootstrap

### 3. `resources/views/users/index.blade.php`

#### Configuración JavaScript (línea ~1054)
- **Agregado**: Nueva propiedad `canModify` en `window.usersIndexConfig`
- **Propósito**: Determinar si el usuario actual puede editar usuarios
- **Valor**: `Auth::user()->canModify() && !Auth::user()->isSubsecretary() && !Auth::user()->isSuborganizer()`

## Mejoras Implementadas

### 1. **Sin Recarga de Página**
- ✅ Creación de usuarios sin recarga
- ✅ Edición de usuarios sin recarga
- ✅ Los filtros aplicados se mantienen
- ✅ La posición en la tabla se mantiene

### 2. **Actualización Dinámica**
- ✅ Las nuevas filas reflejan los datos exactos del servidor
- ✅ Los badges y estilos se aplican correctamente
- ✅ Los botones de acción se generan según permisos

### 3. **Experiencia de Usuario**
- ✅ Feedback inmediato (alertas de éxito/error)
- ✅ Spinners durante la operación
- ✅ Validación de errores en tiempo real
- ✅ Tooltips funcionando después de agregar/editar

## Pruebas Recomendadas

### Caso 1: Crear Usuario
1. Abrir modal "Agregar Usuario"
2. Llenar todos los campos requeridos
3. Hacer clic en "Guardar Usuario"
4. **Verificar**: 
   - Modal se cierra
   - Aparece alerta de éxito
   - Nueva fila aparece en la tabla
   - No hay recarga de página

### Caso 2: Editar Usuario
1. Hacer clic en botón "Editar" de un usuario
2. Modificar campos (nombre, estado, etc.)
3. Hacer clic en "Actualizar Usuario"
4. **Verificar**:
   - Modal se cierra
   - Aparece alerta de éxito
   - Fila se actualiza con nuevos datos
   - No hay recarga de página

### Caso 3: Validación de Errores
1. Intentar crear usuario con email duplicado
2. **Verificar**:
   - Se muestran errores de validación
   - Los campos con error se resaltan
   - No hay recarga de página
   - Modal permanece abierto

### Caso 4: Filtros Activos
1. Aplicar filtro de estado = "Habilitado"
2. Crear o editar un usuario con estado = "Deshabilitado"
3. **Verificar**:
   - El usuario se crea/actualiza correctamente
   - La tabla mantiene el filtro activo
   - El nuevo usuario no aparece si no cumple el filtro

### Caso 5: Permisos
1. Probar con diferentes perfiles (Admin, Coordinador, Secretario)
2. **Verificar**:
   - Los botones de edición aparecen según permisos
   - Las columnas se muestran correctamente según el perfil
   - Las asignaciones aparecen solo para perfiles autorizados

## Compatibilidad

- ✅ **Perfiles soportados**: Admin, Supervisor, Coordinador, Secretario, Organizador y sus sub-roles
- ✅ **Navegadores**: Todos los navegadores modernos con soporte para ES6
- ✅ **DataTables**: Compatible con la versión actual
- ✅ **Bootstrap 5**: Tooltips y modales funcionando correctamente

## Notas Técnicas

### DataTables API
- Se usa `table.row.add()` para agregar nuevas filas
- Se usa `table.row().remove()` para eliminar filas existentes
- El parámetro `false` en `draw()` evita volver a la primera página

### Manejo de Asignaciones
- Las asignaciones se envían como array en el formulario
- Se sincronizan con `sync()` en el backend
- Se devuelven como colección en la respuesta JSON

### Campos de Auditoría
- Los campos `creador_id`, `modificador_id`, etc. se manejan automáticamente por el trait `Auditable`
- No es necesario enviarlos desde el frontend

## Troubleshooting

### Problema: La fila desaparece después de editar
- **Causa**: El método anterior eliminaba la fila y trataba de agregar una nueva, causando conflictos con DataTables
- **Solución implementada**: Ahora actualizamos el contenido HTML de la fila existente sin eliminarla
- **Verificar**: Que `rowElement.html(newRow.html())` esté siendo ejecutado correctamente

### Problema: La columna "Congregación" aparece cuando no debería (después de actualizar)
- **Causa**: La lógica para determinar si mostrar la columna estaba incorrecta
- **Solución implementada**: 
  - `isLimitedUser = true` → Usuario NO es Coordinador/Secretario/Organizador → ES Admin/Supervisor → MOSTRAR congregación
  - `isLimitedUser = false` → Usuario ES Coordinador/Secretario/Organizador → NO mostrar congregación
- **Código**: `const showCongregacion = isLimitedUser;`

### Problema: La fila no se actualiza
- **Solución**: Verificar que el atributo `data-user-id` esté presente en el `<tr>`
- **Verificar**: Consola del navegador para errores JavaScript

### Problema: Los tooltips no funcionan
- **Solución**: Se reinicializan automáticamente después de agregar/editar
- **Verificar**: Que Bootstrap esté cargado correctamente

### Problema: Filtros no se aplican a nuevas filas
- **Solución**: DataTables aplica filtros automáticamente
- **Nota**: Si el usuario creado no cumple el filtro activo, no aparecerá (comportamiento esperado)

## Próximas Mejoras Sugeridas

1. **Animaciones**: Agregar animaciones al insertar/actualizar filas
2. **Confirmación**: Mensaje de confirmación antes de cerrar modal con cambios sin guardar
3. **Auto-refresh**: Opción para refrescar datos cada X minutos
4. **Búsqueda instantánea**: Mejorar la búsqueda con debounce
5. **Historial**: Mostrar historial de cambios del usuario

---

**Fecha de implementación**: 1 de octubre de 2025
**Desarrollado para**: Sistema de Gestión de Congregación
**Tecnologías**: Laravel 12, jQuery, DataTables, Bootstrap 5
