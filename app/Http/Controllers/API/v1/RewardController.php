<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\Exchange;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        $rewards = Reward::where('stock_disponible', '>', 0)->get();
        
        $individuales = $rewards->where('tipo_premio', 'INDIVIDUAL')->values();
        $grupales = $rewards->where('tipo_premio', 'GRUPAL')->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'individuales' => $individuales,
                'grupales' => $grupales
            ]
        ]);
    }

    public function canjear(Request $request)
    {
        $request->validate([
            'id_premio' => 'required|exists:premios_incentivos,id_premio',
            'id_grupo_afectado' => 'nullable|exists:grupos,id_grupo'
        ]);

        try {
            DB::beginTransaction();

            $user = $request->user();
            $reward = Reward::findOrFail($request->id_premio);

            // Validar stock
            if ($reward->stock_disponible <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Premio agotado.'], 400);
            }

            if ($reward->tipo_premio === 'INDIVIDUAL') {
                if ($user->puntos < $reward->costo_puntos) {
                    return response()->json(['status' => 'error', 'message' => 'Puntos insuficientes.'], 400);
                }

                $exchange = Exchange::create([
                    'id_usuario' => $user->id_usuario,
                    'id_premio' => $reward->id_premio,
                    'cuenta_origen' => 'PERSONAL',
                    'puntos_gastados' => $reward->costo_puntos,
                    'fecha_canje' => now()
                ]);

                DB::table('transacciones_puntos')->insert([
                    'id_usuario' => $user->id_usuario,
                    'tipo_movimiento' => 'EGRESO',
                    'monto' => $reward->costo_puntos,
                    'motivo' => 'Canje: ' . $reward->nombre_premio,
                    'fecha_movimiento' => now()
                ]);
            } else {
                // LOGICA GRUPAL (METAS)
                if (!$request->id_grupo_afectado) {
                    return response()->json(['status' => 'error', 'message' => 'Debe especificar la familia para reclamar esta meta.'], 400);
                }

                $family = \App\Models\Family::find($request->id_grupo_afectado);
                if (!$family) {
                    return response()->json(['status' => 'error', 'message' => 'Familia no encontrada.'], 404);
                }

                // Verificar si es ADMIN
                $pivot = $family->usuarios()->where('usuarios.id_usuario', $user->id_usuario)->first();
                if (!$pivot || $pivot->pivot->rol_en_grupo !== 'ADMIN_GRUPO') {
                    return response()->json(['status' => 'error', 'message' => 'Solo el administrador de la familia puede reclamar premios grupales.'], 403);
                }

                // Verificar si la familia ya reclamó este premio
                $alreadyClaimed = Exchange::where('id_premio', $reward->id_premio)
                    ->where('id_grupo_afectado', $family->id_grupo)
                    ->exists();
                if ($alreadyClaimed) {
                    return response()->json(['status' => 'error', 'message' => 'Esta familia ya ha reclamado esta meta grupal.'], 400);
                }

                // Calcular suma de puntos totales históricos de los miembros desde que se unieron
                $totalPoints = $family->usuarios->map(function ($miembro) {
                    $fechaUnion = $miembro->pivot->fecha_union;
                    return \App\Models\PointTransaction::where('id_usuario', $miembro->id_usuario)
                        ->where('tipo_movimiento', 'INGRESO')
                        ->where('fecha_movimiento', '>=', $fechaUnion)
                        ->sum('monto');
                })->sum();

                if ($totalPoints < $reward->costo_puntos) {
                    return response()->json(['status' => 'error', 'message' => 'La familia aún no alcanza la meta (' . $totalPoints . ' / ' . $reward->costo_puntos . ').'], 400);
                }

                // Reclamar la meta (costo 0 puntos porque es un logro)
                $exchange = Exchange::create([
                    'id_usuario' => $user->id_usuario,
                    'id_premio' => $reward->id_premio,
                    'cuenta_origen' => 'GRUPAL',
                    'id_grupo_afectado' => $family->id_grupo,
                    'puntos_gastados' => 0,
                    'fecha_canje' => now()
                ]);
            }

            $reward->stock_disponible -= 1;
            $reward->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => '¡Recompensa obtenida con éxito!',
                'data' => [
                    'id_canje' => $exchange->id_canje
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Ocurrió un error al canjear: ' . $e->getMessage()], 500);
        }
    }

    public function misCanjes(Request $request)
    {
        try {
            $user = $request->user();
            
            $canjesPersonales = Exchange::where('id_usuario', $user->id_usuario)
                ->where('cuenta_origen', 'PERSONAL')
                ->with('reward')
                ->orderBy('fecha_canje', 'desc')
                ->get();

            $familiasIds = $user->families()->pluck('grupos.id_grupo');
            $canjesGrupales = Exchange::whereIn('id_grupo_afectado', $familiasIds)
                ->where('cuenta_origen', 'GRUPAL')
                ->with('reward')
                ->orderBy('fecha_canje', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'individuales' => $canjesPersonales,
                    'grupales' => $canjesGrupales
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener tus canjes: ' . $e->getMessage()
            ], 500);
        }
    }
}
