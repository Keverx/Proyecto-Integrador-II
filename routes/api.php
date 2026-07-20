<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\v1\AuthController;
use App\Http\Controllers\API\v1\RecycleController;
use App\Http\Controllers\API\v1\TachoController;
use App\Http\Controllers\API\v1\FamilyController;
use App\Http\Controllers\API\v1\IncidenciaController;

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerificationCode']);
    
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
    Route::post('/reciclar', [RecycleController::class, 'procesarReciclaje']); 
    Route::get('/tacho/{id}/pin', [RecycleController::class, 'obtenerPin']); 

    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('/logout', [AuthController::class, 'logout']);
        
        Route::get('/user-profile', [AuthController::class, 'profile']);
        Route::put('/user-profile', [\App\Http\Controllers\API\v1\UserController::class, 'update']);
       
        Route::post('/vincular-tacho', [RecycleController::class, 'vincularTacho']); 
        Route::get('/tacho/status', [TachoController::class, 'status']);
        
        Route::get('/recompensas', [\App\Http\Controllers\API\v1\RewardController::class, 'index']);
        Route::post('/canjear', [\App\Http\Controllers\API\v1\RewardController::class, 'canjear']);
        Route::get('/mis-canjes', [\App\Http\Controllers\API\v1\RewardController::class, 'misCanjes']);

        Route::get('/grupos', [FamilyController::class, 'index']);
        Route::post('/grupos', [FamilyController::class, 'store']);
        Route::post('/grupos/join', [FamilyController::class, 'join']);
        Route::get('/grupos/{id}', [FamilyController::class, 'show']);
        Route::delete('/grupos/{id}/leave', [FamilyController::class, 'leave']);
        Route::delete('/grupos/{id}/members/{userId}', [FamilyController::class, 'removeMember']);

        Route::get('/dashboard', [\App\Http\Controllers\API\v1\DashboardController::class, 'index']);

        Route::get('/incidencias', [IncidenciaController::class, 'index']);
        Route::post('/incidencias', [IncidenciaController::class, 'store']);
        
        Route::middleware('role:ADMIN')->get('/admin-test', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'message' => '¡Hola Admin! Has superado la validación de Sanctum y del Rol.'
            ]);
        });
    });
});