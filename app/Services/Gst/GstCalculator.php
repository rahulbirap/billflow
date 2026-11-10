<?php

namespace App\Services\Gst;

use App\DTOs\TaxBreakdown;

final class GstCalculator
{
    public function __construct(
        private IntraStateGst $intraState,
        private InterStateGst $interState,
    ) {}

    public function calculate(
        string $supplierStateCode,
        string $customerStateCode,
        int $subtotalPaise,
        int $rateBasisPoints,
    ): TaxBreakdown {
        $strategy = $supplierStateCode === $customerStateCode
            ? $this->intraState
            : $this->interState;

        return $strategy->calculate($subtotalPaise, $rateBasisPoints);
    }
}
