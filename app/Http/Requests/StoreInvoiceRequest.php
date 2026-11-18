<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use App\Rules\ValidGstin;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('customer_gstin')) {
            $this->merge(['customer_gstin' => strtoupper($this->input('customer_gstin'))]);
        }
    }

    public function rules(): array
    {
        return [
            'invoice_number' => [
                'required', 'string', 'max:50',
                Rule::unique('invoices')->where('tenant_id', app(TenantContext::class)->id()),
            ],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_gstin' => ['nullable', new ValidGstin],
            'customer_state_code' => ['required', 'digits:2'],
            'subtotal_paise' => ['required', 'integer', 'min:1'],
            'gst_rate_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $gstin = $this->input('customer_gstin');

                if ($gstin && substr($gstin, 0, 2) !== $this->input('customer_state_code')) {
                    $validator->errors()->add(
                        'customer_gstin',
                        'The GSTIN state code does not match the customer state code.'
                    );
                }
            },
        ];
    }
}
