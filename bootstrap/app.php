<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'can.access.admin.menu' => \App\Http\Middleware\CanAccessAdminMenuMiddleware::class,
            'can.access.people.management' => \App\Http\Middleware\CanAccessPeopleManagementMiddleware::class,
            'check.user.status' => \App\Http\Middleware\CheckUserStatusMiddleware::class,
            'load.profile' => \App\Http\Middleware\LoadUserProfile::class,
            'recaptcha' => \App\Http\Middleware\VerifyRecaptcha::class,
            'recaptcha.informe' => \App\Http\Middleware\VerifyRecaptchaInforme::class,
        ]);
        
        // Apply middlewares globally to web routes
        $middleware->web(append: [
            \App\Http\Middleware\LoadUserProfile::class,
            \App\Http\Middleware\CheckUserStatusMiddleware::class,
            \App\Http\Middleware\SqlLoggingMiddleware::class,
        ]);
    })
    ->withProviders([
        \App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
