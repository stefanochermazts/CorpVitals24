<?php

namespace App\Repositories\Contracts;

interface KpiRepositoryInterface
{
    public function findIdByCode(string $code): ?int;
    /** @return array<int,int> map code->id */
    public function mapCodesToIds(array $codes): array;
    /** @return array<string, float|null> */
    public function snapshotValues(int $companyId, int $periodId, array $kpiIds): array;
}


