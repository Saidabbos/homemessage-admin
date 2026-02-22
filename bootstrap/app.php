<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Auto-update order statuses every 5 minutes
        $schedule->command('orders:process-statuses')
            ->everyFiveMinutes()
            ->timezone('Asia/Tashkent')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scheduler.log'));

        // Clean up expired OTP codes daily at 2 AM
        $schedule->command('otp:cleanup')
            ->dailyAt('02:00')
            ->timezone('Asia/Tashkent');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'app/save-name',
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        $middleware->replace(
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Http\Middleware\Authenticate::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
