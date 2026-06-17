<?php

use App\Http\Middleware\CheckIpBlacklistMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\TrackUserActivityMiddleware;
use App\Http\Middleware\TwoFactorVerifiedMiddleware;
use App\Http\Middleware\PreventBackHistoryMiddleware;
use App\Http\Middleware\LogFailedActionsMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Middleware global para WEB.
        // El registro de intentos fallidos de login se maneja directamente en
        // LoginController (con motivo y user_id), por eso ya no se usa aquí
        // TrackFailedLoginsMiddleware (evita doble conteo).
        $middleware->web(append: [
            CheckIpBlacklistMiddleware::class,
            // Registra automáticamente intentos fallidos en cualquier módulo
            // (validaciones de formularios/uploads, accesos denegados, etc.).
            LogFailedActionsMiddleware::class,
        ]);

        // Middleware global para API
        $middleware->api(prepend: [
            CheckIpBlacklistMiddleware::class,
        ]);

        // Alias de middleware
        $middleware->alias([
            // Spatie Permission
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // Custom Middleware
            'ip.blacklist' => CheckIpBlacklistMiddleware::class,
            'track.activity' => TrackUserActivityMiddleware::class,
            'prevent.back' => PreventBackHistoryMiddleware::class,
            '2fa.verified' => TwoFactorVerifiedMiddleware::class,

            // Laravel defaults
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        ]);

        // Grupos de middleware personalizados (opcional)
        $middleware->group('admin', [
            'auth',
            'role:super_admin|admin',
            'track.activity',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
