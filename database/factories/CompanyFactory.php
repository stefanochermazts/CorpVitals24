<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $companyTypes = ['S.r.l.', 'S.p.A.', 'S.n.c.', 'S.a.s.'];
        $companyName = $this->faker->company() . ' ' . $this->faker->randomElement($companyTypes);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $companyName,
            'sector' => $this->faker->randomElement(['Manifattura', 'Servizi', 'Commercio', 'Tecnologia', 'Consulenza']),
            'base_currency' => 'EUR',
            'fiscal_year_start' => 1,
        ];
    }
}
