<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Import;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportService
{
    /**
     * Avvia un import CSV/XLSX: salva file, crea record e ritorna Import.
     *
     * @param array{company_id:int, file:UploadedFile, mapping?:array} $data
     */
    public function initiateImport(array $data): Import
    {
        $user = Auth::user();
        $companyId = (int) $data['company_id'];
        /** @var UploadedFile $file */
        $file = $data['file'];

        $tenantId = (int) ($user?->team_id ?? 0);
        $uuid = (string) Str::uuid();
        $path = sprintf('imports/%d/%s/%s', $tenantId, $companyId, $uuid);

        $storedPath = $file->store($path);

        $import = Import::create([
            'tenant_id' => $tenantId,
            'company_id' => $companyId,
            'user_id' => (int) $user->id,
            'type' => strtoupper($file->getClientOriginalExtension()) === 'XLSX' ? 'XLSX' : 'CSV',
            'file_path' => $storedPath,
            'original_filename' => $file->getClientOriginalName(),
            'hash_sha256' => hash_file('sha256', Storage::path($storedPath)),
            'status' => 'pending',
            'metadata' => [
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ],
        ]);

        return $import;
    }
}


