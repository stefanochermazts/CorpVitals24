<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Period>
 */
class PeriodFactory extends Factory
{
    protected $model = Period::class;

    public function definition(): array
    {
        $startDate = Carbon::now()->subMonths($this->faker->numberBetween(1, 24))->startOfMonth();
        
        return [
            'company_id' => Company::factory(),
            'kind' => 'M',
            'start' => $startDate,
            'end' => $startDate->copy()->endOfMonth(),
            'currency' => 'EUR',
        ];
    }

    public function monthly(int $monthsAgo): self
    {
        return $this->state(function (array $attributes) use ($monthsAgo) {
            $startDate = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            
            return [
                'kind' => 'M',
                'start' => $startDate,
                'end' => $startDate->copy()->endOfMonth(),
            ];
        });
    }
}
