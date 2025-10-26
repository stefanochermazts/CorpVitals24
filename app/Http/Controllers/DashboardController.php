<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
    }

    /**
     * Display the dashboard
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $summary = $this->dashboardService->getDashboardSummary($request->user());

        return Inertia::render('Dashboard/Index', [
            'summary' => $summary,
        ]);
    }

    /**
     * Get all KPIs for the authenticated user's company
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kpis(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $this->dashboardService->getAllKpis($request->user());

        return response()->json($data);
    }
}

