<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea 3 team di esempio
        $teams = [
            [
                'name' => 'Studio Commercialista Rossi & Associati',
                'slug' => 'studio-rossi',
            ],
            [
                'name' => 'Consulenza Finanziaria Italia',
                'slug' => 'cfi',
            ],
            [
                'name' => 'Business Advisors Network',
                'slug' => 'ban',
            ],
        ];

        foreach ($teams as $teamData) {
            $team = Team::firstOrCreate(
                ['slug' => $teamData['slug']],
                ['name' => $teamData['name']]
            );

            // Crea 3-5 companies per team
            $companies = Company::factory()
                ->count(rand(3, 5))
                ->create(['team_id' => $team->id]);

            // Crea users per questo team
            $this->createUsersForTeam($team, $companies->first());

            // Crea KPI values per ogni company
            $this->createKpiValuesForCompanies($companies);

            $this->command->info("✅ Team '{$team->name}': {$companies->count()} aziende create");
        }
    }

    /**
     * Crea users per un team con diversi ruoli
     */
    private function createUsersForTeam(Team $team, Company $firstCompany): void
    {
        // Admin del team
        $admin = User::firstOrCreate(
            ['email' => strtolower($team->slug) . '-admin@example.com'],
            [
                'name' => 'Admin ' . $team->name,
                'password' => Hash::make('password'),
                'team_id' => $team->id,
                'company_id' => $firstCompany->id,
            ]
        );
        $admin->assignRole('admin');

        // Manager del team
        $manager = User::firstOrCreate(
            ['email' => strtolower($team->slug) . '-manager@example.com'],
            [
                'name' => 'Manager ' . $team->name,
                'password' => Hash::make('password'),
                'team_id' => $team->id,
                'company_id' => $firstCompany->id,
            ]
        );
        $manager->assignRole('manager');

        // Viewer del team
        $viewer = User::firstOrCreate(
            ['email' => strtolower($team->slug) . '-viewer@example.com'],
            [
                'name' => 'Viewer ' . $team->name,
                'password' => Hash::make('password'),
                'team_id' => $team->id,
                'company_id' => $firstCompany->id,
            ]
        );
        $viewer->assignRole('viewer');

        $this->command->info("  └─ 3 users creati per team {$team->name}");
    }

    /**
     * Crea KPI values realistici per le companies
     */
    private function createKpiValuesForCompanies($companies): void
    {
        $kpis = Kpi::all();
        $periods = Period::orderBy('start_date', 'desc')->take(6)->get(); // Ultimi 6 mesi

        if ($kpis->isEmpty() || $periods->isEmpty()) {
            $this->command->warn('  ⚠️  Nessun KPI o Period trovato. Eseguire PeriodsSeeder e KpisSeeder prima.');
            return;
        }

        foreach ($companies as $company) {
            foreach ($periods as $period) {
                foreach ($kpis->random(rand(8, 12)) as $kpi) {
                    // Genera valori realistici basati sul tipo di KPI
                    $value = $this->generateRealisticValue($kpi);

                    KpiValue::firstOrCreate(
                        [
                            'company_id' => $company->id,
                            'kpi_id' => $kpi->id,
                            'period_id' => $period->id,
                        ],
                        [
                            'value' => $value,
                            'provenance_json' => [
                                'source' => 'seed',
                                'timestamp' => now()->toIso8601String(),
                            ],
                        ]
                    );
                }
            }
        }

        $this->command->info("  └─ KPI values creati per {$companies->count()} aziende");
    }

    /**
     * Genera valori realistici basati sul tipo di KPI
     */
    private function generateRealisticValue(Kpi $kpi): float
    {
        return match ($kpi->unit) {
            'EUR' => (float) rand(10000, 5000000),
            '%' => (float) rand(1, 100),
            'ratio' => (float) (rand(50, 300) / 100),
            'days' => (float) rand(30, 90),
            default => (float) rand(0, 10000),
        };
    }
}
