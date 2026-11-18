<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'customer' => [
                'name' => $this->customer_name,
                'gstin' => $this->customer_gstin,
                'state_code' => $this->customer_state_code,
            ],
            'amounts' => [
                'subtotal_paise' => $this->subtotal_paise,
                'cgst_paise' => $this->cgst_paise,
                'sgst_paise' => $this->sgst_paise,
                'igst_paise' => $this->igst_paise,
                'total_paise' => $this->total_paise,
                'amount_paid_paise' => $this->amount_paid_paise,
                'balance_due_paise' => $this->total_paise - $this->amount_paid_paise,
            ],
            'status' => $this->status->value,
            'due_date' => $this->due_date->toDateString(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
