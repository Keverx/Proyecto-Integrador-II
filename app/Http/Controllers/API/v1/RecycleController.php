<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LinkBinRequest;
use App\Http\Requests\ProcessRecycleRequest;
use App\Services\Contracts\RecycleServiceInterface;
use App\Models\Bin; // Solo para el método simple de obtenerPin
use Exception;

class RecycleController extends Controller
{
    protected $recycleService;

    // DIP: Inyección de Dependencias
    public function __construct(RecycleServiceInterface $recycleService)
    {
        $this->recycleService = $recycleService;
    }

    public function vincularTacho(LinkBinRequest $request)
    {
        try {
            // Extraemos el ID del usuario directamente de la sesión de Sanctum
            $userId = $request->user()->id_usuario;
            $this->recycleService->linkBin($request->pin_qr, $userId);
            return response()->json([
                'status' => 'success',
                'message' => '¡Tacho vinculado! Tienes 60 segundos para botar tu basura.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    public function procesarReciclaje(ProcessRecycleRequest $request)
    {
        try {
            $data = $this->recycleService->processRecycle($request->tacho_id, $request->material);
            return response()->json([
                'status' => 'success',
                'message' => "¡Reciclaje exitoso!",
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

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