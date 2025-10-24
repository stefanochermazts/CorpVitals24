<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description', 'formula_refs'];
    protected $casts = [
        'formula_refs' => 'array',
    ];

    public function values()
    {
        return $this->hasMany(KpiValue::class);
    }
}


