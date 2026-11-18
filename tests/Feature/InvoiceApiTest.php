<?php

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function actingAsRole(UserRole $role): User
{
    $user = User::factory()
        ->for(Tenant::factory()->create(['state_code' => '27']))
        ->create(['role' => $role]);

    Sanctum::actingAs($user);

    return $user;
}

function validInvoicePayload(array $overrides = []): array
{
    return array_merge([
        'invoice_number' => 'INV-1001',
        'customer_name' => 'Acme Traders',
        'customer_gstin' => '27AAPFU0939F1ZV',
        'customer_state_code' => '27',
        'subtotal_paise' => 100_000,
        'gst_rate_percent' => 18,
        'due_date' => now()->addDays(15)->toDateString(),
    ], $overrides);
}

it('issues a token for valid credentials', function () {
    $user = User::factory()->create(['password' => 'secret-password']);

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'secret-password',
    ])->assertOk()->assertJsonStructure(['token']);
});

it('gives the same error for a wrong password and an unknown email', function () {
    $user = User::factory()->create();

    $wrongPassword = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $unknownEmail = $this->postJson('/api/v1/login', [
        'email' => 'nobody@example.com',
        'password' => 'wrong-password',
    ]);

    $wrongPassword->assertUnprocessable();
    expect($wrongPassword->json('message'))->toBe($unknownEmail->json('message'));
});

it('creates an invoice and calculates GST on the server', function () {
    actingAsRole(UserRole::Accountant);

    $this->postJson('/api/v1/invoices', validInvoicePayload())
        ->assertCreated()
        ->assertJsonPath('data.amounts.cgst_paise', 9_000)
        ->assertJsonPath('data.amounts.sgst_paise', 9_000)
        ->assertJsonPath('data.amounts.total_paise', 118_000)
        ->assertJsonPath('data.status', 'issued');
});

it('ignores totals and status sent by the client', function () {
    actingAsRole(UserRole::Accountant);

    $this->postJson('/api/v1/invoices', validInvoicePayload([
        'total_paise' => 1,
        'status' => 'paid',
    ]))
        ->assertCreated()
        ->assertJsonPath('data.amounts.total_paise', 118_000)
        ->assertJsonPath('data.status', 'issued');
});

it('forbids viewers from creating invoices', function () {
    actingAsRole(UserRole::Viewer);

    $this->postJson('/api/v1/invoices', validInvoicePayload())->assertForbidden();
});

it('rejects a GSTIN from a different state than the customer', function () {
    actingAsRole(UserRole::Accountant);

    $this->postJson('/api/v1/invoices', validInvoicePayload(['customer_state_code' => '29']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('customer_gstin');
});

it('returns 404 for another tenant\'s invoice', function () {
    actingAsRole(UserRole::Owner);
    $otherInvoice = Invoice::factory()->create();

    $this->getJson("/api/v1/invoices/{$otherInvoice->id}")->assertNotFound();
});

it('shows an invoice to a user of the same tenant', function () {
    $user = actingAsRole(UserRole::Viewer);
    $invoice = Invoice::factory()->for($user->tenant)->create();

    $this->getJson("/api/v1/invoices/{$invoice->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $invoice->id);
});

it('paginates invoices with a cursor', function () {
    $user = actingAsRole(UserRole::Viewer);
    Invoice::factory()->count(3)->for($user->tenant)->create();

    $firstPage = $this->getJson('/api/v1/invoices?per_page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->getJson('/api/v1/invoices?per_page=2&cursor='.$firstPage->json('meta.next_cursor'))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});
