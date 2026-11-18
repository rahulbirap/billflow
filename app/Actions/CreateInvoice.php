<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Services\Gst\GstCalculator;
use App\Support\Tenancy\TenantContext;

final class CreateInvoice
{
    public function __construct(
        private GstCalculator $gst,
        private TenantContext $tenantContext,
    ) {}

    public function handle(array $data): Invoice
    {
        $tenant = $this->tenantContext->get();

        $tax = $this->gst->calculate(
            supplierStateCode: $tenant->state_code,
            customerStateCode: $data['customer_state_code'],
            subtotalPaise: $data['subtotal_paise'],
            rateBasisPoints: $data['gst_rate_percent'] * 100,
        );

        $invoice = new Invoice([
            'invoice_number' => $data['invoice_number'],
            'customer_name' => $data['customer_name'],
            'customer_gstin' => $data['customer_gstin'] ?? null,
            'customer_state_code' => $data['customer_state_code'],
            'subtotal_paise' => $data['subtotal_paise'],
            'cgst_paise' => $tax->cgstPaise,
            'sgst_paise' => $tax->sgstPaise,
            'igst_paise' => $tax->igstPaise,
            'total_paise' => $data['subtotal_paise'] + $tax->totalTaxPaise(),
            'due_date' => $data['due_date'],
        ]);

        $invoice->status = InvoiceStatus::Issued;
        $invoice->save();

        return $invoice;
    }
}
