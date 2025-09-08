# Campo Privilegio en Perfiles - Documentación

## 📋 Resumen

Se ha añadido el campo `privilegio` a la tabla `perfiles` para almacenar información específica sobre el privilegio espiritual o cargo del perfil del usuario.

## 🗃️ Estructura de Base de Datos

### Campo Añadido

```sql
-- Campo privilegio añadido a la tabla perfiles
privilegio VARCHAR(255) NOT NULL DEFAULT 'Anciano'
```

### Migración Aplicada

- ✅ [`add_privilegio_to_perfiles_table`](database/migrations/2025_05_28_023009_add_privilegio_to_perfiles_table.php)

## 🔧 Implementación Técnica

### 1. Modelo Perfil Actualizado

El modelo [`Perfil`](app/Models/Perfil.php) ahora incluye:

```php
protected $fillable = [
    'nombre',
    'privilegio',      // ← Nuevo campo
    'descripcion',
    'estado',
    'creador_id',
    'modificador_id',
    'creado_por_timestamp',
    'modificado_por_timestamp',
];

protected $attributes = [
    'privilegio' => 'Anciano', // ← Valor por defecto
    'estado' => 1,
    'creador_id' => 1,
    'modificador_id' => 1
];
```

### 2. Modelo User Actualizado

Se añadieron métodos para acceder al privilegio:

```php
/**
 * Obtener el privilegio del perfil del usuario.
 */
public function getPerfilPrivilegioAttribute()
{
    if ($this->relationLoaded('perfil') && $this->getRelation('perfil')) {
        return $this->getRelation('perfil')->privilegio;
    }
    
    $perfil = $this->perfil()->first();
    return $perfil ? $perfil->privilegio : 'Sin privilegio';
}

/**
 * Get the user's role name (alias for perfil privilegio)
 */
public function getRoleNameAttribute()
{
    return $this->perfil_privilegio; // ← Ahora retorna privilegio en lugar de nombre
}
```

### 3. Controlador Actualizado

El [`PerfilController`](app/Http/Controllers/PerfilController.php) ahora valida y maneja el campo privilegio:

```php
$validator = Validator::make($request->all(), [
    'nombre' => 'required|string|max:255|unique:perfiles',
    'privilegio' => 'required|string|max:255', // ← Nueva validación
    'descripcion' => 'required|string|max:500',
    'estado' => 'required|integer|in:0,1'
], [
    'privilegio.required' => 'El privilegio es obligatorio.',
    'privilegio.max' => 'El privilegio no puede exceder 255 caracteres.',
    // ... otros mensajes
]);
```

### 4. Vista Actualizada

La vista [`perfiles/index.blade.php`](resources/views/perfiles/index.blade.php) ahora incluye:

**Tabla actualizada:**
- Nueva columna "Privilegio" que muestra el privilegio como badge
- Campos de privilegio en modales de crear y editar
- JavaScript actualizado para manejar el nuevo campo

**Modales actualizados:**
- Campo de entrada para privilegio con valor por defecto "Anciano"
- Validación del lado cliente
- Actualización de formularios de creación y edición

## 🚀 Datos Actualizados

### Perfiles Existentes Actualizados

El seeder [`UpdatePerfilesPrivilegioSeeder`](database/seeders/UpdatePerfilesPrivilegioSeeder.php) actualizó:

- **Administrador** → Privilegio: "Anciano"
- **Estudiante** → Privilegio: "Publicador"  
- **Supervisor** → Privilegio: "Siervo Ministerial"

### Nuevos Perfiles Creados

- **Siervo Ministerial** → Privilegio: "Siervo Ministerial"
- **Precursor** → Privilegio: "Precursor Regular"
- **Betelita** → Privilegio: "Miembro de Betel"

## 💻 Uso en el Sistema

### Mostrar Privilegio del Usuario

En el navbar, ahora se muestra el privilegio del usuario en lugar del nombre del perfil:

```blade
{{ Auth::user()->name }}
<small class="text-muted">
    ({{ Auth::user()->role_name }}) <!-- Ahora muestra el privilegio -->
</small>
```

### Crear Nuevo Perfil

```php
$perfil = Perfil::create([
    'nombre' => 'Nuevo Perfil',
    'privilegio' => 'Precursor Especial', // ← Campo obligatorio
    'descripcion' => 'Descripción del perfil',
    'estado' => 1
]);
```

### Consultar por Privilegio

```php
// Obtener todos los perfiles con privilegio específico
$ancianos = Perfil::where('privilegio', 'Anciano')->get();

// Obtener usuarios con privilegio específico
$publicadores = User::whereHas('perfil', function($query) {
    $query->where('privilegio', 'Publicador');
})->get();
```

## 🎯 Ejemplos de Privilegios

### Privilegios Típicos en Congregaciones

- **Anciano**
- **Siervo Ministerial**
- **Precursor Regular**
- **Precursor Especial**
- **Misionero**
- **Superintendente de Circuito**
- **Superintendente de Distrito**
- **Miembro de Betel**
- **Publicador**
- **Estudiante**

### Uso en Modales

Los modales ahora incluyen un campo de texto para privilegio con:
- Valor por defecto: "Anciano"
- Validación obligatoria
- Sugerencias de ejemplo en texto de ayuda
- Máximo 255 caracteres

## ✅ Verificación de Funcionalidad

La funcionalidad se ha verificado con:

- ✅ Migración aplicada exitosamente
- ✅ Seeder ejecutado correctamente  
- ✅ Modelos actualizados funcionando
- ✅ Controladores validando correctamente
- ✅ Vistas mostrando privilegios
- ✅ Sistema de auditoría funcionando con nuevo campo
- ✅ Pruebas automatizadas pasando

## 🔄 Migración de Datos

Todos los perfiles existentes fueron actualizados automáticamente con valores de privilegio apropiados, manteniendo la integridad de los datos y la funcionalidad del sistema de auditoría.