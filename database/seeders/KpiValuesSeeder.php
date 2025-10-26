<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use Illuminate\Database\Seeder;

class KpiValuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        $kpis = Kpi::all();

        if ($companies->isEmpty()) {
            $this->command->warn('⚠️  Nessuna company trovata.');
            return;
        }

        if ($kpis->isEmpty()) {
            $this->command->warn('⚠️  Nessun KPI trovato.');
            return;
        }

        foreach ($companies as $company) {
            $periods = Period::where('company_id', $company->id)
                ->orderBy('start', 'desc')
                ->take(6)
                ->get();

            if ($periods->isEmpty()) {
                continue;
            }

            foreach ($periods as $period) {
                foreach ($kpis->random(min(rand(8, 12), $kpis->count())) as $kpi) {
                    // Genera valori realistici basati sul tipo di KPI
                    $unit = $kpi->formula_refs['unit'] ?? 'EUR';
                    $value = $this->generateRealisticValue($unit);

                    KpiValue::firstOrCreate(
                        [
                            'period_id' => $period->id,
                            'kpi_id' => $kpi->id,
                        ],
                        [
                            'value' => $value,
                            'unit' => $unit === 'EUR' ? 'EUR' : '%',
                            'provenance_json' => [
                                'source' => 'seed',
                                'timestamp' => now()->toIso8601String(),
                            ],
                        ]
                    );
                }
            }

            $this->command->info("  └─ KPI values creati per company '{$company->name}'");
        }

        $this->command->info('✅ KPI values creati per ' . $companies->count() . ' companies');
    }

    /**
     * Genera valori realistici basati sul tipo di KPI
     */
    private function generateRealisticValue(string $unit): float
    {
        return match ($unit) {
            'EUR' => (float) rand(10000, 5000000),
            '%' => (float) rand(1, 100),
            'ratio' => (float) (rand(50, 300) / 100),
            'days' => (float) rand(30, 90),
            default => (float) rand(0, 10000),
        };
    }
}

