<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TenantDashboard;

class DashboardController extends Controller
{
    public function __invoke(TenantDashboard $dashboard)
    {
        return response()->json(['data' => $dashboard->stats()]);
    }
}