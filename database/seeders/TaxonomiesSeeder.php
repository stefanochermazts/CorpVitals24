<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomiesSeeder extends Seeder
{
    public function run(): void
    {
        $taxonomies = [
            [
                'name' => 'IFRS-ESEF 2023',
                'version' => '2023',
                'country' => 'EU',
                'base_standard' => 'IFRS',
                'taxonomy_url' => 'https://www.esma.europa.eu/taxonomy/2023-03-23',
                'entry_point_url' => 'https://www.esma.europa.eu/taxonomy/2023-03-23/esef_all.xsd',
                'schema_refs' => [
                    'ifrs-full' => 'https://xbrl.ifrs.org/taxonomy/2023-03-23/ifrs-full.xsd',
                    'esef_cor' => 'https://www.esma.europa.eu/taxonomy/2023-03-23/esef_cor.xsd',
                ],
                'is_active' => true,
                'valid_from' => '2023-01-01',
                'valid_to' => '2023-12-31',
            ],
            [
                'name' => 'IFRS-ESEF 2024',
                'version' => '2024',
                'country' => 'EU',
                'base_standard' => 'IFRS',
                'taxonomy_url' => 'https://www.esma.europa.eu/taxonomy/2024-03-21',
                'entry_point_url' => 'https://www.esma.europa.eu/taxonomy/2024-03-21/esef_all.xsd',
                'schema_refs' => [
                    'ifrs-full' => 'https://xbrl.ifrs.org/taxonomy/2024-03-21/ifrs-full.xsd',
                    'esef_cor' => 'https://www.esma.europa.eu/taxonomy/2024-03-21/esef_cor.xsd',
                ],
                'is_active' => true,
                'valid_from' => '2024-01-01',
                'valid_to' => null,
            ],
            [
                'name' => 'Italian GAAP (OIC) 2022',
                'version' => '2022',
                'country' => 'IT',
                'base_standard' => 'OIC',
                'taxonomy_url' => 'https://xbrl.registro-imprese.it/tassonomie/oic-2022',
                'entry_point_url' => 'https://xbrl.registro-imprese.it/tassonomie/oic-2022/oic-bilancio.xsd',
                'schema_refs' => [
                    'itcc-ci' => 'https://xbrl.registro-imprese.it/tassonomie/oic-2022/itcc-ci.xsd',
                    'itcc-sp' => 'https://xbrl.registro-imprese.it/tassonomie/oic-2022/itcc-sp.xsd',
                ],
                'is_active' => true,
                'valid_from' => '2022-01-01',
                'valid_to' => '2023-12-31',
            ],
            [
                'name' => 'Italian GAAP (OIC) 2023',
                'version' => '2023',
                'country' => 'IT',
                'base_standard' => 'OIC',
                'taxonomy_url' => 'https://xbrl.registro-imprese.it/tassonomie/oic-2023',
                'entry_point_url' => 'https://xbrl.registro-imprese.it/tassonomie/oic-2023/oic-bilancio.xsd',
                'schema_refs' => [
                    'itcc-ci' => 'https://xbrl.registro-imprese.it/tassonomie/oic-2023/itcc-ci.xsd',
                    'itcc-sp' => 'https://xbrl.registro-imprese.it/tassonomie/oic-2023/itcc-sp.xsd',
                    'itcc-nf' => 'https://xbrl.registro-imprese.it/tassonomie/oic-2023/itcc-nf.xsd',
                ],
                'is_active' => true,
                'valid_from' => '2023-01-01',
                'valid_to' => null,
            ],
        ];

        foreach ($taxonomies as $data) {
            Taxonomy::firstOrCreate(
                ['name' => $data['name'], 'version' => $data['version']],
                $data
            );
        }

        $this->command?->info('✅ Seeded taxonomies: IFRS-ESEF (2023,2024), OIC (2022,2023)');
    }
}


