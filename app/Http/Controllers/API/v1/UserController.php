<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    public function update(UpdateUserRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();

        if (isset($validated['nombre'])) {
            $user->nombre = $validated['nombre'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['new_password']) && isset($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $user->password_hash)) {
                return response()->json(['status' => 'error', 'message' => 'La contraseña actual es incorrecta.'], 400);
            }
            $user->password_hash = Hash::make($validated['new_password']);
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Perfil actualizado correctamente.',
            'data' => [
                'nombre' => $user->nombre,
                'email' => $user->email
            ]
        ]);
    }
}
