<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\AuthController; #ruta del controlador
use App\Http\Controllers\API\v1\RecycleController;

// Agrupamos todo bajo el prefijo "v1"
Route::prefix('v1')->group(function () {

    // Ruta pública para el Login y Registro
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Rutas para el flujo del Tacho Inteligente (ESP32) - Públicas temporalmente
    Route::post('/reciclar', [RecycleController::class, 'procesarReciclaje']); // ESP32-CAM
    Route::get('/tacho/{id}/pin', [RecycleController::class, 'obtenerPin']); // ESP32-CAM

    // ==========================================
    // RUTAS PROTEGIDAS (Requieren Sanctum Token)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {
        
        // Perfil de Usuario
        Route::get('/user-profile', [AuthController::class, 'profile']);
        
        // App Móvil
        Route::post('/vincular-tacho', [RecycleController::class, 'vincularTacho']); 

        // Ejemplo de ruta protegida por Rol (Encadenando middlewares)
        Route::middleware('role:ADMIN')->get('/admin-test', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'message' => '¡Hola Admin! Has superado la validación de Sanctum y del Rol.'
            ]);
        });
    });
});