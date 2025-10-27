<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxonomyMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxonomy_id',
        'concept_qname',
        'valore_base_target',
        'sign_rule',
        'multiplier',
        'priority',
        'transformation_rules',
        'notes',
        'is_default',
    ];

    protected $casts = [
        'multiplier' => 'float',
        'priority' => 'int',
        'transformation_rules' => 'array',
        'is_default' => 'bool',
    ];
}


