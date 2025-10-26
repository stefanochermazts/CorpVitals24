<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use App\Repositories\Contracts\KpiRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KpiRepository implements KpiRepositoryInterface
{
    /**
     * Get latest KPI values for a specific company
     *
     * @param int $companyId
     * @param int $limit
     * @return Collection
     */
    public function getLatestKpiValuesForCompany(int $companyId, int $limit = 10): Collection
    {
        // Get latest period for this company
        $latestPeriod = Period::where('company_id', $companyId)
            ->orderBy('start', 'desc')
            ->first();

        if (!$latestPeriod) {
            return collect([]);
        }

        // Get KPI values for latest period with eager loading
        return KpiValue::with('kpi')
            ->where('period_id', $latestPeriod->id)
            ->limit($limit)
            ->get()
            ->map(function ($kpiValue) {
                $formulaRefs = $kpiValue->kpi->formula_refs ?? [];
                
                return [
                    'id' => $kpiValue->kpi->id,
                    'code' => $kpiValue->kpi->code,
                    'name' => $kpiValue->kpi->name,
                    'value' => $kpiValue->value,
                    'unit' => $formulaRefs['unit'] ?? $kpiValue->unit,
                    'display_format' => $formulaRefs['display_format'] ?? 'number',
                ];
            });
    }

    /**
     * Get KPI trend for a specific KPI code and company over multiple periods
     *
     * @param int $companyId
     * @param string $kpiCode
     * @param int $periodsCount
     * @return Collection
     */
    public function getKpiTrend(int $companyId, string $kpiCode, int $periodsCount = 6): Collection
    {
        $kpi = Kpi::where('code', $kpiCode)->first();

        if (!$kpi) {
            return collect([]);
        }

        // Get last N periods for this company
        $periods = Period::where('company_id', $companyId)
            ->orderBy('start', 'desc')
            ->limit($periodsCount)
            ->get()
            ->reverse()
            ->values();

        $periodIds = $periods->pluck('id');

        // Get KPI values for these periods
        $kpiValues = KpiValue::whereIn('period_id', $periodIds)
            ->where('kpi_id', $kpi->id)
            ->get()
            ->keyBy('period_id');

        // Build trend data with period labels
        return $periods->map(function ($period) use ($kpiValues) {
            $kpiValue = $kpiValues->get($period->id);
            
            return [
                'period' => $period->kind === 'M' ? 
                    \Carbon\Carbon::parse($period->start)->format('M Y') : 
                    $period->start->format('Y-m-d'),
                'value' => $kpiValue ? $kpiValue->value : null,
            ];
        });
    }

    /**
     * Get summary stats for a company
     *
     * @param int $companyId
     * @return array
     */
    public function getCompanySummary(int $companyId): array
    {
        // Get latest period
        $latestPeriod = Period::where('company_id', $companyId)
            ->orderBy('start', 'desc')
            ->first();

        if (!$latestPeriod) {
            return [
                'total_kpis' => 0,
                'latest_period' => null,
                'kpi_values_count' => 0,
            ];
        }

        // Count KPI values for latest period
        $kpiValuesCount = KpiValue::where('period_id', $latestPeriod->id)->count();

        return [
            'total_kpis' => Kpi::count(),
            'latest_period' => [
                'id' => $latestPeriod->id,
                'name' => \Carbon\Carbon::parse($latestPeriod->start)->format('F Y'),
                'start' => $latestPeriod->start,
                'end' => $latestPeriod->end,
            ],
            'kpi_values_count' => $kpiValuesCount,
        ];
    }

    /**
     * Get all KPIs with their latest values for a company
     *
     * @param int $companyId
     * @return Collection
     */
    public function getAllKpisWithLatestValues(int $companyId): Collection
    {
        // Get latest period
        $latestPeriod = Period::where('company_id', $companyId)
            ->orderBy('start', 'desc')
            ->first();

        if (!$latestPeriod) {
            return collect([]);
        }

        // Get all KPIs with their values for latest period
        return Kpi::leftJoin('kpi_values', function ($join) use ($latestPeriod) {
            $join->on('kpis.id', '=', 'kpi_values.kpi_id')
                ->where('kpi_values.period_id', '=', $latestPeriod->id);
        })
        ->select([
            'kpis.id',
            'kpis.code',
            'kpis.name',
            'kpis.description',
            'kpis.formula_refs',
            'kpi_values.value',
            'kpi_values.unit',
        ])
        ->get()
        ->map(function ($kpi) {
            $formulaRefs = is_string($kpi->formula_refs) ? 
                json_decode($kpi->formula_refs, true) : 
                ($kpi->formula_refs ?? []);

            return [
                'id' => $kpi->id,
                'code' => $kpi->code,
                'name' => $kpi->name,
                'description' => $kpi->description,
                'value' => $kpi->value,
                'unit' => $formulaRefs['unit'] ?? $kpi->unit,
                'display_format' => $formulaRefs['display_format'] ?? 'number',
            ];
        });
    }
}
