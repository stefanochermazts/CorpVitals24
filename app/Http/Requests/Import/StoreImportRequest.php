<?php

declare(strict_types=1);

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('import-data') ?? false;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'file' => ['required', 'file', 'max:102400', 'mimetypes:text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'mapping' => ['sometimes', 'array'],
        ];
    }
}


