<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Import\StoreImportRequest;
use App\Jobs\ImportJob;
use App\Models\Import;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;

class ImportController extends Controller
{
    public function __construct(private ImportService $service) {}

    public function store(StoreImportRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['file'] = $request->file('file');

        $import = $this->service->initiateImport($data);

        ImportJob::dispatch($import->id)->onQueue('imports');

        return response()->json([
            'id' => $import->id,
            'status' => $import->status,
        ], 202);
    }

    public function show(int $id): JsonResponse
    {
        $import = Import::findOrFail($id);

        return response()->json([
            'id' => $import->id,
            'status' => $import->status,
            'error' => $import->error_message,
            'started_at' => $import->started_at,
            'finished_at' => $import->finished_at,
        ]);
    }
}


