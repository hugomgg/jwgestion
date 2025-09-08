# GitHub Copilot Instructions

## Project Overview
This is a **religious congregation management system** built with Laravel 12, featuring user management, program scheduling, and organizational hierarchy tracking. Uses SQLite as default database with role-based access control.

## Key Domain Models & Architecture

### Core Entities
- **User**: Central entity with extensive profile data (congregation, group, service roles, spiritual status)
- **Programa**: Meeting/service programs with speakers and songs
- **Congregacion/Grupo**: Organizational hierarchy
- **Asignacion**: Task/role assignments with temporal tracking
- **PartePrograma/ParteSeccion**: Program parts and sections

### Universal Auditing Pattern
**ALL models** use `Auditable` trait providing automatic audit fields:
```php
use App\Traits\Auditable;
// Auto-populated: creador_id, modificador_id, creado_por_timestamp, modificado_por_timestamp
```

## Authorization & Permissions

### Profile-Based Access (perfil field)
- **Perfil 1**: Admin - Full CRUD access
- **Perfil 2**: Supervisor - Read-only access  
- **Perfil 3**: Coordinator - Limited management
- **Perfil 4**: User - Basic access

### Key Middleware Chain
- `CheckUserStatusMiddleware`: Validates user estado (0=inactive, 1=active)
- `AdminMiddleware`: Enforces `$user->isAdmin()` method
- Gates defined in `AuthServiceProvider` for granular permissions

### Permission Pattern
Routes use Laravel Gates: `Route::middleware('can:can.view.users')`
Controllers check: `$user->isAdmin()`, `$user->canAccessPeopleManagement()`

## Development Workflows

### Database Setup
```bash
# Uses SQLite by default (database/database.sqlite)
php artisan migrate
php artisan db:seed
```

### Asset Building
```bash
npm run dev     # Development with Vite
npm run build   # Production build
```

### Custom PHP Path
Uses `php.bat` wrapper pointing to `D:\PROGRAMAS\php\php.exe` - adjust paths accordingly.

### Deployment
Run `./deploy.sh` for production deployment with cache optimization and maintenance mode.

## Code Conventions

### Controller Patterns
- Extensive use of DB joins over Eloquent relationships for performance
- Manual JOIN queries in controllers (see `UserController::index()`)
- PDF exports via `barryvdh/laravel-dompdf`

### Model Relationships
Reference foreign keys by ID, not objects:
```php
'perfil' => 1,           // ✓ Store ID
'congregacion' => 2,     // ✓ Store ID
```

### Validation & Error Handling
- Estado validation: active users only can login
- Permission redirects to `users.index` with error messages
- Uses Laravel's built-in validation with custom messages

## Key Files to Reference
- `app/Traits/Auditable.php`: Universal audit pattern
- `routes/web.php`: Complete permission structure (lines 14-197)
- `PERMISOS.md`: Detailed permission matrix
- `app/Http/Controllers/UserController.php`: Advanced querying patterns
- `config/database.php`: SQLite-first configuration

## Quick Commands
```bash
php artisan config:cache    # Cache config for production
php artisan route:cache     # Cache routes
composer dump-autoload     # Refresh autoloader
```
