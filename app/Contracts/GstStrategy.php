<?php

namespace App\Contracts;

use App\DTOs\TaxBreakdown;

interface GstStrategy
{
    public function calculate(int $subtotalPaise, int $rateBasisPoints): TaxBreakdown;
}
