<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;


    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }


    public function index(Request $request)
    {
        $user = $request->user();

        $metricas = $this->dashboardService->getUserMetrics($user->id_usuario);
        $historial = $this->dashboardService->getRecentHistory($user->id_usuario);

        return response()->json([
            'metricas' => $metricas,
            'historial' => $historial
        ]);
    }
}
