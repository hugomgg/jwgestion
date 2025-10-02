# Sistema de Gestión de Congregaciones

Aplicación Laravel para la gestión de congregaciones religiosas, usuarios y programas.

## 📁 Documentación

Toda la documentación del proyecto está organizada en la carpeta [`md/`](md/):

- **[`md/README.md`](md/README.md)** - Documentación estándar de Laravel
- **[`md/DOCUMENTATION.md`](md/DOCUMENTATION.md)** - Guía de organización de la documentación
- **[`md/DEPLOYMENT.md`](md/DEPLOYMENT.md)** - Guía de despliegue en producción
- **[`md/PERMISOS.md`](md/PERMISOS.md)** - Matriz de permisos y roles
- **[`md/AUDITORIA.md`](md/AUDITORIA.md)** - Sistema de auditoría

## 🚀 Inicio Rápido

```bash
# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos
php artisan migrate --seed

# Construir assets
npm run build

# Iniciar servidor
php artisan serve
```

## 📋 Características

- Gestión de usuarios con roles y permisos
- Programación de reuniones y servicios
- Sistema de auditoría completo
- Emails en español
- Interfaz responsive

## 🔧 Configuración de Producción

Consulta [`md/DEPLOYMENT.md`](md/DEPLOYMENT.md) para instrucciones detalladas de despliegue.

## 📖 Más Información

Revisa la carpeta [`md/`](md/) para documentación completa sobre permisos, debugging, configuración de emails, etc.