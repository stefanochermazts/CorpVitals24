<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'kind', 'start', 'end', 'currency'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}


