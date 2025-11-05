# Actualización de Códigos para Congregaciones

## Contexto

El sistema ahora usa `congregaciones.codigo` en lugar de `congregaciones.id` en las URLs públicas del formulario de informes.

## URLs Antes vs Después

### Antes
```
/informe/1  → Congregación con ID 1
/informe/2  → Congregación con ID 2
```

### Después
```
/informe/ADMIN  → Congregación con código "ADMIN"
/informe/LC     → Congregación con código "LC"
```

## Ventajas del Cambio

1. **URLs más amigables**: Códigos legibles vs números
2. **Seguridad**: No expone IDs internos de la base de datos
3. **Flexibilidad**: Los códigos pueden ser personalizados por congregación
4. **Memorabilidad**: Más fácil de recordar y compartir

## Asignación de Códigos

Los códigos deben ser asignados manualmente para cada congregación. Se recomienda:

### Códigos Sugeridos

| ID | Nombre | Código Sugerido | Descripción |
|----|--------|-----------------|-------------|
| 1 | Administración | `ADMIN` | Código administrativo |
| 2 | Lo Cañas | `LC` o `LOCANAS` | Iniciales del nombre |
| 3 | Los Copihues | `LCP` o `COPIHUES` | Iniciales del nombre |

### Comandos SQL para Actualizar

```sql
-- Asignar códigos a congregaciones existentes
UPDATE congregaciones SET codigo = 'ADMIN' WHERE id = 1;
UPDATE congregaciones SET codigo = 'LC' WHERE id = 2;
UPDATE congregaciones SET codigo = 'LCP' WHERE id = 3;
```

### Usando Artisan Tinker

```php
php artisan tinker
```

```php
// Actualizar congregación por ID
$cong = App\Models\Congregacion::find(1);
$cong->codigo = 'ADMIN';
$cong->save();

// O actualizar varias a la vez
App\Models\Congregacion::where('id', 1)->update(['codigo' => 'ADMIN']);
App\Models\Congregacion::where('id', 2)->update(['codigo' => 'LC']);
App\Models\Congregacion::where('id', 3)->update(['codigo' => 'LCP']);
```

### Usando SQL directo (SQLite)

```bash
# En PowerShell
php artisan tinker --execute="DB::table('congregaciones')->where('id', 1)->update(['codigo' => 'ADMIN']); DB::table('congregaciones')->where('id', 2)->update(['codigo' => 'LC']); DB::table('congregaciones')->where('id', 3)->update(['codigo' => 'LCP']); echo 'Códigos actualizados exitosamente';"
```

## Validaciones del Sistema

### Validación de Código Existente

El sistema valida que el código exista antes de mostrar el formulario:

```php
// En PublicInformeController::show()
$congregacion = Congregacion::where('codigo', $congregacion_codigo)->first();

if (!$congregacion) {
    return redirect('/')->with('error', 'Código de congregación no válido');
}
```

### Comportamiento en Errores

- **Código no existe**: Redirección a `/` con mensaje de error
- **Código NULL**: Igual que código no existe
- **Código duplicado**: El sistema tomará el primero encontrado (debe evitarse)

## Restricciones de Códigos

### Formato Recomendado

- **Longitud**: 2-20 caracteres
- **Caracteres**: Solo letras, números y guiones (`A-Z`, `0-9`, `-`)
- **Estilo**: Mayúsculas (más legible en URLs)
- **Sin espacios**: Usar guiones en su lugar (`LO-CANAS`)

### Ejemplos Válidos

✅ `ADMIN`  
✅ `LC`  
✅ `LCP`  
✅ `CONG-01`  
✅ `SANTIAGO-CENTRO`  

### Ejemplos NO Recomendados

❌ `admin` (minúsculas)  
❌ `Lo Cañas` (espacios y caracteres especiales)  
❌ `123` (solo números)  
❌ ` ` (vacío)  

## Migración Futura (Opcional)

Para hacer el código obligatorio en el futuro:

```php
// Migración para hacer código obligatorio
Schema::table('congregaciones', function (Blueprint $table) {
    $table->string('codigo')->unique()->change();
});
```

**Nota:** Antes de ejecutar esto, asegurarse de que todas las congregaciones tengan códigos asignados.

## Pruebas

### Verificar Códigos Actuales

```bash
php artisan tinker --execute="App\Models\Congregacion::select('id', 'nombre', 'codigo')->get()->each(function(\$c) { echo \$c->id . ' | ' . \$c->nombre . ' | ' . (\$c->codigo ?? 'SIN CODIGO') . PHP_EOL; });"
```

### Probar URLs

```bash
# Con código válido (debe mostrar formulario)
http://localhost/informe/ADMIN

# Con código inválido (debe redirigir a /)
http://localhost/informe/INVALIDO
```

## Actualización de Enlaces Existentes

Si existen enlaces o QR codes con las URLs antiguas (`/informe/{id}`), deben ser regenerados con los nuevos códigos:

### Antes
```
https://tu-dominio.com/informe/1
```

### Después
```
https://tu-dominio.com/informe/ADMIN
```

## Archivos Modificados

1. **Controlador**: `app/Http/Controllers/PublicInformeController.php`
   - Método `show()`: Busca por código en lugar de ID
   - Método `store()`: Valida código en lugar de ID

2. **Rutas**: `routes/web.php`
   - Cambio de parámetro `{congregacion_id}` → `{congregacion_codigo}`

3. **Vista**: `resources/views/public/informe.blade.php`
   - URLs generadas con código en lugar de ID

4. **Documentación**: 
   - `md/PUBLIC_INFORME_FORM.md`
   - `md/PUBLIC_INFORME_EXAMPLE.md`

## Compatibilidad con Versiones Anteriores

⚠️ **IMPORTANTE**: Este cambio NO es compatible con URLs antiguas. Los enlaces existentes con IDs dejarán de funcionar.

### Solución

Si necesitas mantener compatibilidad temporal, puedes agregar rutas alternativas:

```php
// En routes/web.php (TEMPORAL)
Route::get('/informe/id/{congregacion_id}', function($id) {
    $cong = Congregacion::find($id);
    if ($cong && $cong->codigo) {
        return redirect()->route('public.informe.show', $cong->codigo);
    }
    return redirect('/')->with('error', 'Congregación no encontrada');
})->name('public.informe.show.by.id');
```

## Mantenimiento

- **Códigos únicos**: Asegurarse de que cada congregación tenga un código único
- **Validación**: Implementar validación en el formulario de creación/edición de congregaciones
- **Documentación**: Mantener lista de códigos asignados actualizada
- **Comunicación**: Informar a usuarios sobre nuevas URLs
