<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Observers\InvoiceObserver;
use App\Support\Tenancy\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(InvoiceObserver::class)]
class Invoice extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'customer_gstin',
        'customer_state_code',
        'subtotal_paise',
        'cgst_paise',
        'sgst_paise',
        'igst_paise',
        'total_paise',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'due_date' => 'date',
        ];
    }
}
