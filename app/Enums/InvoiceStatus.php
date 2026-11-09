<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function canAcceptPayment(): bool
    {
        return in_array($this, [self::Issued, self::PartiallyPaid], true);
    }
}
