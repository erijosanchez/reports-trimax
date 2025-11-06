<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;

// Auth Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
    
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// 2FA Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/2fa/setup', [TwoFactorController::class, 'show'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/verify', [TwoFactorController::class, 'show'])->name('2fa.verify');
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
});

// Protected Routes
Route::middleware(['auth', 'throttle:dashboard', 'track.activity', 'prevent.back'])->group(function () {
    
    // Home
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Dashboards
    Route::prefix('dashboards')->name('dashboards.')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/{id}', [DashboardController::class, 'show'])->name('show');
        Route::get('/create', [DashboardController::class, 'create'])
            ->name('create')
            ->middleware('role:super_admin|admin');
        Route::post('/', [DashboardController::class, 'store'])
            ->name('store')
            ->middleware('role:super_admin|admin');
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
    
    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:super_admin|admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users-online', [AdminController::class, 'usersOnline'])->name('users-online');
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs');
        Route::get('/security', [AdminController::class, 'security'])->name('security');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
        
        // User Management
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});

require __DIR__ . '/auth.php';
