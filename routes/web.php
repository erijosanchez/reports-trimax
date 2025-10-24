<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;;

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    Route::get('/dashboard/{id}', [DashboardController::class, 'show'])->name('dashboard.show');
    
    // Rutas de administración (solo para admins)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('dashboards', AdminDashboardController::class);
        Route::get('access', [UserAccessController::class, 'index'])->name('access.index');
        Route::post('access/{user}', [UserAccessController::class, 'update'])->name('access.update');
    });
});

require __DIR__.'/auth.php';
