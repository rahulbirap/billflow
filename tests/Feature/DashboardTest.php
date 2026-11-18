<?php

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

it('summarises the tenant\'s invoices', function () {
    $tenant = actingAsRole(UserRole::Viewer)->tenant;

    Invoice::factory()->for($tenant)->create([
        'status' => InvoiceStatus::Issued,
        'total_paise' => 100_000,
        'amount_paid_paise' => 0,
        'due_date' => now()->addDays(10),
    ]);
    Invoice::factory()->for($tenant)->create([
        'status' => InvoiceStatus::PartiallyPaid,
        'total_paise' => 50_000,
        'amount_paid_paise' => 20_000,
        'due_date' => now()->subDay(),
    ]);
    Invoice::factory()->for($tenant)->create([
        'status' => InvoiceStatus::Paid,
        'total_paise' => 30_000,
        'amount_paid_paise' => 30_000,
    ]);
    Invoice::factory()->for($tenant)->create([
        'status' => InvoiceStatus::Cancelled,
        'total_paise' => 99_999,
        'amount_paid_paise' => 0,
    ]);

    $this->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertExactJson(['data' => [
            'invoice_count' => 4,
            'outstanding_paise' => 130_000,
            'collected_paise' => 50_000,
            'overdue_count' => 1,
        ]]);
});

it('serves the dashboard from cache', function () {
    $user = actingAsRole(UserRole::Viewer);
    Invoice::factory()->for($user->tenant)->create();

    $this->getJson('/api/v1/dashboard')->assertJsonPath('data.invoice_count', 1);

    // A raw insert skips Eloquent events, so the cache is not cleared.
    DB::table('invoices')->insert([
        'tenant_id' => $user->tenant_id,
        'invoice_number' => 'RAW-1',
        'customer_name' => 'Raw Insert Ltd',
        'customer_state_code' => '27',
        'subtotal_paise' => 1_000,
        'total_paise' => 1_000,
        'status' => InvoiceStatus::Issued->value,
        'due_date' => now()->addDays(5)->toDateString(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson('/api/v1/dashboard')->assertJsonPath('data.invoice_count', 1);
});

it('clears the cached dashboard when an invoice changes', function () {
    $user = actingAsRole(UserRole::Viewer);
    Invoice::factory()->for($user->tenant)->create();

    $this->getJson('/api/v1/dashboard')->assertJsonPath('data.invoice_count', 1);

    Invoice::factory()->for($user->tenant)->create();

    $this->getJson('/api/v1/dashboard')->assertJsonPath('data.invoice_count', 2);
});

it('never shares a cached dashboard between tenants', function () {
    $userA = actingAsRole(UserRole::Viewer);
    Invoice::factory()->count(3)->for($userA->tenant)->create();

    $this->getJson('/api/v1/dashboard')->assertJsonPath('data.invoice_count', 3);

    actingAsRole(UserRole::Viewer); // a user from a different tenant

    $this->getJson('/api/v1/dashboard')->assertJsonPath('data.invoice_count', 0);
});