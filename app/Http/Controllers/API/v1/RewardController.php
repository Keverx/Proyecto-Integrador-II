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
    public function index()
    {
        $rewards = Reward::where('stock_disponible', '>', 0)->get();
        return response()->json([
            'status' => 'success',
            'data' => $rewards
        ]);
    }

    public function canjear(Request $request)
    {
        $request->validate([
            'id_premio' => 'required|exists:premios_incentivos,id_premio'
        ]);

        try {
            DB::beginTransaction();

            $user = $request->user();
            $reward = Reward::findOrFail($request->id_premio);

            // Validar stock
            if ($reward->stock_disponible <= 0) {
                return response()->json(['status' => 'error', 'message' => 'Premio agotado.'], 400);
            }

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

            $reward->stock_disponible -= 1;
            $reward->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => '¡Canje realizado con éxito!',
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
            $canjes = Exchange::where('id_usuario', $user->id_usuario)
                ->with('reward')
                ->orderBy('fecha_canje', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $canjes
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener tus canjes: ' . $e->getMessage()
            ], 500);
        }
    }
}
