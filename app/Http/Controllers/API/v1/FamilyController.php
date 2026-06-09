<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Family;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\PointTransaction;

class FamilyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $Familys = $user->families()->get();
        return response()->json([
            'status' => 'success',
            'data' => $Familys
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_grupo' => 'required|string|max:100'
        ]);

        $user = $request->user();

        $codigo = strtoupper(Str::random(6));
        while (Family::where('codigo_invitacion', $codigo)->exists()) {
            $codigo = strtoupper(Str::random(6));
        }

        DB::beginTransaction();
        try {
            $Family = Family::create([
                'nombre_grupo' => $request->nombre_grupo,
                'codigo_invitacion' => $codigo
            ]);

            $Family->usuarios()->attach($user->id_usuario, [
                'rol_en_grupo' => 'ADMIN_GRUPO',
                'fecha_union' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Family creado exitosamente',
                'data' => $Family
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Error al crear el Family', 'details' => $e->getMessage()], 500);
        }
    }

    public function join(Request $request)
    {
        $request->validate([
            'codigo_invitacion' => 'required|string'
        ]);

        $user = $request->user();
        $Family = Family::where('codigo_invitacion', strtoupper($request->codigo_invitacion))->first();

        if (!$Family) {
            return response()->json(['status' => 'error', 'message' => 'Código de invitación inválido'], 404);
        }

        if ($Family->usuarios()->where('usuarios.id_usuario', $user->id_usuario)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Ya eres miembro de este Family'], 400);
        }

        $Family->usuarios()->attach($user->id_usuario, [
            'rol_en_grupo' => 'MIEMBRO',
            'fecha_union' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Te has unido al Family exitosamente',
            'data' => $Family
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $Family = Family::with('usuarios')->find($id);

        if (!$Family) {
            return response()->json(['status' => 'error', 'message' => 'Family no encontrado'], 404);
        }

        // Verificar que el usuario pertenece al Family
        $isMember = $Family->usuarios->contains('id_usuario', $user->id_usuario);
        if (!$isMember) {
            return response()->json(['status' => 'error', 'message' => 'No tienes permiso para ver este Family'], 403);
        }

        // Calcular ranking: Puntos generados por cada miembro DESDE su fecha de unión al Family
        $ranking = $Family->usuarios->map(function ($miembro) use ($Family) {
            $fechaUnion = $miembro->pivot->fecha_union;
            
            // Ingresos de este usuario en general desde la fecha de unión (puede modificarse a filtro estricto por id_grupo)
            $puntosIngreso = PointTransaction::where('id_usuario', $miembro->id_usuario)
                ->where('tipo_movimiento', 'INGRESO')
                ->where('fecha_movimiento', '>=', $fechaUnion)
                ->sum('monto');
            
            return [
                'id_usuario' => $miembro->id_usuario,
                'nombre' => $miembro->nombre,
                'rol_en_grupo' => $miembro->pivot->rol_en_grupo,
                'fecha_union' => $fechaUnion,
                'puntos_aportados' => (int) $puntosIngreso
            ];
        })->sortByDesc('puntos_aportados')->values()->toArray();

        $totalPuntosFamilia = array_sum(array_column($ranking, 'puntos_aportados'));

        return response()->json([
            'status' => 'success',
            'data' => [
                'id_grupo' => $Family->id_grupo,
                'nombre_grupo' => $Family->nombre_grupo,
                'codigo_invitacion' => $Family->codigo_invitacion,
                'fecha_creacion' => $Family->fecha_creacion,
                'puntos_totales' => $totalPuntosFamilia,
                'ranking' => $ranking
            ]
        ]);
    }
    public function leave(Request $request, $id)
    {
        $user = $request->user();
        $Family = Family::find($id);

        if (!$Family) {
            return response()->json(['status' => 'error', 'message' => 'Familia no encontrada'], 404);
        }

        $isMember = $Family->usuarios()->where('usuarios.id_usuario', $user->id_usuario)->exists();
        if (!$isMember) {
            return response()->json(['status' => 'error', 'message' => 'No eres miembro de esta familia'], 400);
        }

        $Family->usuarios()->detach($user->id_usuario);
        if ($Family->usuarios()->count() === 0) {
            $Family->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Has abandonado la familia exitosamente'
        ]);
    }

    public function removeMember(Request $request, $id, $userId)
    {
        $user = $request->user();
        $Family = Family::find($id);

        if (!$Family) {
            return response()->json(['status' => 'error', 'message' => 'Familia no encontrada'], 404);
        }

        $currentUserPivot = $Family->usuarios()->where('usuarios.id_usuario', $user->id_usuario)->first();
        if (!$currentUserPivot || $currentUserPivot->pivot->rol_en_grupo !== 'ADMIN_GRUPO') {
            return response()->json(['status' => 'error', 'message' => 'No tienes permisos para expulsar miembros de esta familia'], 403);
        }

        if ($user->id_usuario == $userId) {
            return response()->json(['status' => 'error', 'message' => 'No puedes expulsarte a ti mismo. Usa la opción de abandonar.'], 400);
        }

        $memberToRemove = $Family->usuarios()->where('usuarios.id_usuario', $userId)->first();
        if (!$memberToRemove) {
            return response()->json(['status' => 'error', 'message' => 'El usuario no es miembro de esta familia'], 404);
        }

        $Family->usuarios()->detach($userId);

        return response()->json([
            'status' => 'success',
            'message' => 'Miembro expulsado exitosamente'
        ]);
    }
}
