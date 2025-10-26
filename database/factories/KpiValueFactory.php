<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KpiValue>
 */
class KpiValueFactory extends Factory
{
    protected $model = KpiValue::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'kpi_id' => Kpi::factory(),
            'period_id' => Period::factory(),
            'value' => $this->faker->randomFloat(2, -100000, 1000000),
            'provenance_json' => [
                'source' => 'seed',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
