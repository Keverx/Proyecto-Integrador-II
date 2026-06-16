<?php

namespace App\Services;

use App\Models\User;
use App\Models\Bin;
use App\Models\WasteType;
use App\Models\PointTransaction;
use App\Models\RecyclingHistory;
use Illuminate\Support\Facades\Cache;
use App\Services\Contracts\RecycleServiceInterface;
use Exception;

class RecycleService implements RecycleServiceInterface
{
    private const SESSION_TTL = 60;

    public function linkBin(string $pin, int $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            throw new Exception('Usuario no encontrado', 404);
        }

        $tacho = Bin::where('pin_actual', $pin)->first();
        if (!$tacho) {
            throw new Exception('Código QR inválido o ya usado', 400);
        }

        Cache::put('sesion_tacho_' . $tacho->id_tacho, $user->id_usuario, self::SESSION_TTL);
        
        // Limpiamos el historial de reciclajes de la última sesión.
        // Usamos Cache::forever para que la lista persista incluso
        // si el usuario cierra la aplicación o cierra sesión en el dispositivo móvil.
        // Solo se borra/formatea cuando el usuario vuelve a escanear un código QR.
        Cache::forever('sesion_items_' . $user->id_usuario, []);

        $nuevoPin = rand(1000, 9999);
        $tacho->pin_actual = (string)$nuevoPin;
        $tacho->save();

        return true;
    }

    public function processRecycle(int $tachoId, string $material)
    {
        $id_usuario = Cache::get('sesion_tacho_' . $tachoId);

        if (!$id_usuario) {
            throw new Exception('No hay una sesión activa en este tacho. Por favor, escanea el QR primero.', 403);
        }

        $user = User::find($id_usuario);
        $materialClean = strtolower($material);
        $tipoResiduo = WasteType::where('nombre_residuo', $materialClean)->first();

        if (!$tipoResiduo) {
            throw new Exception('Material no reconocido en la base de datos', 400);
        }

        $puntosGanados = $tipoResiduo->puntos_otorgados;
        $id_tipo_residuo = $tipoResiduo->id_tipo_residuo;

        PointTransaction::create([
            'id_usuario' => $user->id_usuario,
            'tipo_movimiento' => 'INGRESO',
            'monto' => $puntosGanados,
            'motivo' => 'Reciclaje de ' . $materialClean . ' en Tacho #' . $tachoId
        ]);

        RecyclingHistory::create([
            'id_usuario' => $user->id_usuario,
            'id_tacho' => $tachoId,
            'id_tipo_residuo' => $id_tipo_residuo,
        ]);

        Cache::put('sesion_tacho_' . $tachoId, $user->id_usuario, self::SESSION_TTL);

        // Guardamos el material en la lista temporal de la sesión actual
        $items = Cache::get('sesion_items_' . $user->id_usuario, []);
        // Insertamos al inicio (unshift) para que el más reciente salga primero
        array_unshift($items, [
            'material' => ucfirst($materialClean),
            'puntos' => $puntosGanados,
            'hora' => now()->format('H:i:s')
        ]);
        Cache::forever('sesion_items_' . $user->id_usuario, $items);

        return [
            'usuario' => $user->nombre,
            'material_recibido' => $materialClean,
            'puntos_sumados' => $puntosGanados,
            'total_actual' => $user->puntos
        ];
    }
}
