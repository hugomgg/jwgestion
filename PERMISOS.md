# 🔐 Sistema de Permisos y Accesos

## Estado de Usuario

### **Estado 1 - Activo**
- ✅ **Puede iniciar sesión** en el sistema
- ✅ **Acceso completo** según su perfil
- ✅ **Sesión mantenida** mientras esté activo

### **Estado 0 - Inactivo**
- ❌ **No puede iniciar sesión**
- ❌ **Sesión cerrada automáticamente** si es desactivado
- 🔒 **Mensaje:** "Su cuenta está inactiva. Contacte al administrador"

## Tipos de Usuario

### **Perfil 1 - Administrador**
- ✅ **Acceso completo** a todos los módulos
- ✅ **Lectura, creación, edición y eliminación** en:
  - Gestión de Usuarios
  - Gestión de Perfiles  
  - Gestión de Asignaciones
- ✅ **Menú lateral de navegación** completo

### **Perfil 2 - Supervisor (Solo Lectura)**
- ✅ **Acceso de lectura** a todos los módulos
- ✅ **Solo visualización** de:
  - Listado de Usuarios
  - Listado de Perfiles
  - Listado de Asignaciones
- ❌ **No puede** crear, editar o eliminar
- ✅ **Menú lateral de navegación** (mismo que administrador)
- ✅ **Panel de Opciones** con acceso directo a Gestión de Usuarios
- 🔒 **Botones de acción ocultos** (Agregar, Editar, Eliminar)

### **Otros Perfiles**
- ❌ **Sin acceso** al menú de administración
- ✅ **Solo acceso** al dashboard básico

## Validación de Estado

### **En el Login (`LoginController`)**
- **Credenciales:** Se agrega `'estado' => 1` a las credenciales
- **Verificación:** Solo usuarios con estado = 1 pueden autenticarse
- **Error:** "Las credenciales proporcionadas son incorrectas o la cuenta está inactiva"

### **En Tiempo Real (`CheckUserStatusMiddleware`)**
- **Verificación continua:** En cada request se verifica el estado
- **Logout automático:** Si el usuario es desactivado, se cierra la sesión
- **Redirección:** A login con mensaje explicativo

## Middlewares Implementados

### **AdminMiddleware (`admin`)**
- **Propósito:** Operaciones de escritura (crear, editar, eliminar)
- **Acceso:** Solo perfil = 1 (Administradores)
- **Rutas protegidas:** POST, PUT, DELETE

### **CanAccessAdminMenuMiddleware (`can.access.admin.menu`)**
- **Propósito:** Operaciones de lectura y acceso al menú
- **Acceso:** Perfil = 1 y perfil = 2
- **Rutas protegidas:** GET (visualización)

### **CheckUserStatusMiddleware (`check.user.status`)**
- **Propósito:** Verificar estado del usuario en tiempo real
- **Acceso:** Aplicado globalmente a rutas web
- **Función:** Logout automático si usuario es desactivado

## Métodos del Modelo User

```php
// Verificar permisos
$user->isAdmin()              // true si perfil = 1
$user->isStudent()            // true si perfil = 2  
$user->isReadOnly()           // true si perfil = 2
$user->canAccessAdminMenu()   // true si perfil = 1 o 2
$user->canModify()            // true si perfil = 1
```

## Estructura de Rutas

### **Rutas de Lectura** (`can.access.admin.menu`)
```php
GET /usuarios      -> users.index
GET /perfiles      -> perfiles.index  
GET /asignaciones  -> asignaciones.index
```

### **Rutas de Escritura** (`admin`)
```php
POST   /usuarios       -> users.store
PUT    /usuarios/{id}  -> users.update
DELETE /usuarios/{id}  -> users.destroy
// Similar para perfiles y asignaciones
```

## Implementación en Vistas

### **Botones Condicionales**
```blade
@if(Auth::user()->canModify())
    <button class="btn btn-primary">Agregar</button>
    <button class="btn btn-warning">Editar</button>
    <button class="btn btn-danger">Eliminar</button>
@else
    <span class="text-muted small">Solo lectura</span>
@endif
```

### **Menú Lateral Condicional**
```blade
@if(Auth::user()->canAccessAdminMenu())
    <!-- Menú lateral completo -->
@else
    <!-- Sin menú lateral -->
@endif
```

## Seguridad

### **Nivel de Vista**
- Botones ocultos para usuarios sin permisos
- Indicador "Solo lectura" para perfil = 2

### **Nivel de Middleware**
- Verificación de métodos HTTP
- Redirección automática si no tiene permisos
- Mensajes de error explicativos

### **Nivel de Ruta**
- Separación clara entre lectura y escritura
- Protección granular por operación

## Flujo de Acceso

1. **Usuario se autentica**
2. **Middleware verifica perfil:**
   - Perfil 1: Acceso completo
   - Perfil 2: Solo lectura
   - Otros: Sin acceso
3. **Vista adapta interfaz:**
   - Muestra/oculta botones según permisos
   - Renderiza menú según acceso
4. **Operaciones protegidas:**
   - GET: Permitido para perfil 1 y 2
   - POST/PUT/DELETE: Solo perfil 1

## Mensajes de Error

- **Sin autenticación:** "Debe iniciar sesión para acceder"
- **Sin permisos de acceso:** "No tiene permisos para acceder a esta sección"
- **Sin permisos de escritura:** "No tiene permisos para realizar esta acción. Solo puede ver la información"

## Testing de Permisos

### **Verificar Acceso de Lectura (Perfil 2 - Supervisor)**
```bash
# Crear usuario con perfil = 2 (Supervisor)
# Verificar acceso a:
- GET /usuarios ✅ (desde menú lateral y panel de opciones)
- GET /perfiles ✅
- GET /asignaciones ✅
- POST /usuarios ❌
- Botón "Acceder" en Panel de Opciones ✅
```

### **Verificar Acceso Completo (Perfil 1)**
```bash
# Crear usuario con perfil = 1
# Verificar acceso a todas las rutas ✅
```

### **Verificar Sin Acceso (Otros Perfiles)**
```bash
# Crear usuario con perfil != 1,2
# Verificar que no puede acceder a ninguna ruta ❌
```

### **Verificar Validación de Estado**
```bash
# Crear usuario con estado = 0 (Inactivo)
# Intentar login ❌ - "Las credenciales proporcionadas son incorrectas o la cuenta está inactiva"

# Usuario logueado + cambiar estado a 0
# Siguiente request → Logout automático ✅
# Redirección a login con mensaje ✅
```