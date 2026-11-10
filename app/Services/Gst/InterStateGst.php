<?php

namespace App\Services\Gst;

use App\Contracts\GstStrategy;
use App\DTOs\TaxBreakdown;
use App\Support\Money;

final class InterStateGst implements GstStrategy
{
    public function calculate(int $subtotalPaise, int $rateBasisPoints): TaxBreakdown
    {
        return new TaxBreakdown(
            cgstPaise: 0,
            sgstPaise: 0,
            igstPaise: Money::applyRate($subtotalPaise, $rateBasisPoints),
        );
    }
}
