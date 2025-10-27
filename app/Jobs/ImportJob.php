<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $importId) {}

    public function handle(): void
    {
        $import = Import::find($this->importId);
        if (!$import) {
            return;
        }
        // Placeholder: processamento asincrono CSV/XLSX (parsing chunked)
        $import->update([
            'status' => 'completed',
            'finished_at' => now(),
        ]);
    }
}


