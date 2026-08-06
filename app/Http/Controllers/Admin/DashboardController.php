<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $data = $this->dashboardService->getDashboardData($request);
        return view('admin.dashboard', $data);
    }

    public function stats(Request $request)
    {
        $stats = $this->dashboardService->getDashboardData($request, true);
        return response()->json($stats);
    }
}
