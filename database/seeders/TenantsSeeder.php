<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\Tenant;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crea 3 tenant di esempio
        $tenants = [
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

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::firstOrCreate(
                ['slug' => $tenantData['slug']],
                [
                    'name' => $tenantData['name'],
                    'settings_json' => [
                        'default_currency' => 'EUR',
                        'fiscal_year_start' => 1,
                    ],
                ]
            );

            // Crea anche un team per RBAC (Spatie Permission multi-tenancy)
            $team = Team::firstOrCreate(
                ['slug' => $tenantData['slug']],
                ['name' => $tenantData['name']]
            );

            // Crea 3-5 companies per tenant
            $companies = Company::factory()
                ->count(rand(3, 5))
                ->create(['tenant_id' => $tenant->id]);

            // Crea users per questo tenant/team
            $this->createUsersForTenant($team, $companies->first());

            $this->command->info("✅ Tenant '{$tenant->name}': {$companies->count()} aziende create");
        }
    }

    /**
     * Crea users per un team con diversi ruoli
     */
    private function createUsersForTenant(Team $team, Company $firstCompany): void
    {
        // Set the current team context for Spatie Permission
        setPermissionsTeamId($team->id);

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
}

