<?php

namespace App\Services\Contracts;

interface RecycleServiceInterface
{
    public function linkBin(string $pin, string $token);
    public function processRecycle(int $tachoId, string $material);
}
