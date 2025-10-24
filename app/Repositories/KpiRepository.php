<?php

namespace App\Repositories;

use App\Models\Kpi;
use App\Models\KpiValue;
use App\Repositories\Contracts\KpiRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class KpiRepository implements KpiRepositoryInterface
{
    public function findIdByCode(string $code): ?int
    {
        $kpi = Kpi::query()->select('id')->where('code', $code)->first();
        return $kpi?->id;
    }

    public function mapCodesToIds(array $codes): array
    {
        return Kpi::query()->whereIn('code', $codes)->pluck('id', 'code')->all();
    }

    public function snapshotValues(int $companyId, int $periodId, array $kpiIds): array
    {
        $cacheKey = "kpi:snapshot:{$companyId}:{$periodId}:".md5(implode(',', $kpiIds));
        return Cache::remember($cacheKey, (int) config('kpi.cache_ttl_seconds', 60), function () use ($companyId, $periodId, $kpiIds) {
            $rows = KpiValue::query()
                ->select(['kpi_id', 'value'])
                ->where('period_id', $periodId)
                ->whereIn('kpi_id', $kpiIds)
                ->get();
            $out = [];
            foreach ($rows as $row) {
                $out[(int) $row->kpi_id] = $row->value === null ? null : (float) $row->value;
            }
            return $out;
        });
    }
}


