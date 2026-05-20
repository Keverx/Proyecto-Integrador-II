<?php

namespace App\Services\Contracts;

interface HistoryCalculatorInterface
{
    public function calculateCarbonFootprint(int $itemCount): float; //co2 ahorrado
}
