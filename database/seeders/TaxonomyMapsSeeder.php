<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Taxonomy;
use App\Models\TaxonomyMap;
use Illuminate\Database\Seeder;

class TaxonomyMapsSeeder extends Seeder
{
    public function run(): void
    {
        $ifrs2023 = Taxonomy::where(['name' => 'IFRS-ESEF 2023', 'version' => '2023'])->first();
        $ifrs2024 = Taxonomy::where(['name' => 'IFRS-ESEF 2024', 'version' => '2024'])->first();
        $oic2023 = Taxonomy::where(['name' => 'Italian GAAP (OIC) 2023', 'version' => '2023'])->first();

        if (!$ifrs2023 || !$ifrs2024 || !$oic2023) {
            $this->command?->warn('⚠️  Missing taxonomies. Run TaxonomiesSeeder first.');
            return;
        }

        $ifrsMappings = [
            ['ifrs-full:Revenue', 'Ricavi', 'positive'],
            ['ifrs-full:CostOfSales', 'COGS', 'negative'],
            ['ifrs-full:OperatingExpense', 'CostiOperativi', 'negative'],
            ['ifrs-full:DepreciationAndAmortisationExpense', 'Ammortamenti', 'negative'],
            ['ifrs-full:FinanceCosts', 'OneriFin', 'negative'],
            ['ifrs-full:IncomeTaxExpense', 'Imposte', 'negative'],
            ['ifrs-full:ProfitLoss', 'UtileNetto', 'preserve'],
            ['ifrs-full:Assets', 'TotaleAttivo', 'positive'],
            ['ifrs-full:CurrentAssets', 'AttivoCorrente', 'positive'],
            ['ifrs-full:NoncurrentAssets', 'AttivoNonCorrente', 'positive'],
            ['ifrs-full:CashAndCashEquivalents', 'Liquidità', 'positive'],
            ['ifrs-full:TradeAndOtherCurrentReceivables', 'CreditiComm', 'positive'],
            ['ifrs-full:Inventories', 'Rimanenze', 'positive'],
            ['ifrs-full:Liabilities', 'TotalePassivo', 'positive'],
            ['ifrs-full:CurrentLiabilities', 'PassivoCorrente', 'positive'],
            ['ifrs-full:NoncurrentLiabilities', 'PassivoNonCorrente', 'positive'],
            ['ifrs-full:TradeAndOtherCurrentPayables', 'DebitiComm', 'positive'],
            ['ifrs-full:Equity', 'PatrimonioNetto', 'positive'],
            ['ifrs-full:CashFlowsFromUsedInOperatingActivities', 'CFO', 'preserve'],
            ['ifrs-full:CashFlowsFromUsedInInvestingActivities', 'CFI', 'preserve'],
            ['ifrs-full:CashFlowsFromUsedInFinancingActivities', 'CFF', 'preserve'],
        ];

        foreach ([$ifrs2023, $ifrs2024] as $taxonomy) {
            foreach ($ifrsMappings as [$concept, $target, $sign]) {
                TaxonomyMap::firstOrCreate(
                    ['taxonomy_id' => $taxonomy->id, 'concept_qname' => $concept],
                    [
                        'valore_base_target' => $target,
                        'sign_rule' => $sign,
                        'multiplier' => 1.0,
                        'priority' => 100,
                        'is_default' => true,
                    ]
                );
            }
        }

        $oicMappings = [
            ['itcc-ci:RicaviVendite', 'Ricavi', 'positive'],
            ['itcc-ci:CostiProduzione', 'COGS', 'negative'],
            ['itcc-ci:CostiPersonale', 'CostiPersonale', 'negative'],
            ['itcc-ci:Ammortamenti', 'Ammortamenti', 'negative'],
            ['itcc-ci:OneriFinanziari', 'OneriFin', 'negative'],
            ['itcc-ci:Imposte', 'Imposte', 'negative'],
            ['itcc-ci:UtilePerditaEsercizio', 'UtileNetto', 'preserve'],
            ['itcc-sp:TotaleAttivo', 'TotaleAttivo', 'positive'],
            ['itcc-sp:AttivoCircolante', 'AttivoCorrente', 'positive'],
            ['itcc-sp:Immobilizzazioni', 'AttivoNonCorrente', 'positive'],
            ['itcc-sp:DisponibilitaLiquide', 'Liquidità', 'positive'],
            ['itcc-sp:Crediti', 'CreditiComm', 'positive'],
            ['itcc-sp:Rimanenze', 'Rimanenze', 'positive'],
            ['itcc-sp:TotalePassivo', 'TotalePassivo', 'positive'],
            ['itcc-sp:PassivitaCorrente', 'PassivoCorrente', 'positive'],
            ['itcc-sp:DebitiEntroAnno', 'DebitiComm', 'positive'],
            ['itcc-sp:PatrimonioNetto', 'PatrimonioNetto', 'positive'],
        ];

        foreach ($oicMappings as [$concept, $target, $sign]) {
            TaxonomyMap::firstOrCreate(
                ['taxonomy_id' => $oic2023->id, 'concept_qname' => $concept],
                [
                    'valore_base_target' => $target,
                    'sign_rule' => $sign,
                    'multiplier' => 1.0,
                    'priority' => 100,
                    'is_default' => true,
                ]
            );
        }

        $this->command?->info('✅ Seeded taxonomy maps for IFRS/ESEF (2023, 2024) and OIC (2023)');
    }
}


