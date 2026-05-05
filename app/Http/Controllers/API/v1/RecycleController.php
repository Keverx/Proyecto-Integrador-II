<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Bin; // Importamos el modelo del Tacho
use Illuminate\Support\Facades\Cache; // Importamos la herramienta de Cache (memoria temporal)
use Illuminate\Http\Request;

class RecycleController extends Controller
{
    /**
     *  VINCULAR TACHO (Usado por la App Móvil)
     * Cuando el usuario escanea el código QR en la pantalla del tacho.
     */
    public function vincularTacho(Request $request)
    {
        // 1. Validamos que la App nos mande el PIN del QR y quién es el usuario
        $request->validate([
            'pin_qr' => 'required|string',
            'token_usuario' => 'required|string'
        ]);

        // 2. Identificamos al usuario
        $user = User::where('token_recuperacion', $request->token_usuario)->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Usuario no identificado'], 404);
        }

        // 3. Buscamos qué tacho está mostrando ese PIN en su pantalla
        $tacho = Bin::where('pin_actual', $request->pin_qr)->first();
        if (!$tacho) {
            return response()->json(['status' => 'error', 'message' => 'Código QR inválido o ya usado'], 400);
        }

        // 4. MAGIA: Guardamos en Cache quién está usando el tacho
        // Estructura: Cache::put('nombre_clave', 'valor', 'tiempo_en_segundos');
        Cache::put('sesion_tacho_' . $tacho->id_tacho, $user->id_usuario, 60);

        // 5. SEGURIDAD: Actualizamos el PIN del tacho para que el QR que acaban de escanear quede inservible
        $nuevoPin = rand(1000, 9999); // Genera un nuevo PIN de 4 dígitos
        $tacho->pin_actual = (string)$nuevoPin;
        $tacho->save();

        return response()->json([
            'status' => 'success',
            'message' => '¡Tacho vinculado! Tienes 60 segundos para botar tu basura.'
        ]);
    }

    /**
     * PASO 2.B: PROCESAR RECICLAJE (Usado por el Tacho/ESP32)
     * Esta función recibe los datos del tacho y suma los puntos al usuario correspondiente.
     */
    public function procesarReciclaje(Request $request)
    {
        // 1. Validamos que el tacho envíe su ID y el material
        $request->validate([
            'tacho_id' => 'required|numeric',
            'material' => 'required|string'
        ]);

        // 2. Buscamos en el Cache si hay algún usuario usando este tacho AHORA MISMO
        $id_usuario = Cache::get('sesion_tacho_' . $request->tacho_id);

        if (!$id_usuario) {
            // Nadie escaneó el tacho, o pasaron los 60 segundos
            return response()->json([
                'status' => 'error',
                'message' => 'No hay una sesión activa en este tacho. Por favor, escanea el QR primero.'
            ], 403);
        }

        // Buscamos al usuario en la BD usando el ID que sacamos del Cache
        $user = User::find($id_usuario);

        // 3. CONSULTAMOS LA BASE DE DATOS PARA VERIFICAR EL MATERIAL Y PUNTOS
        $material = strtolower($request->material);
        $tipoResiduo = \App\Models\WasteType::where('nombre_residuo', $material)->first();

        if (!$tipoResiduo) {
            return response()->json([
                'status' => 'error',
                'message' => 'Material no reconocido en la base de datos'
            ], 400);
        }

        $puntosGanados = $tipoResiduo->puntos_otorgados;
        $id_tipo_residuo = $tipoResiduo->id_tipo_residuo;

        // 4. NUEVO SISTEMA (Ledger): Registramos la ganancia de puntos en transacciones_puntos
        \App\Models\PointTransaction::create([
            'id_usuario' => $user->id_usuario,
            'tipo_movimiento' => 'INGRESO',
            'monto' => $puntosGanados,
            'motivo' => 'Reciclaje de ' . $material . ' en Tacho #' . $request->tacho_id
        ]);

        // 5. Guardamos en tu nueva tabla historial_reciclaje (V2)
        \App\Models\RecyclingHistory::create([
            'id_usuario' => $user->id_usuario,
            'id_tacho' => $request->tacho_id,
            'id_tipo_residuo' => $id_tipo_residuo,
        ]);

        // 6. ¡Reiniciamos los 60 segundos! Por si el usuario quiere botar otra cosa
        Cache::put('sesion_tacho_' . $request->tacho_id, $user->id_usuario, 60);

        // 7. Respuesta para el tacho
        return response()->json([
            'status' => 'success',
            'message' => "¡Reciclaje exitoso!",
            'data' => [
                'usuario' => $user->nombre,
                'material_recibido' => $material,
                'puntos_sumados' => $puntosGanados,
                'total_actual' => $user->puntos // Usa nuestro calculador automático
            ]
        ]);
    }

    /**
     * PASO 2.C: OBTENER PIN (Usado por el Tacho/ESP32)
     * El tacho hace un GET a esta ruta cada 5 segundos para saber qué PIN dibujar en su pantalla.
     */
    public function obtenerPin($id)
    {
        $tacho = Bin::find($id);

        if (!$tacho) {
            return response()->json(['status' => 'error', 'message' => 'Tacho no encontrado'], 404);
        }

        return response()->json([
            'status' => 'success',
            'tacho_id' => $tacho->id_tacho,
            'pin_actual' => $tacho->pin_actual
        ]);
    }
}