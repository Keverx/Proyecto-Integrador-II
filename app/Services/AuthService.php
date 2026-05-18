<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\Contracts\AuthServiceInterface;
use Exception;

class AuthService implements AuthServiceInterface
{
    public function registerUser(array $data)
    {
        $user = User::create([
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'estado_cuenta' => 'PENDIENTE',
            'id_rol' => $data['id_rol'] ?? 1,
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'user_id' => $user->id_usuario,
            'nombre' => $user->nombre,
            'token' => $token
        ];
    }
    public function loginUser(array $credentials)
    {
        $user = User::where('email', $credentials['email'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            throw new Exception('Credenciales incorrectas', 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'user_id' => $user->id_usuario,
            'nombre' => $user->nombre,
            'puntos' => $user->puntos,
            'token' => $token
        ];
    }

}
