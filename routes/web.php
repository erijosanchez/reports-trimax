<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\LocationApiController;
use App\Http\Controllers\ComercialController;

// ============================================================
// RUTAS PARA LARAVEL 11
// El middleware se aplica AQUÍ, no en los constructores
// ============================================================

// Auth Routes (Guest)
Route::middleware(['guest'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:login');
});

// Logout (Authenticated)
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// 2FA Routes (Authenticated)
Route::middleware(['auth'])->prefix('2fa')->name('2fa.')->group(function () {
    Route::get('/setup', [TwoFactorController::class, 'show'])->name('setup');
    Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
    Route::get('/verify', [TwoFactorController::class, 'show'])->name('verify');
    Route::post('/verify', [TwoFactorController::class, 'verify']);
    Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
});

// Protected Routes (Auth + Track + Prevent Back)
Route::middleware(['auth', 'throttle:dashboard', 'track.activity', 'prevent.back'])->group(function () {

    // Home
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::post('/gps-location', [LocationApiController::class, 'storeGpsLocation'])
        ->name('location.store-gps'); //API gps automatico

    // Dashboards
    Route::prefix('dashboards')->name('dashboards.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        // Admin only routes
        Route::middleware('role:super_admin|admin')->group(function () {
            Route::get('/create', [DashboardController::class, 'create'])->name('create');
            Route::post('/', [DashboardController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [DashboardController::class, 'edit'])->name('edit');
            Route::put('/{id}', [DashboardController::class, 'update'])->name('update');
            Route::post('/{id}/assign-users', [DashboardController::class, 'assignUsers'])->name('assign-users');

            // Solo super admin puede eliminar
            Route::delete('/{id}', [DashboardController::class, 'destroy'])
                ->name('destroy')
                ->middleware('role:super_admin');
        });
        Route::get('/{id}', [DashboardController::class, 'show'])->name('show');
    });

    // Files
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/', [FileController::class, 'index'])->name('index');
        Route::post('/upload', [FileController::class, 'upload'])
            ->name('upload')
            ->middleware('throttle:uploads');
        Route::get('/{id}/view', [FileController::class, 'view'])->name('view');
        Route::get('/{id}/download', [FileController::class, 'download'])->name('download');
        Route::delete('/{id}', [FileController::class, 'destroy'])->name('destroy');
    });

    // Comercial Routes (Consultor + Super Admin only)
    Route::prefix('comercial')->name('comercial.')->group(function () {

        /* ACUERDOS COMERCIALES APROBAR*/
        Route::get('/acuerdos', [ComercialController::class, 'acuerdos'])->name('acuerdos');
         // AJAX endpoints
        Route::get('/acuerdos/obtener', [ComercialController::class, 'obtenerAcuerdos'])->name('acuerdos.obtener');
        Route::get('/acuerdos/usuarios', [ComercialController::class, 'obtenerUsuariosCreadores'])->name('acuerdos.usuarios');
        Route::post('/acuerdos/crear', [ComercialController::class, 'crearAcuerdo'])->name('acuerdos.crear');
        Route::post('/acuerdos/{id}/validar', [ComercialController::class, 'validarAcuerdo'])->name('acuerdos.validar');
        Route::post('/acuerdos/{id}/aprobar', [ComercialController::class, 'aprobarAcuerdo'])->name('acuerdos.aprobar');
        Route::post('/acuerdos/{id}/deshabilitar', [ComercialController::class, 'deshabilitarAcuerdo'])->name('acuerdos.deshabilitar');
        Route::post('/acuerdos/{id}/extender', [ComercialController::class, 'extenderAcuerdo'])->name('acuerdos.extender');
        Route::get('/acuerdos/{id}/archivo/{index}', [ComercialController::class, 'descargarArchivo'])->name('acuerdos.descargar');
        Route::post('/acuerdos/{id}/rehabilitar', [ComercialController::class, 'rehabilitarAcuerdo'])->name('acuerdos.rehabilitar');
        
        /* CONSULTAR ORDENES -> SHEET */
        Route::get('consultar-orden', [ComercialController::class, 'consultarOrden'])
            ->name('orden');
        Route::get('obtener-ordenes', [ComercialController::class, 'obtenerOrdenes'])
            ->name('ordenes.obtener');
        Route::get('obtener-sedes', [ComercialController::class, 'obtenerSedes'])
            ->name('ordenes.sedes');
        Route::post('limpiar-cache', [ComercialController::class, 'limpiarCache'])
            ->name('ordenes.cache');
        Route::get('exportar-excel', [ComercialController::class, 'exportarExcel'])
            ->name('ordenes.exportar');
    });

    // Admin Routes (Admin + Super Admin only)
    Route::middleware('role:super_admin|admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs');
        Route::get('/security', [AdminController::class, 'security'])->name('security');
        // 🌍 RUTAS DE UBICACIONES
        Route::prefix('locations')->name('locations.')->group(function () {
            Route::get('/map', [LocationController::class, 'map'])->name('map');
            Route::get('/', [LocationController::class, 'index'])->name('index');
            Route::get('/user/{userId}', [LocationController::class, 'userHistory'])->name('user-history');
            Route::get('/live', [LocationController::class, 'liveLocations'])->name('live'); // API

        });

        // User Management
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

require __DIR__ . '/auth.php';
