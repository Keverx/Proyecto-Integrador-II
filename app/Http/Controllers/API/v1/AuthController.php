<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Http\Request;
use Exception;

class AuthController extends Controller
{
    protected $authService;
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

    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            $this->authService->verifyEmail($request->email, $request->codigo_verificacion);
            return response()->json([
                'status' => 'success',
                'message' => '¡Cuenta verificada exitosamente! Ya puedes iniciar sesión.'
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function resendVerificationCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:usuarios,email']);

        try {
            $this->authService->resendCode($request->email);
            return response()->json([
                'status' => 'success',
                'message' => 'Se ha enviado un nuevo código de verificación a tu correo.'
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        
        $user->load('role');

        $responseData = [
            'id_usuario' => $user->id_usuario,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'estado_cuenta' => $user->estado_cuenta,
            'rol' => $user->role ? $user->role->nombre : 'USER',
            'eco_puntos_personales' => $user->puntos,
            'fecha_registro' => $user->fecha_registro ?? now()->toIso8601String(),
        ];

        $grupo = $user->families()->with('tachos')->first();

        if ($grupo) {
            $tachoPrincipal = $grupo->tachos->first();
            
            $responseData['id_familia'] = $grupo->id_grupo;
            $responseData['familia'] = [
                'id_familia' => $grupo->id_grupo,
                'nombre_familia' => $grupo->nombre_grupo,
                'id_tacho_asignado' => $tachoPrincipal ? $tachoPrincipal->id_tacho : null,
                'puntos_grupales' => $grupo->puntos_grupales,
                'fecha_creacion' => $grupo->fecha_creacion ? \Carbon\Carbon::parse($grupo->fecha_creacion)->toIso8601String() : null,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $responseData
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:usuarios,email']);

        try {
            $this->authService->forgotPassword($request->email);
            return response()->json([
                'status' => 'success',
                'message' => 'Se ha enviado un código de restablecimiento a tu correo.'
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
            'codigo' => 'required|string|size:6'
        ]);

        try {
            $this->authService->verifyResetCode($request->email, $request->codigo);
            return response()->json([
                'status' => 'success',
                'message' => 'Código verificado correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
            'codigo' => 'required|string|size:6',
            'password' => 'required|string|min:6'
        ]);

        try {
            $this->authService->resetPassword($request->email, $request->codigo, $request->password);
            return response()->json([
                'status' => 'success',
                'message' => 'Contraseña restablecida exitosamente.'
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Sesión cerrada correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Hubo un error al cerrar sesión.'], 500);
        }
    }
}