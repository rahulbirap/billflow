<?php

namespace App\Services\Gst;

use App\Contracts\GstStrategy;
use App\DTOs\TaxBreakdown;
use App\Support\Money;

final class IntraStateGst implements GstStrategy
{
    public function calculate(int $subtotalPaise, int $rateBasisPoints): TaxBreakdown
    {
        $half = Money::applyRate($subtotalPaise, intdiv($rateBasisPoints, 2));

        return new TaxBreakdown(cgstPaise: $half, sgstPaise: $half, igstPaise: 0);
    }
}
