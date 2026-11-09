<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(private TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->user()?->tenant;

        abort_if($tenant === null, 403, 'No tenant is linked to this account.');

        $this->context->set($tenant);

        return $next($request);
    }
}
