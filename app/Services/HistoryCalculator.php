<?php

namespace App\Services;

use App\Services\Contracts\HistoryCalculatorInterface;

class HistoryCalculator implements HistoryCalculatorInterface
{
    private const CO2_MULTIPLIER = 0.5;

    public function calculateCarbonFootprint(int $itemCount): float
    {
        return $itemCount * self::CO2_MULTIPLIER;
    }
}
