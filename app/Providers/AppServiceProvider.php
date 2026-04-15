<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;

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
        Carbon::setLocale('es');

        if (config('app.env') !== 'local') {
            // ya manejado por view:cache
        }

        \Illuminate\Support\Facades\Blade::setEchoFormat('%s');
    }
}
