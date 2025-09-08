#!/bin/bash

echo "🚀 Iniciando deployment a producción..."

# Activar modo mantenimiento
php artisan down --retry=60

# Actualizar código desde Git (si usas Git)
# git pull origin main

# Instalar/actualizar dependencias
echo "📦 Instalando dependencias..."
composer install --optimize-autoloader --no-dev
npm ci --production

# Limpiar cachés
echo "🧹 Limpiando cachés..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Compilar assets
echo "🎨 Compilando assets..."
npm run build

# Ejecutar migraciones
echo "🗄️ Ejecutando migraciones..."
php artisan migrate --force

# Generar cachés optimizadas
echo "⚡ Generando cachés..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimizar autoloader
composer dump-autoload --optimize

# Configurar permisos
echo "🔐 Configurando permisos..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Desactivar modo mantenimiento
php artisan up

echo "✅ Deployment completado exitosamente!"