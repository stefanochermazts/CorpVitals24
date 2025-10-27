<?php

return [
    'arelle_path' => env('ARELLE_PATH', '/opt/Arelle/arelleCmdLine.py'),
    'parse_timeout' => env('XBRL_PARSE_TIMEOUT', 300),
    'supported_extensions' => ['xbrl', 'ixbrl', 'xml', 'xhtml', 'html'],
    'max_file_size' => env('XBRL_MAX_FILE_SIZE', 50 * 1024 * 1024),
    'taxonomy_cache_ttl' => env('XBRL_TAXONOMY_CACHE_TTL', 86400),
    // mode: cli (python3 arelleCmdLine.py) | docker (docker exec arelle ...)
    'mode' => env('XBRL_MODE', 'cli'),
];


