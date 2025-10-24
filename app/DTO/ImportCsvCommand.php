<?php

namespace App\DTO;

final class ImportCsvCommand
{
    public function __construct(
        public readonly int $companyId,
        public readonly string $filePath,
        public readonly ?int $periodId = null,
    ) {
    }
}


