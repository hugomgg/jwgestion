# Fix: Filtro de Estado en Grupos No Funcionaba

## Problema Identificado

El filtro de estado en la vista de grupos (`grupos/index.blade.php`) no funcionaba correctamente después de cambiar la nomenclatura de estados de "Activo/Inactivo" a "Habilitado/Deshabilitado".

### Causa Raíz

El código JavaScript en `public/js/grupos-index.js` (línea ~100) estaba buscando los textos antiguos:
- "Activo" (estado 1)
- "Inactivo" (estado 0)

Pero los badges en la tabla ahora muestran:
- "Habilitado" (estado 1)
- "Deshabilitado" (estado 0)

### Síntomas del Problema

Cuando el usuario seleccionaba un estado en el filtro:
- ❌ Al seleccionar "Habilitado" → No mostraba ningún grupo
- ❌ Al seleccionar "Deshabilitado" → No mostraba ningún grupo
- ✅ Al seleccionar "Todos" → Mostraba todos los grupos correctamente

## Solución Implementada

### Archivo: public/js/grupos-index.js

**Línea ~100 - ANTES:**
```javascript
// Mapear valores numéricos a textos para la búsqueda
const textoEstado = selectedEstado === '1' ? 'Activo' : 'Inactivo';
```

**Línea ~100 - AHORA:**
```javascript
// Mapear valores numéricos a textos para la búsqueda
const textoEstado = selectedEstado === '1' ? 'Habilitado' : 'Deshabilitado';
```

### Cómo Funciona el Filtro

El filtro de DataTables funciona mediante la función `$.fn.dataTable.ext.search`:

1. **Usuario selecciona un estado** en el dropdown:
   ```html
   <select id="estadoFilter">
       <option value="">Todos</option>
       <option value="1">Habilitado</option>
       <option value="0">Deshabilitado</option>
   </select>
   ```

2. **JavaScript mapea el valor** al texto que aparece en la tabla:
   ```javascript
   const textoEstado = selectedEstado === '1' ? 'Habilitado' : 'Deshabilitado';
   ```

3. **Se crea una función de filtro** que busca ese texto en la columna de estado:
   ```javascript
   currentEstadoFilter = function(settings, data, dataIndex) {
       const estadoColumn = data[3]; // Columna 3 = Estado
       return estadoColumn.indexOf(textoEstado) !== -1;
   };
   ```

4. **La tabla se redibuja** mostrando solo las filas que coinciden.

### Estructura de Datos en DataTable

La columna de estado (índice 3) contiene HTML:
```html
<!-- Para estado = 1 -->
<span class="badge bg-success">Habilitado</span>

<!-- Para estado = 0 -->
<span class="badge bg-danger">Deshabilitado</span>
```

El filtro busca dentro de este HTML la palabra "Habilitado" o "Deshabilitado".

## Código Completo de la Función de Filtro

```javascript
$('#estadoFilter').on('change', function() {
    const selectedEstado = $(this).val();
    
    // Limpiar filtro anterior si existe
    if (currentEstadoFilter !== null) {
        $.fn.dataTable.ext.search.splice($.fn.dataTable.ext.search.indexOf(currentEstadoFilter), 1);
    }
    
    if (selectedEstado === '') {
        // Mostrar todos
        currentEstadoFilter = null;
        table.draw();
    } else {
        // Filtrar por estado seleccionado
        const textoEstado = selectedEstado === '1' ? 'Habilitado' : 'Deshabilitado';
        
        currentEstadoFilter = function(settings, data, dataIndex) {
            if (settings.nTable !== table.table().node()) {
                return true;
            }
            const estadoColumn = data[3]; // Columna 3 es el estado
            return estadoColumn.indexOf(textoEstado) !== -1;
        };
        
        $.fn.dataTable.ext.search.push(currentEstadoFilter);
        table.draw();
    }
});
```

## Relación con Cambios Previos

Este problema surgió después del cambio documentado en `md/GRUPOS_CAMBIOS_ESTADO_ELIMINAR.md`, donde se modificó:

| Cambio | Archivo | Línea |
|--------|---------|-------|
| Vista - Filtro | `grupos/index.blade.php` | ~21-22 |
| Vista - Modal Agregar | `grupos/index.blade.php` | ~97-98 |
| Vista - Modal Editar | `grupos/index.blade.php` | ~149-150 |
| JS - Renderizado DataTable | `grupos-index.js` | ~30-32 |
| JS - Modal Ver | `grupos-index.js` | ~210 |
| **JS - Filtro Estado** | `grupos-index.js` | **~100** ← NO SE ACTUALIZÓ |
| Controlador | `GrupoController.php` | ~88, ~183 |

El filtro de estado era el único lugar que faltaba actualizar.

## Testing

### Caso de Prueba 1: Filtrar por "Habilitado"
1. Ir a la vista de Grupos
2. En el filtro "Estado", seleccionar "Habilitado"
3. **Resultado esperado:** Solo mostrar grupos con estado = 1
4. **Verificación:** Badge verde "Habilitado" visible

### Caso de Prueba 2: Filtrar por "Deshabilitado"
1. Ir a la vista de Grupos
2. En el filtro "Estado", seleccionar "Deshabilitado"
3. **Resultado esperado:** Solo mostrar grupos con estado = 0
4. **Verificación:** Badge rojo "Deshabilitado" visible

### Caso de Prueba 3: Mostrar Todos
1. Ir a la vista de Grupos
2. En el filtro "Estado", seleccionar "Todos"
3. **Resultado esperado:** Mostrar todos los grupos
4. **Verificación:** Mezcla de badges verdes y rojos

### Caso de Prueba 4: Cambiar de filtro
1. Filtrar por "Habilitado"
2. Cambiar a "Deshabilitado"
3. Cambiar a "Todos"
4. **Resultado esperado:** El filtro se actualiza correctamente en cada cambio
5. **Verificación:** La tabla se redibuja cada vez

## Archivos Modificados

| Archivo | Línea | Cambio |
|---------|-------|--------|
| `public/js/grupos-index.js` | ~100 | `'Activo'` → `'Habilitado'` |
| `public/js/grupos-index.js` | ~100 | `'Inactivo'` → `'Deshabilitado'` |

## Lecciones Aprendidas

1. **Consistencia:** Al cambiar textos en la UI, verificar todos los lugares donde se usan esos textos
2. **Búsqueda de texto:** Los filtros que buscan texto deben coincidir exactamente con el texto renderizado
3. **Testing completo:** Probar todas las funcionalidades después de cambios de nomenclatura

## Prevención Futura

Para evitar este tipo de problemas en el futuro:

### Opción 1: Usar Valores Numéricos
En lugar de buscar texto, filtrar por el valor numérico:
```javascript
currentEstadoFilter = function(settings, data, dataIndex) {
    const row = table.row(dataIndex).data();
    return row.estado == selectedEstado;
};
```

### Opción 2: Constantes Centralizadas
Definir textos en un solo lugar:
```javascript
const ESTADOS = {
    HABILITADO: { valor: 1, texto: 'Habilitado' },
    DESHABILITADO: { valor: 0, texto: 'Deshabilitado' }
};

const textoEstado = selectedEstado === '1' ? ESTADOS.HABILITADO.texto : ESTADOS.DESHABILITADO.texto;
```

### Opción 3: Data Attributes
Usar atributos data en el HTML:
```javascript
render: function(data, type, row) {
    if (data == 1) {
        return '<span class="badge bg-success" data-estado="1">Habilitado</span>';
    } else {
        return '<span class="badge bg-danger" data-estado="0">Deshabilitado</span>';
    }
}
```

## Conclusión

El problema estaba en una línea de código que no se actualizó cuando se cambió la nomenclatura de estados. La solución fue simple: actualizar los textos de búsqueda de "Activo/Inactivo" a "Habilitado/Deshabilitado".

**Estado del Fix:**
- ✅ Problema identificado
- ✅ Causa raíz determinada
- ✅ Solución implementada
- ✅ Sin errores de sintaxis
- ⏳ Pendiente testing funcional
