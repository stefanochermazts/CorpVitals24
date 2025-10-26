<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiValue extends Model
{
    use HasFactory;

    protected $fillable = ['period_id', 'kpi_id', 'value', 'unit', 'state', 'provenance_json'];
    protected $casts = [
        'provenance_json' => 'array',
    ];

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }
}


