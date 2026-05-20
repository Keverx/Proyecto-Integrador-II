<?php

namespace App\Services\Contracts;

interface RecycleServiceInterface
{
    public function linkBin(string $pin, int $userId);
    public function processRecycle(int $tachoId, string $material);
}
