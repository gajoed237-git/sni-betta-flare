<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\BlockSuspiciousAccess::class);
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Throwable $e) {
            // Log malfunction to database
            try {
                \App\Models\SecurityLog::create([
                    'type' => 'malfunction',
                    'event' => 'Application Error: ' . get_class($e),
                    'details' => $e->getMessage(),
                    'metadata' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => substr($e->getTraceAsString(), 0, 1000),
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                    ],
                    'ip_address' => request()->ip(),
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                ]);
            } catch (\Throwable $loggingError) {
                // Fail silently to avoid infinite loops if DB is down
            }
        });
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        $schedule->command('app:cleanup-unverified-users')->daily();
    })
    ->create();
