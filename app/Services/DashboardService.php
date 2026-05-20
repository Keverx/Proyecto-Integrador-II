<?php

namespace App\Services;

use App\Services\Contracts\RecyclingHistoryQueriesInterface;
use App\Services\Contracts\HistoryCalculatorInterface;
use Carbon\Carbon;

class DashboardService
{
    private $historyQueries;
    private $historyCalculator;

    public function __construct(
        RecyclingHistoryQueriesInterface $historyQueries,
        HistoryCalculatorInterface $historyCalculator
    ) {
        $this->historyQueries = $historyQueries;
        $this->historyCalculator = $historyCalculator;
    }

    public function getUserMetrics(int $userId): array
    {
        $cantidadPlastico = $this->historyQueries->countByWasteType($userId, 'Plastico');
        $cantidadPapel = $this->historyQueries->countByWasteType($userId, 'Papel');
        $cantidadVidrio = $this->historyQueries->countByWasteType($userId, 'Vidrio');

        $rachaDiasActivos = $this->historyQueries->countActiveDays($userId);

        $totalObjetos = $cantidadPlastico + $cantidadPapel + $cantidadVidrio;
        $huellaCarbono = $this->historyCalculator->calculateCarbonFootprint($totalObjetos);

        return [
            'huella_carbono_ahorrada' => $huellaCarbono,
            'racha_dias_activos' => $rachaDiasActivos,
            'cantidad_plastico' => $cantidadPlastico,
            'cantidad_papel' => $cantidadPapel,
            'cantidad_vidrio' => $cantidadVidrio,
            'total_objetos' => $totalObjetos
        ];
    }

    public function getRecentHistory(int $userId): array
    {
        $historial = $this->historyQueries->getRecentHistory($userId, 10);

        return $historial->map(function ($item) {
            return [
                'id_registro' => $item->id_transaccion,
                'tipo_residuo' => $item->wasteType ? $item->wasteType->nombre_residuo : 'Desconocido',
                'cantidad' => 1,
                'puntos_ganados' => $item->wasteType ? $item->wasteType->puntos_otorgados : 0,
                'fecha_escaneo' => Carbon::parse($item->fecha_hora)->format('d/m/Y - h:i A'),
                'iso_date' => Carbon::parse($item->fecha_hora)->toIso8601String()
            ];
        })->toArray();
    }
}
