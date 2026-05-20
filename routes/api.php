<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\AuthController;
use App\Http\Controllers\API\v1\RecycleController;
use App\Http\Controllers\API\v1\TachoController;

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::post('/reciclar', [RecycleController::class, 'procesarReciclaje']); 
    Route::get('/tacho/{id}/pin', [RecycleController::class, 'obtenerPin']); 

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::get('/user-profile', [AuthController::class, 'profile']);
       
        Route::post('/vincular-tacho', [RecycleController::class, 'vincularTacho']); 
        Route::get('/tacho/status', [TachoController::class, 'status']);
        
        // Recompensas y Canjes
        Route::get('/recompensas', [\App\Http\Controllers\API\v1\RewardController::class, 'index']);
        Route::post('/canjear', [\App\Http\Controllers\API\v1\RewardController::class, 'canjear']);
        Route::get('/mis-canjes', [\App\Http\Controllers\API\v1\RewardController::class, 'misCanjes']);

        // Dashboard e Historial
        Route::get('/dashboard', [\App\Http\Controllers\API\v1\DashboardController::class, 'index']);
        
        Route::middleware('role:ADMIN')->get('/admin-test', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'message' => '¡Hola Admin! Has superado la validación de Sanctum y del Rol.'
            ]);
        });
    });
});