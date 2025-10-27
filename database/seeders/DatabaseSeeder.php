<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Inizio seeding del database...');

        // 1. Ruoli e permessi (base del sistema RBAC)
        $this->call(RolesTableSeeder::class);

        // 2. KPI definitions (master data)
        $this->call(KpisSeeder::class);

        // 2b. XBRL taxonomies and default mappings
        $this->call([
            TaxonomiesSeeder::class,
            TaxonomyMapsSeeder::class,
        ]);

        // 3. Tenants, Teams, Companies, Users (dati multi-tenant)
        $this->call(TenantsSeeder::class);

        // 4. Periodi contabili (per ogni company)
        $this->call(PeriodsSeeder::class);

        // 5. KPI Values (dati di test)
        $this->call(KpiValuesSeeder::class);

        $this->command->info('✅ Database seeding completato!');
        $this->command->newLine();
        $this->command->info('🔐 Credenziali di accesso create:');
        $this->command->table(
            ['Team', 'Email', 'Password', 'Ruolo'],
            [
                ['Studio Rossi', 'studio-rossi-admin@example.com', 'password', 'Admin'],
                ['Studio Rossi', 'studio-rossi-manager@example.com', 'password', 'Manager'],
                ['Studio Rossi', 'studio-rossi-viewer@example.com', 'password', 'Viewer'],
                ['CFI', 'cfi-admin@example.com', 'password', 'Admin'],
                ['CFI', 'cfi-manager@example.com', 'password', 'Manager'],
                ['CFI', 'cfi-viewer@example.com', 'password', 'Viewer'],
                ['BAN', 'ban-admin@example.com', 'password', 'Admin'],
                ['BAN', 'ban-manager@example.com', 'password', 'Manager'],
                ['BAN', 'ban-viewer@example.com', 'password', 'Viewer'],
            ]
        );
    }
}

