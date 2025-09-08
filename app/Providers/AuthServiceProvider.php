<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Gates para permisos
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('can.access.admin.menu', function ($user) {
            return $user->canAccessAdminMenu();
        });

        Gate::define('can.view.users', function ($user) {
            return $user->canViewUsers();
        });

        Gate::define('can.edit.own.congregation', function ($user) {
            return $user->isCoordinator() || $user->isSecretary();
        });

        // Directiva Blade para verificar si el usuario es administrador
        Blade::if('admin', function () {
            return Auth::check() && Auth::user()->isAdmin();
        });

        // Directiva Blade para verificar si el usuario es estudiante
        Blade::if('student', function () {
            return Auth::check() && Auth::user()->isStudent();
        });

        // Directiva Blade para verificar perfiles específicos
        Blade::if('role', function ($role) {
            return Auth::check() && Auth::user()->perfil == $role;
        });

        // Directiva Blade para verificar si el usuario está activo
        Blade::if('active', function () {
            return Auth::check() && Auth::user()->isActive();
        });
    }
}
