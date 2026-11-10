<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidGstin implements ValidationRule
{
    private const CHARSET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $gstin = strtoupper((string) $value);

        if (! preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/', $gstin)) {
            $fail('The :attribute must be a valid 15-character GSTIN.');

            return;
        }

        if ($gstin[14] !== self::checksumFor($gstin)) {
            $fail('The :attribute has an invalid checksum.');
        }
    }

    public static function checksumFor(string $gstin): string
    {
        $sum = 0;

        for ($i = 0; $i < 14; $i++) {
            $value = strpos(self::CHARSET, $gstin[$i]);
            $product = $value * ($i % 2 === 0 ? 1 : 2);
            $sum += intdiv($product, 36) + ($product % 36);
        }

        return self::CHARSET[(36 - ($sum % 36)) % 36];
    }
}
