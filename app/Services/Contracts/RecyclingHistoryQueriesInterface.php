<?php

namespace App\Services\Contracts;

interface RecyclingHistoryQueriesInterface
{
    public function countByWasteType(int $userId, string $wasteType): int;
    public function countActiveDays(int $userId): int;
    public function getRecentHistory(int $userId, int $limit = 10);
}
