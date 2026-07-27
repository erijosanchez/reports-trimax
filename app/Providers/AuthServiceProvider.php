<?php

namespace App\Providers;

use App\Models\Dashboard;
use App\Models\UploadedFile;
use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Policies\FilePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Dashboard::class => DashboardPolicy::class,
        UploadedFile::class => FilePolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true;
            }

            return null;
        });

        // Piloto de A1 (ARQUITECTURA.md): los helpers de User siguen
        // existiendo, se convierten en el detalle de implementación del
        // Gate en vez de la API que consume todo el sistema. Controladores
        // y vistas pueden usar $this->authorize('ver-vouchers') / @can(...)
        // en vez de llamar al helper directamente.
        Gate::define('ver-vouchers', fn (User $u) => $u->puedeVerVouchers());
        Gate::define('revisar-vouchers', fn (User $u) => $u->puedeRevisarReportesSedes());
    }
}
