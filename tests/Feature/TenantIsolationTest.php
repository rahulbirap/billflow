<?php

use App\Models\Invoice;
use App\Models\Tenant;
use App\Support\Tenancy\TenantContext;

it('only returns invoices for the current tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    Invoice::factory()->count(3)->for($tenantA)->create();
    Invoice::factory()->count(2)->for($tenantB)->create();

    app(TenantContext::class)->set($tenantA);

    expect(Invoice::count())->toBe(3)
        ->and(Invoice::pluck('tenant_id')->unique()->all())->toBe([$tenantA->id]);
});

it('returns no invoices when no tenant is set', function () {
    Invoice::factory()->count(2)->create();

    expect(Invoice::count())->toBe(0);
});

it('assigns the current tenant when an invoice is created', function () {
    $tenant = Tenant::factory()->create();
    app(TenantContext::class)->set($tenant);

    $invoice = Invoice::factory()->make(['tenant_id' => null]);
    $invoice->save();

    expect($invoice->tenant_id)->toBe($tenant->id);
});

it('cannot load another tenant\'s invoice by id', function () {
    $tenantA = Tenant::factory()->create();
    $otherInvoice = Invoice::factory()->create();

    app(TenantContext::class)->set($tenantA);

    expect(Invoice::find($otherInvoice->id))->toBeNull();
});
