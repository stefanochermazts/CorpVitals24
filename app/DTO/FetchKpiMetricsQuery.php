<?php

namespace App\DTO;

final class FetchKpiMetricsQuery
{
    public function __construct(
        public readonly int $companyId,
        public readonly int $periodId,
        /** @var list<string> */
        public readonly array $kpiCodes,
    ) {
    }
}


