<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'settings_json'];

    protected $casts = [
        'settings_json' => 'array',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}

