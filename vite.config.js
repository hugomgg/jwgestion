import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                // CSS personalizados
                'resources/css/dark-mode.css',
                'resources/css/users-index.css',
                'resources/css/public-informe.css',
                'resources/css/programas-index.css',
                'resources/css/programas-resumen.css',
                'resources/css/programas-edit.css',
                'resources/css/informes-index.css',
                'resources/css/grupos-index.css',
                // JS personalizados
                'resources/js/dark-mode.js',
                'resources/js/users-index.js',
                'resources/js/public-informe.js',
                'resources/js/programas-show.js',
                'resources/js/programas-index.js',
                'resources/js/programas-edit.js',
                'resources/js/informes-index.js',
                'resources/js/grupos-index.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        minify: 'terser',
        terser: {
            compress: {
                drop_console: true,
            },
        },
        cssMinify: true,
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
    },
});
