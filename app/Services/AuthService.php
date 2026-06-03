<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\Contracts\AuthServiceInterface;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationMail;

class AuthService implements AuthServiceInterface
{
    public function registerUser(array $data)
    {
        $codigoVerificacion = sprintf("%06d", mt_rand(1, 999999));

        $user = User::create([
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'estado_cuenta' => 'PENDIENTE',
            'codigo_verificacion' => $codigoVerificacion,
            'id_rol' => $data['id_rol'] ?? 1,
        ]);

        try {
            Mail::to($user->email)->send(new VerificationMail($codigoVerificacion, $user->nombre));
        } catch (Exception $e) {
            // Ignoramos el error de correo en local si no está bien configurado para no bloquear el registro,
            // pero idealmente en producción debería registrarse en los logs.
        }

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

        if ($user->estado_cuenta === 'BLOQUEADO') {
            throw new Exception('Tu cuenta ha sido suspendida. Contacta al soporte.', 403);
        }

        if ($user->estado_cuenta === 'PENDIENTE') {
            throw new Exception('Tu cuenta no está verificada. Por favor, revisa tu correo electrónico.', 403);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            'user_id' => $user->id_usuario,
            'nombre' => $user->nombre,
            'email' => $user->email,
            'rol' => $user->id_rol == 2 ? 'ADMIN' : 'USER',
            'puntos' => $user->puntos,
            'token' => $token
        ];
    }

    public function verifyEmail(string $email, string $code)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception('Usuario no encontrado', 404);
        }

        if ($user->estado_cuenta === 'VERIFICADO') {
            throw new Exception('La cuenta ya se encuentra verificada', 400);
        }

        if ($user->codigo_verificacion !== $code) {
            throw new Exception('El código de verificación es incorrecto', 400);
        }

        $user->estado_cuenta = 'VERIFICADO';
        $user->codigo_verificacion = null;
        $user->save();

        return true;
    }

    public function resendCode(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new Exception('Usuario no encontrado', 404);
        }

        if ($user->estado_cuenta === 'VERIFICADO') {
            throw new Exception('La cuenta ya se encuentra verificada', 400);
        }

        $codigoVerificacion = sprintf("%06d", mt_rand(1, 999999));
        $user->codigo_verificacion = $codigoVerificacion;
        $user->save();

        Mail::to($user->email)->send(new VerificationMail($codigoVerificacion, $user->nombre));

        return true;
    }

}
