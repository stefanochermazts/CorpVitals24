<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $companyTypes = [
            'Studio Commercialista',
            'Società di Consulenza',
            'Incubatore Startup',
            'Private Equity',
            'Business Advisors',
        ];

        $name = $this->faker->randomElement($companyTypes) . ' ' . $this->faker->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'settings_json' => [
                'default_currency' => 'EUR',
                'fiscal_year_start' => 1,
            ],
        ];
    }
}
