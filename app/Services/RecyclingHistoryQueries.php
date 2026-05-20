<?php

namespace App\Services;

use App\Models\RecyclingHistory;
use App\Services\Contracts\RecyclingHistoryQueriesInterface;
use Carbon\Carbon;

class RecyclingHistoryQueries implements RecyclingHistoryQueriesInterface
{
    public function countByWasteType(int $userId, string $wasteType): int
    {
        return RecyclingHistory::where('id_usuario', $userId)
            ->whereHas('wasteType', function ($query) use ($wasteType) {
                $query->where('nombre_residuo', $wasteType);
            })->count();
    }

    public function countActiveDays(int $userId): int
    {
        return RecyclingHistory::where('id_usuario', $userId)
            ->select('fecha_hora')
            ->get()
            ->pluck('fecha_hora')
            ->map(function ($fecha) {
                return Carbon::parse($fecha)->format('Y-m-d');
            })
            ->unique()
            ->count();
    }

    public function getRecentHistory(int $userId, int $limit = 10)
    {
        return RecyclingHistory::with('wasteType')
            ->where('id_usuario', $userId)
            ->orderBy('fecha_hora', 'desc')
            ->limit($limit)
            ->get();
    }
}
