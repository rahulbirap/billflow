<?php

namespace App\Support;

final class Money
{
    public static function applyRate(int $amountPaise, int $basisPoints): int
    {
        // Integer maths with half-up rounding, so no floats are ever involved.
        return intdiv($amountPaise * $basisPoints + 5_000, 10_000);
    }
}
