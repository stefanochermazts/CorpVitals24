<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'company_id',
        'user_id',
        'type',
        'file_path',
        'original_filename',
        'hash_sha256',
        'status',
        'started_at',
        'finished_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'metadata' => 'array',
    ];
}


