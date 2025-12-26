# Configuración de Vite para Producción

## Resumen de Cambios

Se ha optimizado la configuración de Vite para que todos los archivos JS y CSS personalizados se compilen, minifiquen y versionen correctamente para producción.

## Archivos Procesados por Vite

### CSS Personalizados
- `resources/css/dark-mode.css`
- `resources/css/users-index.css`
- `resources/css/public-informe.css`
- `resources/css/programas-index.css`
- `resources/css/programas-edit.css`
- `resources/css/informes-index.css`
- `resources/css/grupos-index.css`

### JS Personalizados
- `resources/js/dark-mode.js`
- `resources/js/users-index.js`
- `resources/js/public-informe.js`
- `resources/js/programas-show.js`
- `resources/js/programas-index.js`
- `resources/js/programas-edit.js`
- `resources/js/informes-index.js`
- `resources/js/grupos-index.js`

### Archivos Base
- `resources/sass/app.scss`
- `resources/js/app.js`

## Configuración de Minificación

En `vite.config.js` se ha configurado:

```javascript
build: {
    minify: 'terser',
    terser: {
        compress: {
            drop_console: true,  // Elimina console.log en producción
        },
    },
    cssMinify: true,  // Minifica CSS
}
```

## Archivos Blade Actualizados

Los siguientes archivos Blade ahora usan `@vite()` en lugar de `asset()`:

1. `resources/views/layouts/app.blade.php` - dark-mode.css y dark-mode.js
2. `resources/views/users/index.blade.php`
3. `resources/views/public/informe.blade.php`
4. `resources/views/programas/index.blade.php`
5. `resources/views/programas/edit.blade.php`
6. `resources/views/programas/show.blade.php`
7. `resources/views/informes/index.blade.php`
8. `resources/views/grupos/index.blade.php`

## Comandos para Despliegue

### Desarrollo Local
```bash
npm run dev
```

### Build de Producción
```bash
npm run build
```

### Despliegue Completo
```bash
# 1. Compilar assets
npm run build

# 2. Optimizar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. O usar el script de despliegue
./deploy.sh
```

## Verificación en Producción

Después del despliegue, verifica que:

1. ✅ Los archivos se cargan desde `/build/assets/` con hash de versión
2. ✅ Los archivos CSS están minificados (sin espacios ni comentarios)
3. ✅ Los archivos JS están minificados y sin `console.log`
4. ✅ No hay errores 404 en la consola del navegador
5. ✅ El manifest.json existe en `public/build/manifest.json`

## Archivos que Siguen Usando asset()

Los siguientes recursos **deben** seguir usando `asset()` porque son librerías de terceros o recursos estáticos:

### CSS de Terceros
- FontAwesome: `css/fontawesome/all.min.css`
- DataTables: `css/datatables/*.css`
- Select2: `css/select2.min.css`, `css/select2-bootstrap-5-theme.min.css`
- Bootstrap (en public-informe): `css/bootstrap/bootstrap.min.css`
- Fuentes: `css/fonts/nunito.css`

### JS de Terceros
- jQuery: `js/jquery-3.7.1.min.js`, `js/jquery-3.7.0.min.js`
- DataTables: `js/datatables/*.js`
- Select2: `js/select2.min.js`
- Bootstrap (en public-informe): `js/bootstrap/bootstrap.bundle.min.js`

Estos archivos no necesitan ser procesados por Vite ya que están pre-minificados.

## Estructura de Archivos Resultante

```
public/
├── build/
│   ├── manifest.json          # Mapeo de archivos originales a compilados
│   └── assets/
│       ├── app-[hash].css     # SCSS compilado y minificado
│       ├── app-[hash].js      # JS principal compilado
│       ├── dark-mode-[hash].css
│       ├── dark-mode-[hash].js
│       ├── users-index-[hash].css
│       ├── users-index-[hash].js
│       └── ... (más archivos con hash)
├── css/                       # Librerías de terceros (sin procesar)
└── js/                        # Librerías de terceros (sin procesar)
```

## Solución de Problemas

### Error: Assets no se cargan
```bash
# Limpiar cache y rebuild
php artisan optimize:clear
npm run build
```

### Error: manifest.json no encontrado
```bash
# Asegúrate de ejecutar build antes de desplegar
npm run build
```

### Error: CSS/JS no minificado
```bash
# Verifica que terser esté instalado
npm install --save-dev terser
npm run build
```

## Ventajas de esta Configuración

1. ✅ **Minificación automática**: JS y CSS más pequeños
2. ✅ **Versionado con hash**: Cache busting automático
3. ✅ **Tree shaking**: Elimina código no usado
4. ✅ **Eliminación de console.log**: Producción más limpia
5. ✅ **Compilación optimizada**: Mejor rendimiento
6. ✅ **Compatibilidad**: Funciona en desarrollo y producción
