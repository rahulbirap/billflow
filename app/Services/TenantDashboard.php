<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Cache;

final class TenantDashboard
{
    public const TTL_SECONDS = 600;

    public function __construct(private TenantContext $tenantContext) {}

    public static function cacheKey(int $tenantId): string
    {
        return "tenant:{$tenantId}:dashboard";
    }

    public function stats(): array
    {
        return Cache::remember(
            self::cacheKey($this->tenantContext->id()),
            self::TTL_SECONDS,
            fn () => $this->calculate(),
        );
    }

    private function calculate(): array
    {
        $open = [InvoiceStatus::Issued->value, InvoiceStatus::PartiallyPaid->value];

        $row = Invoice::query()
            ->toBase()
            ->selectRaw('COUNT(*) AS invoice_count')
            ->selectRaw(
                'SUM(CASE WHEN status IN (?, ?) THEN CAST(total_paise AS SIGNED) - CAST(amount_paid_paise AS SIGNED) ELSE 0 END) AS outstanding_paise',
                $open,
            )
            ->selectRaw('SUM(amount_paid_paise) AS collected_paise')
            ->selectRaw(
                'SUM(CASE WHEN status IN (?, ?) AND due_date < ? THEN 1 ELSE 0 END) AS overdue_count',
                [...$open, now()->toDateString()],
            )
            ->first();

        return [
            'invoice_count' => (int) $row->invoice_count,
            'outstanding_paise' => (int) $row->outstanding_paise,
            'collected_paise' => (int) $row->collected_paise,
            'overdue_count' => (int) $row->overdue_count,
        ];
    }
}
