<?php

return [
    'cache_ttl_seconds' => env('KPI_CACHE_TTL', 60),
    'max_kpis_per_request' => env('KPI_MAX_CODES', 50),
    'max_import_size_mb' => env('KPI_MAX_IMPORT_MB', 10),
];


