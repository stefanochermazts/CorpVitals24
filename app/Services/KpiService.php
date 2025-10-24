<?php

namespace App\Services;

use App\DTO\FetchKpiMetricsQuery;
use App\Repositories\Contracts\KpiRepositoryInterface;

class KpiService
{
    public function __construct(private readonly KpiRepositoryInterface $kpis)
    {
    }

    /**
     * @return array<int, array{code:string,value:float|null}>
     */
    public function getSnapshot(FetchKpiMetricsQuery $query): array
    {
        $codes = array_values(array_unique($query->kpiCodes));
        if (count($codes) > (int) config('kpi.max_kpis_per_request', 50)) {
            throw new \DomainException('Too many KPI codes');
        }
        $codeToId = $this->kpis->mapCodesToIds($codes);
        $valuesById = $this->kpis->snapshotValues($query->companyId, $query->periodId, array_values($codeToId));
        $result = [];
        foreach ($codes as $code) {
            $id = $codeToId[$code] ?? null;
            $result[] = [
                'code' => $code,
                'value' => $id ? ($valuesById[$id] ?? null) : null,
            ];
        }
        return $result;
    }
}


