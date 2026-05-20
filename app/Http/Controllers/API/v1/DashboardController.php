<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $dashboardService;

    /**
     * Injecting the DashboardService (SOLID - Dependency Inversion Principle)
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get the dashboard metrics and recent history for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Single Responsibility Principle: Controller delegates to Service
        $metricas = $this->dashboardService->getUserMetrics($user->id_usuario);
        $historial = $this->dashboardService->getRecentHistory($user->id_usuario);

        return response()->json([
            'metricas' => $metricas,
            'historial' => $historial
        ]);
    }
}
