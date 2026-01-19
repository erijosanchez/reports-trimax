<?php

use App\Http\Controllers\ComercialController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\SurveyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AssistantAIController;

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

// Ruta para el asistente de consultas
// Chat con IA
Route::post('/assistant/chat', [AssistantAIController::class, 'chat']);

// Consultas de datos (cuando integres la BD)
Route::post('/assistant', [AssistantController::class, 'query']);
