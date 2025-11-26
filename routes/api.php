<?php

use App\Http\Controllers\ComercialController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas para órdenes desde Google Sheets
Route::prefix('ordenes')->group(function () {
    Route::get('/', [ComercialController::class, 'index']);
    Route::get('/sedes', [ComercialController::class, 'getSedes']);
    Route::post('/clear-cache', [ComercialController::class, 'clearCache']);
    Route::get('/test-connection', [ComercialController::class, 'testConnection']);
});