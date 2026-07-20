<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Incidencia;
use Illuminate\Support\Facades\Validator;

class IncidenciaController extends Controller
{
    /**
     * Muestra las incidencias del usuario autenticado.
     */
    public function index(Request $request)
    {
        $usuario = $request->user();
        
        $incidencias = Incidencia::where('id_usuario', $usuario->id_usuario)
            ->with('tacho')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $incidencias
        ]);
    }

    /**
     * Crea un nuevo ticket de incidencia.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_problema' => 'required|string|max:100',
            'descripcion' => 'required|string',
            'id_tacho' => 'nullable|exists:tachos,id_tacho'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = $request->user();

        $incidencia = Incidencia::create([
            'id_usuario' => $usuario->id_usuario,
            'id_tacho' => $request->id_tacho,
            'tipo_problema' => $request->tipo_problema,
            'descripcion' => $request->descripcion,
            'estado' => 'PENDIENTE'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'El problema ha sido reportado exitosamente. Soporte lo revisará pronto.',
            'data' => $incidencia
        ], 201);
    }
}
