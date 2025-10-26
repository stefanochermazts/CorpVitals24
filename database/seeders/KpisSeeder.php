<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Kpi;
use Illuminate\Database\Seeder;

class KpisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kpis = [
            // KPI Finanziari
            [
                'name' => 'Fatturato',
                'code' => 'REV',
                'description' => 'Ricavi totali del periodo',
                'formula_refs' => ['unit' => 'EUR', 'display_format' => 'currency'],
            ],
            [
                'name' => 'EBITDA',
                'code' => 'EBITDA',
                'description' => 'Earnings Before Interest, Taxes, Depreciation and Amortization',
                'formula_refs' => ['unit' => 'EUR', 'display_format' => 'currency'],
            ],
            [
                'name' => 'Margine Operativo Lordo',
                'code' => 'MOL',
                'description' => 'Margine operativo lordo (MOL/EBITDA)',
                'formula_refs' => ['unit' => 'EUR', 'display_format' => 'currency'],
            ],
            [
                'name' => 'Utile Netto',
                'code' => 'NET',
                'description' => 'Utile netto del periodo',
                'formula_refs' => ['unit' => 'EUR', 'display_format' => 'currency'],
            ],
            [
                'name' => 'ROI',
                'code' => 'ROI',
                'description' => 'Return On Investment - Redditività del capitale investito',
                'formula_refs' => ['unit' => '%', 'display_format' => 'percentage'],
            ],
            [
                'name' => 'ROE',
                'code' => 'ROE',
                'description' => 'Return On Equity - Redditività del capitale proprio',
                'formula_refs' => ['unit' => '%', 'display_format' => 'percentage'],
            ],
            [
                'name' => 'Indice di Liquidità',
                'code' => 'LIQ',
                'description' => 'Attività correnti / Passività correnti',
                'formula_refs' => ['unit' => 'ratio', 'display_format' => 'number'],
            ],
            [
                'name' => 'Quick Ratio',
                'code' => 'QR',
                'description' => '(Liquidità immediate + Liquidità differite) / Passività correnti',
                'formula_refs' => ['unit' => 'ratio', 'display_format' => 'number'],
            ],
            [
                'name' => 'Capitale Circolante Netto',
                'code' => 'CCN',
                'description' => 'Attività correnti - Passività correnti',
                'formula_refs' => ['unit' => 'EUR', 'display_format' => 'currency'],
            ],
            [
                'name' => 'Giorni Medi Incasso',
                'code' => 'DSO',
                'description' => 'Days Sales Outstanding - Giorni medi di incasso crediti',
                'formula_refs' => ['unit' => 'days', 'display_format' => 'number'],
            ],
            [
                'name' => 'Giorni Medi Pagamento',
                'code' => 'DPO',
                'description' => 'Days Payable Outstanding - Giorni medi di pagamento fornitori',
                'formula_refs' => ['unit' => 'days', 'display_format' => 'number'],
            ],
            [
                'name' => 'Tasso di Crescita Fatturato',
                'code' => 'GR',
                'description' => 'Crescita percentuale del fatturato rispetto al periodo precedente',
                'formula_refs' => ['unit' => '%', 'display_format' => 'percentage'],
            ],
            [
                'name' => 'Debt to Equity',
                'code' => 'DTE',
                'description' => 'Rapporto tra debiti totali e patrimonio netto',
                'formula_refs' => ['unit' => 'ratio', 'display_format' => 'number'],
            ],
            [
                'name' => 'Indice di Indebitamento',
                'code' => 'LEV',
                'description' => 'Totale debiti / Totale attivo',
                'formula_refs' => ['unit' => '%', 'display_format' => 'percentage'],
            ],
            [
                'name' => 'Rotazione Magazzino',
                'code' => 'INV_TURN',
                'description' => 'Costo del venduto / Valore medio magazzino',
                'formula_refs' => ['unit' => 'ratio', 'display_format' => 'number'],
            ],
        ];

        foreach ($kpis as $kpiData) {
            Kpi::firstOrCreate(
                ['code' => $kpiData['code']],
                $kpiData
            );
        }

        $this->command->info('✅ Creati ' . count($kpis) . ' KPI standard');
    }
}
