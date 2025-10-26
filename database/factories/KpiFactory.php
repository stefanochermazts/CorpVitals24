<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Kpi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kpi>
 */
class KpiFactory extends Factory
{
    protected $model = Kpi::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'formula_refs' => [
                'formula' => 'custom_calculation',
                'display_format' => 'number',
            ],
        ];
    }
}
