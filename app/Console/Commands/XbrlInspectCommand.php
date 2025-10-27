<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\XbrlParserService;
use Illuminate\Console\Command;

class XbrlInspectCommand extends Command
{
    protected $signature = 'xbrl:inspect {file : Path to XBRL/iXBRL file}';
    protected $description = 'Parse an XBRL file with Arelle and print a summary';

    public function handle(XbrlParserService $service): int
    {
        $file = (string) $this->argument('file');
        $this->info("Parsing: {$file}");
        $data = $service->parse($file);
        $facts = is_countable($data['facts']) ? count($data['facts']) : 0;
        $this->table(['Key', 'Value'], [
            ['facts', (string) $facts],
            ['contexts', (string) (is_countable($data['contexts']) ? count($data['contexts']) : 0)],
            ['units', (string) (is_countable($data['units']) ? count($data['units']) : 0)],
            ['file', $data['metadata']['file'] ?? ''],
        ]);
        return self::SUCCESS;
    }
}


