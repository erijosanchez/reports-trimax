<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */

    public const HOME = '/home';

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        // Rate limit general para API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limit estricto para login
        RateLimiter::for('login', function (Request $request) {
            $key = $request->input('email') . '|' . $request->ip();

            return [
                // 5 intentos por minuto
                Limit::perMinute(5)->by($key)->response(function () {
                    return response()->json([
                        'message' => 'Demasiados intentos de inicio de sesión. Por favor, intente de nuevo en 1 minuto.'
                    ], 429);
                }),
                // 20 intentos por hora
                Limit::perHour(20)->by($key)->response(function () {
                    return response()->json([
                        'message' => 'Ha excedido el límite de intentos. Cuenta bloqueada temporalmente.'
                    ], 429);
                }),
            ];
        });

        // Rate limit por rol de usuario
        RateLimiter::for('dashboard', function (Request $request) {
            if (!$request->user()) {
                return Limit::none();
            }

            // Super admin sin límites
            if ($request->user()->hasRole('super_admin')) {
                return Limit::none();
            }

            // Admin: 200 peticiones por minuto
            if ($request->user()->hasRole('admin')) {
                return Limit::perMinute(200)->by($request->user()->id);
            }

            // Usuario normal: 60 peticiones por minuto
            return Limit::perMinute(60)->by($request->user()->id);
        });

        // Rate limit para subida de archivos
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Demasiadas subidas de archivos. Espere un momento.'
                    ], 429);
                });
        });

        // Rate limit para acciones sensibles
        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limit para búsquedas/queries pesadas
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}
