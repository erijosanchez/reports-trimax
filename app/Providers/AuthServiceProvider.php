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

        // A1 (ARQUITECTURA.md): los helpers de User siguen existiendo, se
        // convierten en el detalle de implementación del Gate en vez de la
        // API que consume todo el sistema. Controladores y vistas pueden
        // usar $this->authorize('ver-vouchers') / @can(...) en vez de
        // llamar al helper directamente. Un Gate por cada puedeX() de
        // User — mismo criterio para los 20, no solo para Vouchers.
        Gate::define('ver-vouchers', fn (User $u) => $u->puedeVerVouchers());
        Gate::define('revisar-vouchers', fn (User $u) => $u->puedeRevisarReportesSedes());

        Gate::define('ver-ventas-consolidadas', fn (User $u) => $u->puedeVerVentasConsolidadas());
        Gate::define('ver-descuentos-especiales', fn (User $u) => $u->puedeVerDescuentosEspeciales());
        Gate::define('ver-consultar-orden', fn (User $u) => $u->puedeVerConsultarOrden());
        Gate::define('ver-acuerdos-comerciales', fn (User $u) => $u->puedeVerAcuerdosComerciales());
        Gate::define('ver-lead-time', fn (User $u) => $u->puedeVerLeadTime());
        Gate::define('ver-venta-clientes', fn (User $u) => $u->puedeVerVentaClientes());
        Gate::define('ver-top-clientes', fn (User $u) => $u->puedeVerTopClientes());
        Gate::define('ver-ordenes-x-sede', fn (User $u) => $u->puedeVerOrdenesXSede());
        Gate::define('ver-asignacion-bases', fn (User $u) => $u->puedeVerAsignacionBases());
        Gate::define('crear-requerimientos', fn (User $u) => $u->puedeCrearRequerimientos());
        Gate::define('ver-todos-requerimientos', fn (User $u) => $u->puedeVerTodosLosRequerimientos());
        Gate::define('gestionar-requerimientos', fn (User $u) => $u->puedeGestionarRequerimientos());
        Gate::define('ver-cobranza-sedes', fn (User $u) => $u->puedeVerCobranzaSedes());
        Gate::define('revisar-reportes-sedes', fn (User $u) => $u->puedeRevisarReportesSedes());
        Gate::define('ver-productivy-total', fn (User $u) => $u->puedeVerProductivyTotal());
        Gate::define('acceder-productivy', fn (User $u) => $u->puedeAccederProducitvy());
        Gate::define('ver-pendiente-entrega-montura', fn (User $u) => $u->puedeVerPendienteEntregaMontura());
        Gate::define('ver-motorizados', fn (User $u) => $u->puedeVerMotorizados());
        Gate::define('ver-retiros-ordenes', fn (User $u) => $u->puedeVerRetirosOrdenes());
        Gate::define('ver-desbloqueo', fn (User $u) => $u->puedeVerDesbloqueo());
    }
}
