<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\Request;
use Exception;

class AuthController extends Controller
{
    protected $authService;
    // DIP: Inyección de Dependencias
    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterUserRequest $request)
    {
        try {
            $data = $this->authService->registerUser($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Usuario registrado con éxito',
                'data' => $data
            ], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function login(LoginUserRequest $request)
    {
        try {
            $data = $this->authService->loginUser($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => '¡Bienvenido a EcoScan!',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function profile(Request $request)
    {
        // Con auth:sanctum, Laravel ya validó el token e inyectó al usuario en $request
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'data' => [
                'nombre' => $user->nombre,
                'email' => $user->email,
                'puntos' => $user->puntos,
                'estado' => $user->estado_cuenta
            ]
        ]);
    }
}