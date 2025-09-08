# 🚀 Guía de Deployment a Producción

## Comandos Principales de Deployment

### 1. Preparación Inicial
```bash
cd laravel-app
composer install --optimize-autoloader --no-dev
npm ci --production
```

### 2. Configuración de Entorno
```bash
cp .env.production.example .env
php artisan key:generate
```

### 3. Optimización
```bash
# Limpiar cachés
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Generar cachés optimizadas
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer dump-autoload --optimize
```

### 4. Assets Frontend
```bash
npm run build
```

### 5. Base de Datos
```bash
php artisan migrate --force
```

### 6. Script Automatizado
```bash
chmod +x deploy.sh
./deploy.sh
```

## 🔧 Configuraciones de Servidor Web

### Apache (.htaccess)
El archivo `public/.htaccess` ya está configurado. Asegúrate de que Apache tenga habilitado `mod_rewrite`.

### Nginx
Agregar esta configuración al virtual host:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tudominio.com;
    root /ruta/completa/al/proyecto/laravel-app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🛡️ Seguridad en Producción

### Variables de Entorno Críticas
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_KEY` - Genera una nueva para producción
- `DEBUGBAR_ENABLED=false` - Desactivar Laravel Debugbar en producción
- Cambiar base de datos de SQLite a MySQL/PostgreSQL

### Permisos de Archivos
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### SSL/HTTPS
- Configura certificado SSL
- Fuerza HTTPS en `.env`: `APP_URL=https://tudominio.com`

## 📊 Monitoreo y Mantenimiento

### Logs
```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar logs antiguos
php artisan log:clear
```

### Backup de Base de Datos
```bash
# MySQL
mysqldump -u usuario -p base_datos > backup_$(date +%Y%m%d).sql

# PostgreSQL
pg_dump -U usuario base_datos > backup_$(date +%Y%m%d).sql
```

### Monitoreo de Cola de Trabajos
```bash
# Supervisar cola
php artisan queue:work --daemon

# Con supervisor (recomendado)
# Configurar /etc/supervisor/conf.d/laravel-worker.conf
```

## 🔄 Actualizaciones

### Para actualizaciones futuras:
1. Poner en modo mantenimiento: `php artisan down`
2. Actualizar código: `git pull`
3. Ejecutar script de deployment: `./deploy.sh`
4. Verificar funcionamiento
5. Salir del modo mantenimiento: `php artisan up`

## ⚠️ Checklist Pre-Deployment

- [ ] Configurar `.env` para producción
- [ ] Cambiar base de datos a MySQL/PostgreSQL
- [ ] Configurar Redis para cache y sesiones
- [ ] Compilar assets con `npm run build`
- [ ] Ejecutar migraciones
- [ ] Configurar SSL/HTTPS
- [ ] Configurar servidor web (Apache/Nginx)
- [ ] Configurar backup automático
- [ ] Configurar monitoreo de logs
- [ ] Probar funcionalidad completa

## 🐛 Solución de Problemas Comunes

### Error 500
- Verificar permisos de `storage` y `bootstrap/cache`
- Revisar logs: `storage/logs/laravel.log`
- Verificar configuración de base de datos

### Assets no cargan
- Verificar que se ejecutó `npm run build`
- Revisar configuración de `ASSET_URL` en `.env`

### Sesiones no funcionan
- Verificar configuración de Redis
- Limpiar cache de sesiones: `php artisan session:table`