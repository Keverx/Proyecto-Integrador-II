<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\AuthController; #ruta del controlador
use App\Http\Controllers\API\v1\RecycleController;

// Agrupamos todo bajo el prefijo "v1"
Route::prefix('v1')->group(function () {

    // Ruta pública para el Login
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/user-profile', [AuthController::class, 'profile']);
    
    // Rutas para el flujo del Tacho Inteligente
    Route::post('/vincular-tacho', [RecycleController::class, 'vincularTacho']); // App Móvil
    Route::post('/reciclar', [RecycleController::class, 'procesarReciclaje']); // ESP32-CAM
    Route::get('/tacho/{id}/pin', [RecycleController::class, 'obtenerPin']); // ESP32-CAM

    // Aquí pondremos más adelante las rutas protegidas (las que necesitan Token)
    Route::middleware('auth:sanctum')->group(function () {
        
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Ejemplo: Route::get('/puntos', [PuntosController::class, 'verPuntos']);
    });

});