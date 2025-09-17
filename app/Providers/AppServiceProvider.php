<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Solo manejar errores de conexión a la base de datos
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            \Log::error('Database Connection Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'connection' => config('database.default'),
                'timestamp' => now()->toISOString()
            ]);
        }
    }
}
