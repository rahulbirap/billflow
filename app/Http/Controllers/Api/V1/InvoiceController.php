<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateInvoice;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Invoice::class);

        $perPage = min((int) $request->query('per_page', 20), 100);

        $invoices = Invoice::query()
            ->orderByDesc('id')
            ->cursorPaginate($perPage);

        return InvoiceResource::collection($invoices);
    }

    public function store(StoreInvoiceRequest $request, CreateInvoice $createInvoice)
    {
        $invoice = $createInvoice->handle($request->validated());

        return InvoiceResource::make($invoice)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        return InvoiceResource::make($invoice);
    }
}
