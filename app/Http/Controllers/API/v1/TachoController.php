<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Contracts\RecyclingHistoryQueriesInterface;
use Carbon\Carbon;

class TachoController extends Controller
{
    protected $historyQueries;

    public function __construct(RecyclingHistoryQueriesInterface $historyQueries)
    {
        $this->historyQueries = $historyQueries;
    }

    public function status(Request $request)
    {
        $user = $request->user();
        
        $metricas = [
            'eco_puntos_personales' => $user->puntos,
            'racha_dias_activos' => $this->historyQueries->countActiveDays($user->id_usuario)
        ];

        // Buscar el primer grupo del usuario y su primer tacho
        $grupo = $user->grupos()->with('tachos.nivelesLlenado.tipoResiduo')->first();
        
        $tachoData = null;

        if ($grupo && $grupo->tachos->isNotEmpty()) {
            $tacho = $grupo->tachos->first();
            
            $niveles = [
                'plastico' => 0,
                'papel' => 0,
                'organico' => 0,
                'vidrio' => 0,
            ];

            foreach ($tacho->nivelesLlenado as $nivel) {
                $nombreResiduo = strtolower($nivel->tipoResiduo->nombre_residuo ?? '');
                
                if (str_contains($nombreResiduo, 'plástico') || str_contains($nombreResiduo, 'plastico')) {
                    $niveles['plastico'] = (float)$nivel->porcentaje_llenado;
                } elseif (str_contains($nombreResiduo, 'papel') || str_contains($nombreResiduo, 'carton') || str_contains($nombreResiduo, 'cartón')) {
                    $niveles['papel'] = (float)$nivel->porcentaje_llenado;
                } elseif (str_contains($nombreResiduo, 'vidrio')) {
                    $niveles['vidrio'] = (float)$nivel->porcentaje_llenado;
                } elseif (str_contains($nombreResiduo, 'orgánico') || str_contains($nombreResiduo, 'organico')) {
                    $niveles['organico'] = (float)$nivel->porcentaje_llenado;
                }
            }

            // Formatear tiempo de última conexión
            $ultimaConexion = $tacho->ultima_conexion ? Carbon::parse($tacho->ultima_conexion)->diffForHumans() : 'Nunca';
            // Laravel 11's diffForHumans is in English by default unless configured, but we can do a simple translation or rely on Laravel's locale if set.
            $ultimaConexion = str_replace([' seconds ago', ' minutes ago', ' hours ago', ' days ago', ' months ago', ' years ago', '1 minute ago', '1 hour ago', '1 day ago'], 
                                          [' segundos', ' minutos', ' horas', ' días', ' meses', ' años', '1 minuto', '1 hora', '1 día'], 
                                          $ultimaConexion);
            if (str_starts_with($ultimaConexion, 'Never')) $ultimaConexion = 'Nunca';
            else if (!str_starts_with($ultimaConexion, 'Nunca') && !str_starts_with($ultimaConexion, 'Hace')) {
                $ultimaConexion = 'Hace ' . str_replace(' ago', '', $ultimaConexion);
            }

            $tachoData = [
                'codigo_qr' => $tacho->codigo_qr,
                'estado_operativo' => $tacho->estado_operativo,
                'nivel_llenado_plastico' => $niveles['plastico'],
                'nivel_llenado_papel' => $niveles['papel'],
                'nivel_llenado_organico' => $niveles['organico'],
                'nivel_llenado_vidrio' => $niveles['vidrio'],
                'ultima_conexion' => $ultimaConexion,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'tacho' => $tachoData,
                'metricas' => $metricas
            ]
        ]);
    }
}
