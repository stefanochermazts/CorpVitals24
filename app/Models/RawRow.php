<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_id',
        'row_number',
        'data',
        'validated',
        'errors',
    ];

    protected $casts = [
        'data' => 'array',
        'validated' => 'bool',
        'errors' => 'array',
    ];
}


