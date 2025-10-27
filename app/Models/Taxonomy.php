<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxonomy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'version',
        'country',
        'base_standard',
        'taxonomy_url',
        'entry_point_url',
        'schema_refs',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'schema_refs' => 'array',
        'is_active' => 'bool',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];
}


