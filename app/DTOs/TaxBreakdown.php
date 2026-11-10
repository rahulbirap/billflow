<?php

namespace App\DTOs;

final readonly class TaxBreakdown
{
    public function __construct(
        public int $cgstPaise,
        public int $sgstPaise,
        public int $igstPaise,
    ) {}

    public function totalTaxPaise(): int
    {
        return $this->cgstPaise + $this->sgstPaise + $this->igstPaise;
    }
}
