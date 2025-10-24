<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'name', 'sector', 'base_currency', 'fiscal_year_start'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function periods()
    {
        return $this->hasMany(Period::class);
    }
}


