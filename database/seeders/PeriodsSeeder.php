<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PeriodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('⚠️  Nessuna company trovata. Eseguire TeamsSeeder prima.');
            return;
        }

        // Crea periodi mensili per ogni company per gli ultimi 12 mesi
        foreach ($companies as $company) {
            for ($i = 11; $i >= 0; $i--) {
                $startDate = Carbon::now()->subMonths($i)->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();

                Period::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'kind' => 'M',
                        'start' => $startDate->format('Y-m-d'),
                    ],
                    [
                        'end' => $endDate->format('Y-m-d'),
                        'currency' => $company->base_currency ?? 'EUR',
                    ]
                );
            }
        }

        $this->command->info('✅ Creati periodi mensili per ' . $companies->count() . ' companies');
    }
}
