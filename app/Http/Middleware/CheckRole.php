<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Obtener el usuario autenticado (inyectado previamente por auth:sanctum)
        $user = $request->user();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'No autenticado'], 401);
        }

        // 2. Verificar el rol utilizando el método hasRole del modelo
        if (!$user->hasRole($role)) {
            return response()->json(['status' => 'error', 'message' => 'Acceso denegado. Se requiere el rol: ' . $role], 403);
        }

        return $next($request);
    }
}
