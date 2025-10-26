<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface KpiRepositoryInterface
{
    /**
     * Get latest KPI values for a specific company
     *
     * @param int $companyId
     * @param int $limit
     * @return Collection
     */
    public function getLatestKpiValuesForCompany(int $companyId, int $limit = 10): Collection;

    /**
     * Get KPI trend for a specific KPI code and company over multiple periods
     *
     * @param int $companyId
     * @param string $kpiCode
     * @param int $periodsCount
     * @return Collection
     */
    public function getKpiTrend(int $companyId, string $kpiCode, int $periodsCount = 6): Collection;

    /**
     * Get summary stats for a company (total KPIs, latest period, etc.)
     *
     * @param int $companyId
     * @return array
     */
    public function getCompanySummary(int $companyId): array;

    /**
     * Get all KPIs with their latest values for a company
     *
     * @param int $companyId
     * @return Collection
     */
    public function getAllKpisWithLatestValues(int $companyId): Collection;
}
