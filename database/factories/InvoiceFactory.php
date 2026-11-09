<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10_000, 10_000_000); // ₹100 to ₹1,00,000, in paise
        $cgst = intdiv($subtotal * 9, 100);
        $sgst = $cgst;

        return [
            'tenant_id' => Tenant::factory(),
            'invoice_number' => 'INV-'.fake()->unique()->numerify('#####'),
            'customer_name' => fake()->company(),
            'customer_gstin' => null,
            'customer_state_code' => '27',
            'subtotal_paise' => $subtotal,
            'cgst_paise' => $cgst,
            'sgst_paise' => $sgst,
            'igst_paise' => 0,
            'total_paise' => $subtotal + $cgst + $sgst,
            'status' => InvoiceStatus::Issued,
            'due_date' => now()->addDays(30),
        ];
    }
}
