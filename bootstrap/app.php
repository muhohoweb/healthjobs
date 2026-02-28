<?php

use App\Http\Middleware\EnsureProfileComplete;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;


use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\PermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('subscriptions:expire')->hourly();
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Disable CSRF protection for specific routes
        $middleware->validateCsrfTokens(except: [
            'send',  // Your WhatsApp route
            'whats-app-jobs',
            'whats-app-events',
            'mpesa/*',
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloaadedAssets::class,
        ]);

        // Register middleware aliases
        $middleware->alias([
            'roles' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'complete-profile' => EnsureProfileComplete::class,
            'active-subscription' => \App\Http\Middleware\CheckActiveSubscription::class, // add this
        ]);
        $middleware->redirectUsersTo('/health-jobs');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
