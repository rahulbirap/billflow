<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\TenantDashboard;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Cache;

class InvoiceObserver implements ShouldHandleEventsAfterCommit
{
    public function saved(Invoice $invoice): void
    {
        $this->forgetDashboard($invoice);
    }

    public function deleted(Invoice $invoice): void
    {
        $this->forgetDashboard($invoice);
    }

    private function forgetDashboard(Invoice $invoice): void
    {
        Cache::forget(TenantDashboard::cacheKey($invoice->tenant_id));
    }
}
