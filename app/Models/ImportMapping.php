<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_id',
        'source_column',
        'target_field',
        'transformation_rule',
    ];

    protected $casts = [
        'transformation_rule' => 'array',
    ];
}


