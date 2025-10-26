<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\KpiRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(
        private readonly KpiRepositoryInterface $kpiRepository
    ) {
    }

    /**
     * Get dashboard summary for authenticated user
     *
     * @param User $user
     * @return array
     */
    public function getDashboardSummary(User $user): array
    {
        // Cache key based on user company and current timestamp (5 min TTL)
        $cacheKey = sprintf(
            'dashboard:company:%d:user:%d',
            $user->company_id,
            $user->id
        );

        return Cache::remember($cacheKey, 300, function () use ($user) {
            // Get company summary stats
            $companySummary = $this->kpiRepository->getCompanySummary($user->company_id);

            // Get latest KPIs (top 6 for dashboard cards)
            $latestKpis = $this->kpiRepository->getLatestKpiValuesForCompany(
                $user->company_id,
                6
            );

            // Get trend data for main KPIs (Revenue, EBITDA, etc.)
            $mainKpiCodes = ['REV', 'EBITDA', 'MOL', 'ROI'];
            $trends = [];

            foreach ($mainKpiCodes as $code) {
                $trendData = $this->kpiRepository->getKpiTrend($user->company_id, $code, 6);
                
                if ($trendData->isNotEmpty()) {
                    $trends[$code] = $trendData;
                }
            }

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'team_id' => $user->team_id,
                    'company_id' => $user->company_id,
                ],
                'company_summary' => $companySummary,
                'latest_kpis' => $latestKpis,
                'kpi_trends' => $trends,
                'cached_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Get all KPIs with latest values for a user's company
     *
     * @param User $user
     * @return array
     */
    public function getAllKpis(User $user): array
    {
        $cacheKey = sprintf('kpis:all:company:%d', $user->company_id);

        $kpis = Cache::remember($cacheKey, 300, function () use ($user) {
            return $this->kpiRepository->getAllKpisWithLatestValues($user->company_id);
        });

        return [
            'kpis' => $kpis,
            'count' => $kpis->count(),
        ];
    }

    /**
     * Clear dashboard cache for a specific company
     *
     * @param int $companyId
     * @return void
     */
    public function clearCompanyCache(int $companyId): void
    {
        // Clear all caches related to this company
        Cache::tags(['dashboard', "company:{$companyId}"])->flush();
    }
}

