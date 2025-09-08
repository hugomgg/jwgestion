# Sistema de Auditoría - Documentación

## 📋 Resumen

Se ha implementado un sistema completo de auditoría para las tablas `users`, `perfiles` y `asignaciones` que registra automáticamente:

- **Quién creó** cada registro (ID de usuario + timestamp)
- **Quién modificó** cada registro por última vez (ID de usuario + timestamp)
- **Cuándo** se realizaron estas acciones

## 🗃️ Estructura de Base de Datos

### Campos Añadidos a Cada Tabla

Cada tabla ahora incluye los siguientes campos de auditoría:

```sql
-- Campos de auditoría añadidos
creador_id BIGINT UNSIGNED DEFAULT 1           -- ID del usuario que creó el registro
modificador_id BIGINT UNSIGNED DEFAULT 1       -- ID del usuario que modificó el registro
creado_por_timestamp TIMESTAMP NULL            -- Fecha/hora de creación por usuario
modificado_por_timestamp TIMESTAMP NULL        -- Fecha/hora de última modificación
```

### Índices Creados

Para optimizar consultas de auditoría:

```sql
-- Índices para mejorar rendimiento
idx_[tabla]_creador                -- Índice en creador_id
idx_[tabla]_modificador            -- Índice en modificador_id
idx_[tabla]_creado_timestamp       -- Índice en creado_por_timestamp
idx_[tabla]_modificado_timestamp   -- Índice en modificado_por_timestamp
```

### Claves Foráneas

Para garantizar integridad referencial:

```sql
-- Claves foráneas hacia tabla users
fk_[tabla]_creador     -> users.id
fk_[tabla]_modificador -> users.id
```

## 🔧 Implementación Técnica

### 1. Trait Auditable

Se creó un trait `App\Traits\Auditable` que maneja automáticamente:

- **Eventos de creación**: Establece `creador_id`, `modificador_id` y timestamps
- **Eventos de actualización**: Actualiza `modificador_id` y `modificado_por_timestamp`
- **Relaciones**: Métodos `creador()` y `modificador()` para acceder a los usuarios
- **Scopes**: Métodos para filtrar por creador, modificador y fechas
- **Información formateada**: Método `getAuditInfo()` para obtener datos legibles

### 2. Modelos Actualizados

Todos los modelos (`User`, `Perfil`, `Asignacion`) ahora incluyen:

```php
use App\Traits\Auditable;

class Model extends BaseModel
{
    use Auditable;
    
    protected $fillable = [
        // campos originales...
        'creador_id',
        'modificador_id',
        'creado_por_timestamp',
        'modificado_por_timestamp',
    ];
    
    protected $attributes = [
        'creador_id' => 1,
        'modificador_id' => 1,
    ];
    
    protected function casts(): array
    {
        return [
            'creado_por_timestamp' => 'datetime',
            'modificado_por_timestamp' => 'datetime',
        ];
    }
}
```

### 3. Controladores Actualizados

Los controladores ahora incluyen información de auditoría en las respuestas:

```php
// En las respuestas JSON
return response()->json([
    'success' => true,
    'message' => 'Registro creado exitosamente.',
    'data' => $model->load(['creador', 'modificador']),
    'audit_info' => $model->getAuditInfo()
]);
```

## 🚀 Comandos Disponibles

### 1. Actualizar Datos Existentes

```bash
# Actualizar registros existentes con campos de auditoría
php artisan audit:update-existing-data

# Ver qué se actualizaría sin hacer cambios
php artisan audit:update-existing-data --dry-run
```

### 2. Probar Funcionalidad

```bash
# Ejecutar pruebas de la funcionalidad de auditoría
php artisan audit:test
```

## 💻 Uso en el Código

### Crear Registros

Los campos de auditoría se llenan automáticamente:

```php
$perfil = Perfil::create([
    'nombre' => 'Nuevo Perfil',
    'descripcion' => 'Descripción del perfil'
]);

// Automáticamente se establecen:
// - creador_id = Auth::id() ?? 1
// - modificador_id = Auth::id() ?? 1
// - creado_por_timestamp = now()
// - modificado_por_timestamp = now()
```

### Actualizar Registros

```php
$perfil->update([
    'descripcion' => 'Nueva descripción'
]);

// Automáticamente se actualizan:
// - modificador_id = Auth::id() ?? 1
// - modificado_por_timestamp = now()
```

### Consultar Información de Auditoría

```php
// Obtener relaciones de auditoría
$perfil = Perfil::with(['creador', 'modificador'])->find(1);

echo "Creado por: " . $perfil->creador->name;
echo "Modificado por: " . $perfil->modificador->name;

// Obtener información formateada
$auditInfo = $perfil->getAuditInfo();
echo $auditInfo['creado_por']['usuario_nombre'];
echo $auditInfo['creado_por']['fecha'];
```

### Usar Scopes para Filtrar

```php
// Registros creados por un usuario específico
$registros = Perfil::createdBy(1)->get();

// Registros modificados por un usuario específico
$registros = Perfil::modifiedBy(1)->get();

// Registros creados en un período
$registros = Perfil::createdBetween('2025-01-01', '2025-12-31')->get();

// Registros modificados en un período
$registros = Perfil::modifiedBetween('2025-05-01', '2025-05-31')->get();
```

## 🔍 Consultas de Ejemplo

### Auditoría por Usuario

```sql
-- Ver qué registros ha creado un usuario
SELECT 'users' as tabla, id, name as registro, creado_por_timestamp 
FROM users WHERE creador_id = 1
UNION ALL
SELECT 'perfiles' as tabla, id, nombre as registro, creado_por_timestamp 
FROM perfiles WHERE creador_id = 1
UNION ALL
SELECT 'asignaciones' as tabla, id, nombre as registro, creado_por_timestamp 
FROM asignaciones WHERE creador_id = 1;
```

### Actividad Reciente

```sql
-- Registros modificados en las últimas 24 horas
SELECT 'perfiles' as tabla, nombre, modificado_por_timestamp 
FROM perfiles 
WHERE modificado_por_timestamp >= datetime('now', '-1 day')
ORDER BY modificado_por_timestamp DESC;
```

## 🛡️ Consideraciones de Seguridad

1. **Integridad Referencial**: Las claves foráneas previenen referencias a usuarios inexistentes
2. **Valores por Defecto**: Si no hay usuario autenticado, se usa ID 1 por defecto
3. **Restricción de Eliminación**: Las claves foráneas usan `ON DELETE RESTRICT` para prevenir pérdida de información de auditoría

## ⚡ Optimización de Rendimiento

1. **Índices**: Se crearon índices en todos los campos de auditoría para consultas rápidas
2. **Eager Loading**: Se recomienda usar `with(['creador', 'modificador'])` para evitar N+1 queries
3. **Scopes Optimizados**: Los scopes están diseñados para usar los índices eficientemente

## 🔄 Migración de Datos Existentes

El comando `audit:update-existing-data` ya fue ejecutado y actualizó:
- ✅ 19 usuarios
- ✅ 3 perfiles  
- ✅ 6 asignaciones

Todos los registros existentes ahora tienen campos de auditoría completos usando sus timestamps `created_at` y `updated_at` originales.

## ✅ Verificación de Funcionalidad

El comando `audit:test` confirmó que el sistema funciona correctamente:
- ✅ Creación automática de campos de auditoría
- ✅ Actualización automática en modificaciones
- ✅ Relaciones funcionando correctamente
- ✅ Información de auditoría accesible y formateada