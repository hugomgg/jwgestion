# Tabla Sexo - Documentación

## 📋 Resumen

Se ha creado la tabla `sexo` para gestionar los tipos de sexo en el sistema, con valores específicos para Hombre (ID: 1) y Mujer (ID: 2), incluyendo campos completos de auditoría.

## 🗃️ Estructura de Base de Datos

### Tabla Sexo

```sql
CREATE TABLE sexo (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion VARCHAR(500) NULL,
    estado TINYINT DEFAULT 1,
    
    -- Campos de auditoría
    creador_id BIGINT UNSIGNED DEFAULT 1,
    modificador_id BIGINT UNSIGNED DEFAULT 1,
    creado_por_timestamp TIMESTAMP NULL,
    modificado_por_timestamp TIMESTAMP NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Índices
    INDEX idx_sexo_creador (creador_id),
    INDEX idx_sexo_modificador (modificador_id),
    INDEX idx_sexo_creado_timestamp (creado_por_timestamp),
    INDEX idx_sexo_modificado_timestamp (modificado_por_timestamp),
    
    -- Claves foráneas
    FOREIGN KEY fk_sexo_creador (creador_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY fk_sexo_modificador (modificador_id) REFERENCES users(id) ON DELETE RESTRICT
);
```

### Datos Iniciales

| ID | Nombre | Descripción | Estado |
|----|--------|-------------|--------|
| 1  | Hombre | Sexo masculino | 1 (Activo) |
| 2  | Mujer  | Sexo femenino  | 1 (Activo) |

## 🔧 Implementación Técnica

### 1. Migración

- ✅ [`create_sexo_table`](database/migrations/2025_05_28_025237_create_sexo_table.php) - Tabla con campos de auditoría completos

### 2. Modelo

El modelo [`Sexo`](app/Models/Sexo.php) incluye:

```php
<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Sexo extends Model
{
    use Auditable;
    
    protected $table = 'sexo';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'creador_id',
        'modificador_id',
        'creado_por_timestamp',
        'modificado_por_timestamp',
    ];

    protected $attributes = [
        'estado' => 1,
        'creador_id' => 1,
        'modificador_id' => 1
    ];

    protected function casts(): array
    {
        return [
            'creado_por_timestamp' => 'datetime',
            'modificado_por_timestamp' => 'datetime',
        ];
    }

    // Scopes útiles
    public function scopeActive($query)
    {
        return $query->where('estado', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('estado', 0);
    }
}
```

### 3. Controlador

El [`SexoController`](app/Http/Controllers/SexoController.php) proporciona:

- **Validación completa** de campos
- **Manejo de errores** robusto
- **Información de auditoría** en respuestas
- **Operaciones CRUD** completas

```php
// Ejemplo de validación
$validator = Validator::make($request->all(), [
    'nombre' => 'required|string|max:255|unique:sexo',
    'descripcion' => 'nullable|string|max:500',
    'estado' => 'required|integer|in:0,1'
]);

// Ejemplo de respuesta con auditoría
return response()->json([
    'success' => true,
    'message' => 'Registro creado exitosamente.',
    'sexo' => $sexo->load(['creador', 'modificador']),
    'audit_info' => $sexo->getAuditInfo()
]);
```

### 4. Rutas

Rutas implementadas en [`web.php`](routes/web.php):

```php
// Lectura (requiere acceso al menú de administración)
Route::get('/sexo', [SexoController::class, 'index'])->name('sexo.index');

// Escritura (requiere permisos de administrador)
Route::post('/sexo', [SexoController::class, 'store'])->name('sexo.store');
Route::get('/sexo/{id}/edit', [SexoController::class, 'edit'])->name('sexo.edit');
Route::put('/sexo/{id}', [SexoController::class, 'update'])->name('sexo.update');
Route::delete('/sexo/{id}', [SexoController::class, 'destroy'])->name('sexo.destroy');
```

### 5. Seeder

El [`SexoSeeder`](database/seeders/SexoSeeder.php) pobla la tabla con:

```php
$sexos = [
    [
        'id' => 1,
        'nombre' => 'Hombre',
        'descripcion' => 'Sexo masculino',
        'estado' => 1
    ],
    [
        'id' => 2,
        'nombre' => 'Mujer',
        'descripcion' => 'Sexo femenino',
        'estado' => 1
    ]
];
```

## 🚀 Sistema de Auditoría Integrado

### Campos de Auditoría

Cada registro en la tabla sexo incluye automáticamente:

- **creador_id**: ID del usuario que creó el registro
- **modificador_id**: ID del usuario que modificó el registro por última vez
- **creado_por_timestamp**: Fecha y hora de creación con información del usuario
- **modificado_por_timestamp**: Fecha y hora de última modificación

### Funcionalidad Automática

- ✅ **Creación**: Se establecen automáticamente creador y modificador al crear
- ✅ **Actualización**: Se actualiza automáticamente el modificador al editar
- ✅ **Relaciones**: Acceso directo a usuarios creador y modificador
- ✅ **Información formateada**: Método `getAuditInfo()` disponible

### Integridad Referencial

- ✅ **Claves foráneas** hacia tabla users
- ✅ **Restricción de eliminación** para preservar auditoría
- ✅ **Índices optimizados** para consultas de auditoría

## 💻 Uso en el Sistema

### Consultar Registros

```php
// Obtener todos los sexos activos
$sexos = Sexo::active()->get();

// Obtener con información de auditoría
$sexos = Sexo::with(['creador', 'modificador'])->get();

// Filtrar por creador
$sexosPorUsuario = Sexo::createdBy(1)->get();
```

### Crear Nuevo Registro

```php
$sexo = Sexo::create([
    'nombre' => 'Otro',
    'descripcion' => 'Descripción del tipo',
    'estado' => 1
]);

// Los campos de auditoría se establecen automáticamente
echo $sexo->creador_id; // ID del usuario autenticado
echo $sexo->creado_por_timestamp; // Timestamp actual
```

### Obtener Información de Auditoría

```php
$sexo = Sexo::find(1);
$auditInfo = $sexo->getAuditInfo();

echo $auditInfo['creado_por']['usuario_nombre']; // Nombre del creador
echo $auditInfo['creado_por']['fecha']; // Fecha formateada
echo $auditInfo['modificado_por']['usuario_nombre']; // Nombre del modificador
echo $auditInfo['modificado_por']['fecha']; // Fecha formateada
```

## ✅ Verificación de Funcionalidad

### Pruebas Realizadas

El comando [`audit:test`](app/Console/Commands/TestAuditFunctionality.php) verificó:

- ✅ **Creación de registros** con auditoría automática
- ✅ **Actualización de registros** con auditoría automática
- ✅ **Relaciones de auditoría** funcionando correctamente
- ✅ **Información formateada** accesible
- ✅ **Integridad referencial** mantenida

### Resultados de Prueba

```
📝 Probando creación de registro de sexo...
   Sexo creado con ID: 3
   Creado por: 1 - Administrador
   Fecha de creación: 2025-05-28 02:55:21

📝 Probando actualización de registro de sexo...
   Sexo actualizado con ID: 3
   Modificado por: 1 - Administrador
   Fecha de modificación: 2025-05-28 02:55:21

📋 Sexo 'Prueba Sexo Auditoría':
   Creado por: Administrador el 28/05/2025 02:55:21
   Modificado por: Administrador el 28/05/2025 02:55:21
```

## 🔄 Migración Ejecutada

- ✅ **Tabla creada** exitosamente con todos los campos
- ✅ **Seeder ejecutado** con datos iniciales (1=Hombre, 2=Mujer)
- ✅ **Rutas registradas** y funcionando
- ✅ **Sistema de auditoría** completamente integrado

La tabla sexo está lista para uso en producción con un sistema completo de auditoría que registra automáticamente quién crea y modifica cada registro.