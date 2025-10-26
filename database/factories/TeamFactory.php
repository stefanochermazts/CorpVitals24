<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
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
        ];
    }
}
