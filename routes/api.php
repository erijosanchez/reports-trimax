<?php

use App\Http\Controllers\ComercialController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\SurveyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GpsController;
use App\Http\Controllers\Api\EntregaController;
use App\Http\Controllers\Api\AuthMotorizadoController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/encuesta/{token}', [SurveyController::class, 'getData']);
Route::post('/encuesta/{token}', [SurveyController::class, 'store']);

// Estadísticas (proteger en producción)
Route::get('/stats', [MarketingController::class, 'getStats']);

// Rutas para órdenes desde Google Sheets
Route::prefix('ordenes')->group(function () {
    Route::get('/', [ComercialController::class, 'index']);
    Route::get('/sedes', [ComercialController::class, 'getSedes']);
    Route::post('/clear-cache', [ComercialController::class, 'clearCache']);
    Route::get('/test-connection', [ComercialController::class, 'testConnection']);
});


// Ruta para tracking de motorizados
// ── Auth pública ──────────────────────────────────────────
Route::post('/motorizado/login', [AuthMotorizadoController::class, 'login']);

// ── Rutas protegidas (requieren token Sanctum) ────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/motorizado/logout', [AuthMotorizadoController::class, 'logout']);
    Route::get('/motorizado/me',      [AuthMotorizadoController::class, 'me']);
    Route::get('/motorizado/historial', [GpsController::class, 'historialMotorizado']);

    // GPS
    Route::post('/gps/iniciar',    [GpsController::class, 'iniciar']);
    Route::post('/gps/posicion',   [GpsController::class, 'guardarPosicion']);
    Route::post('/gps/finalizar',  [GpsController::class, 'finalizar']);
    Route::get('/gps/ruta-activa', [GpsController::class, 'rutaActiva']);

    // Entregas
    Route::get('/entregas/hoy',              [EntregaController::class, 'hoy']);
    Route::post('/entregas/{id}/completar',  [EntregaController::class, 'completar']);
    Route::post('/entregas/{id}/fallar',     [EntregaController::class, 'fallar']);
});

// ── API para el CRM admin (usa auth web normal) ───────────
Route::prefix('admin')->group(function () {
    Route::get('/mapa-vivo',        [GpsController::class, 'mapaVivo']);
    Route::get('/historial-km',     [GpsController::class, 'historialKm']);
    Route::get('/resumen-diario',   [GpsController::class, 'resumenDiario']);
    Route::get('/recorrido',   [GpsController::class, 'recorrido']);
});
