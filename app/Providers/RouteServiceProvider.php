<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            $key = $request->input('email') . '|' . $request->ip();
            return [
                Limit::perMinute(5)->by($key)->response(function () {
                    return response()->json([
                        'message' => 'Demasiados intentos. Intente en 1 minuto.'
                    ], 429);
                }),
                Limit::perHour(20)->by($key),
            ];
        });

        RateLimiter::for('dashboard', function (Request $request) {
            if (!$request->user()) {
                return Limit::none();
            }
            if ($request->user()->hasRole('super_admin')) {
                return Limit::none();
            }
            if ($request->user()->hasRole('admin')) {
                return Limit::perMinute(200)->by($request->user()->id);
            }
            return Limit::perMinute(60)->by($request->user()->id);
        });

        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
