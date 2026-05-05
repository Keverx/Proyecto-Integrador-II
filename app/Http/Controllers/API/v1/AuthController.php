<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // FUNCIÓN DE REGISTRO
    public function register(Request $request)
    {
        // 1. Validamos los datos que vienen del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:usuarios', // No permite correos repetidos
            'password' => 'required|string|min:6',
        ]);

        // 2. Creamos el usuario en la tabla 'usuarios'
        // En la función register del AuthController.php
        $user = User::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'estado_cuenta' => 'PENDIENTE', // <--- Debe ser exactamente igual a tu ENUM
        ]);

        // 3. Generamos un token inicial
        $tokenSession = Str::random(60);
        $user->token_recuperacion = $tokenSession;
        $user->save();

        // 4. Respondemos con el éxito
        return response()->json([
            'status' => 'success',
            'message' => 'Usuario registrado con éxito',
            'data' => [
                'user_id' => $user->id_usuario,
                'nombre' => $user->nombre,
                'token' => $tokenSession
            ]
        ], 201); // 201 significa "Creado"
    }

    // FUNCIÓN DE LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $tokenSession = Str::random(60);
        $user->token_recuperacion = $tokenSession;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => '¡Bienvenido a EcoScan!',
            'data' => [
                'user_id' => $user->id_usuario,
                'nombre' => $user->nombre,
                'puntos' => $user->puntos,
                'token' => $tokenSession
            ]
        ]);
    }

    public function profile(Request $request)
    {
        // Buscamos al usuario que tenga el token que nos envían por la URL o Header
        // Nota: Por ahora lo haremos simple buscando por el token que guardamos
        $user = User::where('token_recuperacion', $request->token)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sesión no válida'
            ], 401);
        }

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