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
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\UserMarketingController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\DescuentosEspecialesController;

// ============================================================
// RUTAS PARA LARAVEL 11
// El middleware se aplica AQUÍ, no en los constructores
// ============================================================

// Public Survey Route
Route::get('/encuesta/{token}', [SurveyController::class, 'show'])->name('survey.show');

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
    Route::get('/api/ventas-data', [HomeController::class, 'getVentasData'])->name('api.ventas.data');

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

    // Marketing Routes (All authenticated users)
    Route::prefix('marketing')->name('marketing.')->group(function () {
        // Marketing Dashboard
        Route::get('/amdpanel', [MarketingController::class, 'index'])->name('index');

        Route::prefix('users')->name('users.')->group(function () {
            // User Management (Super Admin and Marketing only)
            Route::get('/adminpanel', [UserMarketingController::class, 'index'])->name('index');
            Route::get('/create', [UserMarketingController::class, 'create'])->name('create');
            Route::post('/', [UserMarketingController::class, 'store'])->name('store');
            Route::get('/{id}', [UserMarketingController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [UserMarketingController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserMarketingController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserMarketingController::class, 'destroy'])->name('destroy');

            // Acciones especiales
            Route::post('/{id}/toggle-status', [UserMarketingController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{id}/regenerate-token', [UserMarketingController::class, 'regenerateToken'])->name('regenerate-token');
            Route::get('/{id}/preview', [UserMarketingController::class, 'preview'])->name('preview');
            Route::get('/{id}/qr', [UserMarketingController::class, 'generateQR'])->name('qr');

            Route::get('/{id}/assign-sedes', [UserMarketingController::class, 'assignSedesForm'])->name('assign-sedes');
            Route::post('/{id}/assign-sedes', [UserMarketingController::class, 'assignSedes'])->name('update-sedes');
        });
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
        Route::put('/acuerdos/{id}/editar', [ComercialController::class, 'editarAcuerdo'])->name('acuerdos.editar');
        Route::post('/acuerdos/{id}/cambiar-validacion', [ComercialController::class, 'cambiarValidacion'])->name('acuerdos.cambiar-validacion');
        Route::post('/acuerdos/{id}/cambiar-aprobacion', [ComercialController::class, 'cambiarAprobacion'])->name('acuerdos.cambiar-aprobacion');

        /* DESCUENTOS ESPECIALES */
        Route::get('/descuentos-especiales', [DescuentosEspecialesController::class, 'index'])->name('descuentos.index');
        Route::get('/descuentos-especiales/obtener', [DescuentosEspecialesController::class, 'obtenerDescuentos'])->name('descuentos.obtener');
        Route::post('/descuentos-especiales/crear', [DescuentosEspecialesController::class, 'crearDescuento'])->name('descuentos.crear');
        Route::put('/descuentos-especiales/{id}/editar', [DescuentosEspecialesController::class, 'editarDescuento'])->name('descuentos.editar');
        Route::post('/descuentos-especiales/{id}/validar', [DescuentosEspecialesController::class, 'validarDescuento'])->name('descuentos.validar');
        Route::post('/descuentos-especiales/{id}/aprobar', [DescuentosEspecialesController::class, 'aprobarDescuento'])->name('descuentos.aprobar');
        Route::post('/descuentos-especiales/{id}/cambiar-validacion', [DescuentosEspecialesController::class, 'cambiarValidacion'])->name('descuentos.cambiar-validacion');
        Route::post('/descuentos-especiales/{id}/cambiar-aprobacion', [DescuentosEspecialesController::class, 'cambiarAprobacion'])->name('descuentos.cambiar-aprobacion');
        Route::post('/descuentos-especiales/{id}/deshabilitar', [DescuentosEspecialesController::class, 'deshabilitarDescuento'])->name('descuentos.deshabilitar');
        Route::post('/descuentos-especiales/{id}/rehabilitar', [DescuentosEspecialesController::class, 'rehabilitarDescuento'])->name('descuentos.rehabilitar');
        Route::get('/descuentos-especiales/{id}/archivo/{index}', [DescuentosEspecialesController::class, 'descargarArchivo'])->name('descuentos.archivo');
        Route::get('/descuentos-especiales/usuarios', [DescuentosEspecialesController::class, 'obtenerUsuariosCreadores'])->name('descuentos.usuarios');


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
        Route::get('obtener-ordenes-recientes', [ComercialController::class, 'obtenerOrdenesRecientes'])
            ->name('ordenes.recientes');
        Route::get('obtener-estadisticas-generales', [ComercialController::class, 'obtenerEstadisticasGenerales'])
            ->name('ordenes.estadisticas');

        //Ver ventas de las sedes 
        Route::get('/ventas-sedes', [ComercialController::class, 'ventasSedes'])->name('ventas.sedes');
        Route::get('/api/ventas-sedes-data', [ComercialController::class, 'getVentasSedesData'])->name('ventas.sedes.data');
    });

    // Admin Routes (Admin + Super Admin only)
    Route::middleware('role:super_admin|admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs');
        Route::get('/security', [AdminController::class, 'security'])->name('security');
        // RUTAS DE UBICACIONES
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
