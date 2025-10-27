# Riepilogo Integrazione XBRL - CorpVitals24

**Data**: 27 Ottobre 2025  
**Versione Piano**: v2.0 (XBRL Integrated)

---

## 🎯 Obiettivo

Integrare supporto completo XBRL/iXBRL nel piano di sviluppo post-MVP, permettendo l'import di bilanci europei standardizzati (ESEF + Italian OIC) oltre ai classici CSV/XLSX.

---

## 📊 Confronto Versioni Piano

| Aspetto | **v1.0 Originale** | **v2.0 XBRL Integrated** |
|---------|-------------------|--------------------------|
| **Step Totali** | 18 | 24 (+6 XBRL) |
| **Timeline** | 7-8 settimane | 11-12 settimane |
| **Import Supportati** | CSV, XLSX | CSV, XLSX, **XBRL, iXBRL** |
| **Parser** | Laravel Excel | Laravel Excel + **Arelle Python** |
| **Tassonomie** | N/A | **IFRS/ESEF, Italian OIC** |
| **Dimensioni XBRL** | N/A | **Explicit + Typed dimensions** |
| **Tabelle DB** | 15 | 22 (+7 XBRL) |
| **Frontend UI** | 1 wizard (CSV) | **2 wizard** (CSV + XBRL) |
| **Taxonomy Management** | N/A | **UI completa CRUD** |
| **Auto-mapping** | No | **Sì (80+ concepts)** |

---

## ✨ Nuove Funzionalità XBRL

### 1. **Import XBRL/iXBRL Completo**
- ✅ Parsing con Arelle Python (parser standard industria)
- ✅ Supporto ESEF iXBRL (società quotate EU)
- ✅ Supporto Italian OIC XBRL (PMI)
- ✅ Rilevamento automatico tassonomia
- ✅ Normalizzazione periodi, valute, decimali
- ✅ Gestione segni (positivi/negativi)

### 2. **Gestione Dimensioni XBRL**
- ✅ **Explicit dimensions** (es. consolidamento, segmenti geografici)
- ✅ **Typed dimensions** (valori custom)
- ✅ Multi-dimensionalità (4+ dimensioni per fact)
- ✅ Storage ottimizzato in JSONB + tabella dedicata

### 3. **Sistema Tassonomie**
- ✅ Database tassonomie (IFRS, OIC, versioning)
- ✅ **Auto-mapping** concept_qname → ValoriBase (80+ mapping predefiniti)
- ✅ Regole di trasformazione (sign rules, multiplier)
- ✅ Priorità mapping per gestire conflitti
- ✅ UI gestione tassonomie (CRUD)

### 4. **Wizard Import XBRL**
- ✅ Upload file .xbrl/.ixbrl/.xhtml
- ✅ Preview facts estratti
- ✅ Suggerimenti auto-mapping
- ✅ Editor manuale per concept custom
- ✅ Validazione e log errori
- ✅ Tracciabilità completa (fact → ValoriBase → KPI)

### 5. **Dataset di Test**
- ✅ File sintetici IFRS e OIC
- ✅ Script acquisizione da ESMA
- ✅ Istruzioni InfoCamere
- ✅ Validazione con Arelle

---

## 🗄️ Estensioni Database

### Nuove Tabelle (7)

#### 1. `filings`
Memorizza file importati (CSV, XLSX, XBRL, iXBRL).

**Campi chiave**:
- `type`: enum('CSV', 'XLSX', 'XBRL', 'iXBRL')
- `taxonomy_id`: FK a taxonomies
- `hash_sha256`: checksum per deduplicazione
- `status`: enum('pending', 'parsing', 'completed', 'failed')

#### 2. `filing_facts`
Facts XBRL estratti (concepts con valori).

**Campi chiave**:
- `concept_qname`: es. "ifrs-full:Revenue"
- `context_ref`: riferimento al contesto (periodo + entità)
- `unit_ref`: unità di misura (EUR, USD, etc.)
- `value_raw` / `value_normalized`: valore grezzo e normalizzato
- `dimensions`: JSONB con segmenti/scenari

#### 3. `taxonomies`
Definizioni tassonomie supportate.

**Esempi**:
- IFRS-ESEF 2023
- IFRS-ESEF 2024
- Italian GAAP (OIC) 2023

#### 4. `taxonomy_maps`
Mapping automatico concepts → ValoriBase.

**Esempio**:
```
concept_qname: "ifrs-full:Revenue"
valore_base_target: "Ricavi"
sign_rule: "positive"
multiplier: 1.0
```

#### 5. `dimension_defs`
Definizioni dimensioni per tassonomia.

**Tipi**:
- **Explicit**: membri predefiniti (es. "ConsolidatedMember", "ParentCompanyMember")
- **Typed**: valori aperti (es. data, testo)

#### 6. `dimension_values`
Valori dimensioni associate ai facts.

**Esempio**:
```
filing_fact_id: 123
dimension_qname: "ifrs-full:EntityAxis"
member_qname: "ifrs-full:ConsolidatedMember"
```

#### 7. Estensione `valori_base`
- `filing_id`: FK nullable a filings
- `source`: enum aggiunto 'XBRL'
- `provenance`: JSONB con link a filing_fact_id

---

## 🛠️ Stack Tecnologico XBRL

### Backend
| Componente | Tecnologia | Scopo |
|------------|------------|-------|
| **Parser** | Arelle Python 3.11 | Parsing XBRL/iXBRL robusto |
| **Wrapper** | PHP Symfony Process | Invocazione Arelle via subprocess |
| **Storage** | PostgreSQL 16 JSONB | Dimensioni e metadata |
| **Cache** | Redis 7 | Tassonomie in-memory (TTL 24h) |
| **Queue** | Laravel Queue (Redis) | Processing asincrono |

### Frontend
| Componente | Tecnologia | Scopo |
|------------|------------|-------|
| **Upload** | Vue 3 + @vueuse/core | Drag-drop file XBRL |
| **Preview** | RevoGrid | Anteprima facts estratti |
| **Mapping** | Vue 3 reactive forms | Wizard interattivo |
| **Taxonomy UI** | Vue 3 + Headless UI | CRUD tassonomie |

---

## 📂 File Creati/Modificati

### Nuovi File (Step 2B-2C-3B)

#### Backend Services
- `app/Services/XbrlParserService.php` - Wrapper Arelle
- `app/Services/XbrlMappingService.php` - Auto-mapping concepts
- `app/Services/DimensionService.php` - Gestione dimensioni
- `app/Exceptions/XbrlParseException.php` - Eccezioni custom

#### Repositories
- `app/Repositories/XbrlRepository.php` - Query Filing/FilingFact
- `app/Repositories/TaxonomyRepository.php` - Query Taxonomies/Maps

#### Controllers
- `app/Http/Controllers/XbrlImportController.php` - Import XBRL
- `app/Http/Controllers/TaxonomyController.php` - Gestione tassonomie

#### Jobs
- `app/Jobs/ProcessXbrlImportJob.php` - Parse + mapping asincrono

#### Database
- `database/migrations/*_create_filings_table.php`
- `database/migrations/*_create_filing_facts_table.php`
- `database/migrations/*_create_taxonomies_table.php`
- `database/migrations/*_create_taxonomy_maps_table.php`
- `database/migrations/*_create_dimension_defs_table.php`
- `database/migrations/*_create_dimension_values_table.php`
- `database/migrations/*_extend_valori_base_table.php`

#### Seeders
- `database/seeders/TaxonomiesSeeder.php` - 4 tassonomie (IFRS + OIC)
- `database/seeders/TaxonomyMapsSeeder.php` - 80+ mapping predefiniti

#### Config
- `config/xbrl.php` - Configurazione Arelle, timeout, cache

#### Frontend
- `resources/js/pages/Import/Xbrl/Index.vue` - UI import XBRL
- `resources/js/pages/Import/Xbrl/MappingWizard.vue` - Wizard mapping
- `resources/js/pages/Taxonomy/Index.vue` - Lista tassonomie
- `resources/js/pages/Taxonomy/Form.vue` - Form CRUD taxonomy
- `resources/js/stores/xbrlStore.ts` - Pinia store XBRL

#### Testing
- `tests/Feature/XbrlParserServiceTest.php`
- `tests/Feature/XbrlImportFlowTest.php`
- `tests/Unit/TaxonomyMapTest.php`

#### Scripts & Dataset
- `scripts/download_xbrl_samples.sh` - Acquisizione dataset
- `storage/xbrl-samples/synthetic/sample-ifrs-2023.xbrl` - File test IFRS
- `storage/xbrl-samples/synthetic/sample-oic-2023.xbrl` - File test OIC
- `storage/xbrl-samples/README.md` - Documentazione dataset

#### Documentazione
- `documenti/ADRs/adr-001-arelle-parser.md`
- `documenti/ADRs/adr-002-dual-source-import.md`
- `documenti/ADRs/adr-003-xbrl-dimensions-storage.md`
- `documenti/ADRs/adr-004-taxonomy-caching.md`
- `documenti/XBRL_INTEGRATION_GUIDE.md`
- `documenti/ARELLE_SETUP.md`
- `documenti/TAXONOMY_MAPPING_GUIDE.md`
- `documenti/XBRL_DIMENSIONS_EXPLAINED.md`

### File Modificati

#### Backend
- `app/Services/KpiEngineService.php` - Lettura da multi-source (CSV + XBRL)
- `app/Repositories/FinancialDataRepository.php` - Query ValoriBase con source
- `bootstrap/app.php` - Registrazione service providers XBRL

#### Frontend
- `resources/js/pages/Import/Index.vue` - Tab CSV vs XBRL
- `resources/js/stores/dashboardStore.ts` - Provenance XBRL nei KPI

#### Config
- `.env.example` - Variabili ARELLE_PATH, XBRL_*
- `docker-compose.yml` - Service Arelle Python

---

## 🔄 Flusso Import XBRL

```
┌─────────────┐
│  User       │
│  Upload     │
│  .xbrl file │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────────────────┐
│  XbrlImportController::store()                  │
│  - Validazione file (ext, size, mime)           │
│  - Crea record Filing (status: pending)         │
│  - Dispatch ProcessXbrlImportJob                │
└──────┬──────────────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────────────┐
│  ProcessXbrlImportJob (Queue)                   │
│                                                  │
│  1. XbrlParserService::parse()                  │
│     └─> Invoca Arelle Python subprocess         │
│         - Extract facts, contexts, units        │
│         - Extract dimensions                    │
│         - Return JSON structured                │
│                                                  │
│  2. XbrlParserService::detectTaxonomy()         │
│     └─> Identifica IFRS/OIC/etc.                │
│                                                  │
│  3. XbrlMappingService::autoMap()               │
│     └─> Query TaxonomyMap per concept_qname     │
│         - Applica sign_rule, multiplier         │
│         - Return mapped ValoriBase entries      │
│                                                  │
│  4. DimensionService::extractDimensions()       │
│     └─> Parse segment/scenario da context       │
│         - Store in dimension_values             │
│                                                  │
│  5. XbrlRepository::storeFilingFacts()          │
│     └─> INSERT filing_facts batch               │
│                                                  │
│  6. XbrlRepository::storeValoriBase()           │
│     └─> INSERT valori_base (source: XBRL)       │
│         - Link filing_id + provenance JSON      │
│                                                  │
│  7. Update Filing (status: completed)           │
│                                                  │
└──────┬──────────────────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────────────┐
│  KpiEngineService::calculateForCompany()        │
│  - Legge ValoriBase (source: CSV + XBRL)        │
│  - Calcola KPI con provenance                   │
│  - Store KpiValue con link a filing_id          │
└──────┬──────────────────────────────────────────┘
       │
       ▼
┌─────────────┐
│  Dashboard  │
│  KPI visibili│
│  con drill-  │
│  down a      │
│  filing_fact │
└─────────────┘
```

---

## 📊 Mappings Predefiniti

### IFRS/ESEF (40+ concepts)

#### Income Statement
| Concept XBRL | ValoriBase | Sign Rule |
|--------------|------------|-----------|
| `ifrs-full:Revenue` | Ricavi | positive |
| `ifrs-full:CostOfSales` | COGS | negative |
| `ifrs-full:OperatingExpense` | CostiOperativi | negative |
| `ifrs-full:DepreciationAndAmortisationExpense` | Ammortamenti | negative |
| `ifrs-full:FinanceCosts` | OneriFin | negative |
| `ifrs-full:IncomeTaxExpense` | Imposte | negative |
| `ifrs-full:ProfitLoss` | UtileNetto | preserve |

#### Balance Sheet - Assets
| Concept XBRL | ValoriBase | Sign Rule |
|--------------|------------|-----------|
| `ifrs-full:Assets` | TotaleAttivo | positive |
| `ifrs-full:CurrentAssets` | AttivoCorrente | positive |
| `ifrs-full:NoncurrentAssets` | AttivoNonCorrente | positive |
| `ifrs-full:CashAndCashEquivalents` | Liquidità | positive |
| `ifrs-full:TradeAndOtherCurrentReceivables` | CreditiComm | positive |
| `ifrs-full:Inventories` | Rimanenze | positive |

#### Balance Sheet - Liabilities & Equity
| Concept XBRL | ValoriBase | Sign Rule |
|--------------|------------|-----------|
| `ifrs-full:Liabilities` | TotalePassivo | positive |
| `ifrs-full:CurrentLiabilities` | PassivoCorrente | positive |
| `ifrs-full:NoncurrentLiabilities` | PassivoNonCorrente | positive |
| `ifrs-full:TradeAndOtherCurrentPayables` | DebitiComm | positive |
| `ifrs-full:Equity` | PatrimonioNetto | positive |

#### Cash Flow
| Concept XBRL | ValoriBase | Sign Rule |
|--------------|------------|-----------|
| `ifrs-full:CashFlowsFromUsedInOperatingActivities` | CFO | preserve |
| `ifrs-full:CashFlowsFromUsedInInvestingActivities` | CFI | preserve |
| `ifrs-full:CashFlowsFromUsedInFinancingActivities` | CFF | preserve |

### Italian OIC (40+ concepts)

#### Conto Economico
| Concept XBRL | ValoriBase | Sign Rule |
|--------------|------------|-----------|
| `itcc-ci:RicaviVendite` | Ricavi | positive |
| `itcc-ci:CostiProduzione` | COGS | negative |
| `itcc-ci:CostiPersonale` | CostiPersonale | negative |
| `itcc-ci:Ammortamenti` | Ammortamenti | negative |
| `itcc-ci:OneriFinanziari` | OneriFin | negative |
| `itcc-ci:Imposte` | Imposte | negative |
| `itcc-ci:UtilePerditaEsercizio` | UtileNetto | preserve |

#### Stato Patrimoniale
| Concept XBRL | ValoriBase | Sign Rule |
|--------------|------------|-----------|
| `itcc-sp:TotaleAttivo` | TotaleAttivo | positive |
| `itcc-sp:AttivoCircolante` | AttivoCorrente | positive |
| `itcc-sp:Immobilizzazioni` | AttivoNonCorrente | positive |
| `itcc-sp:DisponibilitaLiquide` | Liquidità | positive |
| `itcc-sp:Crediti` | CreditiComm | positive |
| `itcc-sp:Rimanenze` | Rimanenze | positive |
| `itcc-sp:TotalePassivo` | TotalePassivo | positive |
| `itcc-sp:PassivitaCorrente` | PassivoCorrente | positive |
| `itcc-sp:DebitiEntroAnno` | DebitiComm | positive |
| `itcc-sp:PatrimonioNetto` | PatrimonioNetto | positive |

---

## 🔍 Esempio Tracciabilità KPI

```
┌─────────────────────────────────┐
│ File XBRL                       │
│ enel-2023.xhtml                 │
└──────┬──────────────────────────┘
       │
       ▼
┌─────────────────────────────────────────────────┐
│ Filing Fact                                     │
│                                                  │
│ concept: "ifrs-full:Revenue"                    │
│ context: "ctx_2023_duration"                    │
│ period: 2023-01-01 to 2023-12-31                │
│ unit: EUR                                        │
│ value_raw: 5000000000                            │
│ value_normalized: 5000000000.00                 │
└──────┬──────────────────────────────────────────┘
       │ Auto-mapping via TaxonomyMap
       ▼
┌─────────────────────────────────────────────────┐
│ Valori Base                                     │
│                                                  │
│ voce: "Ricavi"                                   │
│ importo: 5000000000.00                           │
│ source: "XBRL"                                   │
│ filing_id: 123                                   │
│ provenance: {"filing_fact_id": 456}             │
└──────┬──────────────────────────────────────────┘
       │ Calcolo KPI
       ▼
┌─────────────────────────────────────────────────┐
│ KPI Value                                       │
│                                                  │
│ kpi_code: "REV"                                  │
│ value: 5000000000.00                             │
│ provenance_json: {                               │
│   "valori_base_ids": [789],                     │
│   "filing_id": 123,                              │
│   "source": "XBRL",                              │
│   "taxonomy": "IFRS-ESEF 2023"                  │
│ }                                                │
└──────┬──────────────────────────────────────────┘
       │ Dashboard
       ▼
┌─────────────┐
│ User vede:  │
│             │
│ Ricavi 2023 │
│ €5.0B       │
│             │
│ [Drill-down]│
│ ↓           │
│ Source:     │
│ XBRL ESEF   │
│ Fact ID:456 │
│ Filing:123  │
└─────────────┘
```

---

## ⚙️ Setup & Configurazione

### 1. Installazione Arelle

#### Via Docker (Raccomandato)
```bash
# Build Arelle image
docker build -t corpvitals-arelle -f docker/arelle/Dockerfile .

# Run container
docker run -d --name arelle \
  -v /var/www/CorpVitals24/storage:/storage \
  corpvitals-arelle
```

#### Via Locale (Development)
```bash
# Clone Arelle
cd /opt
git clone https://github.com/Arelle/Arelle.git
cd Arelle
pip3 install -r requirements.txt

# Test
python3 arelleCmdLine.py --help
```

### 2. Configurazione Laravel

#### `.env`
```env
# Arelle
ARELLE_PATH=/opt/Arelle/arelleCmdLine.py
XBRL_PARSE_TIMEOUT=300
XBRL_MAX_FILE_SIZE=52428800
XBRL_TAXONOMY_CACHE_TTL=86400
```

### 3. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed taxonomies
php artisan db:seed --class=TaxonomiesSeeder
php artisan db:seed --class=TaxonomyMapsSeeder
```

### 4. Acquisizione Dataset

```bash
# Generate synthetic files
bash scripts/download_xbrl_samples.sh

# Verify
ls -lh storage/xbrl-samples/synthetic/
```

### 5. Test Parsing

```bash
php artisan tinker

>>> $service = app(\App\Services\XbrlParserService::class);
>>> $result = $service->parse(storage_path('xbrl-samples/synthetic/sample-ifrs-2023.xbrl'));
>>> dump($result);
```

---

## 🚀 Quick Start (Post-Setup)

### Import XBRL via UI
1. Login dashboard
2. Navigate: **Import → XBRL**
3. Upload file `.xbrl` o `.ixbrl`
4. Wait parsing (progress bar)
5. Review auto-mapping suggestions
6. Confirm or adjust mappings
7. Save → Facts stored in DB
8. Navigate: **Dashboard → KPI**
9. View calculated KPI with provenance

### Import XBRL via API
```bash
curl -X POST http://localhost:8000/api/imports/xbrl \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@enel-2023.xhtml" \
  -F "company_id=1"
```

---

## 📈 Metriche Attese

### Performance
- **Parsing XBRL**: <10s per file medio (2MB, 500 facts)
- **Auto-mapping**: <1s per 500 concepts
- **Storage**: <5s per 500 filing_facts
- **End-to-end import**: <30s (file 2MB)

### Copertura
- **Auto-mapping success rate**: >90% per IFRS/OIC standard
- **Manual mapping required**: <10% concepts (custom extensions)
- **Dimensioni gestite**: 4+ per fact
- **File size max**: 50MB (configurabile)

### Affidabilità
- **Parsing success rate**: >95% (file ben formati)
- **Retry policy**: 3 tentativi per timeout
- **Error logging**: 100% errori tracciati
- **Audit trail**: 100% operazioni XBRL loggate

---

## 🎓 Risorse Aggiuntive

### Documentazione
- [ESEF ESMA Guidelines](https://www.esma.europa.eu/policy-activities/corporate-disclosure/european-single-electronic-format)
- [IFRS Taxonomy](https://www.ifrs.org/issued-standards/ifrs-taxonomy/)
- [Arelle Documentation](https://arelle.org/)
- [Italian OIC Taxonomy](https://xbrl.registro-imprese.it/)

### Training
- Webinar XBRL basics (TBD)
- Workshop mapping custom concepts (TBD)
- Video tutorial import ESEF (TBD)

---

## ❓ FAQ

### Q: Perché Arelle invece di libreria PHP?
**A**: Arelle è lo standard de-facto per XBRL, usato da SEC/ESMA. Parsing robusto, supporto completo dimensioni, aggiornamenti regolari. Alternative PHP (lyquidity/xbrl) meno mature per iXBRL complessi.

### Q: Posso usare file XBRL in produzione?
**A**: Sì, ma verifica licenza dei file originali. File pubblici ESMA sono OK per uso non commerciale. Bilanci privati richiedono consenso azienda.

### Q: Cosa succede se concept non mappato?
**A**: Sistema mostra warning + wizard mapping manuale. Utente può creare mapping custom salvabile come profilo riutilizzabile.

### Q: Posso aggiungere altre tassonomie (es. DE, FR)?
**A**: Sì! Aggiungi entry in `TaxonomiesSeeder` e crea mapping in `TaxonomyMapsSeeder`. Sistema supporta N tassonomie.

### Q: Performance con file XBRL grandi (>10MB)?
**A**: Usa queue job + chunking. Considera timeout XBRL_PARSE_TIMEOUT=600 (10min). Monitor memory usage Arelle (potrebbe servire container dedicato con RAM>2GB).

---

## ✅ Checklist Pre-Lancio XBRL

- [ ] Arelle installato e testato
- [ ] Migrations eseguite (7 tabelle XBRL)
- [ ] Seed taxonomies completato (IFRS + OIC)
- [ ] Dataset sintetici generati
- [ ] Almeno 2 file ESEF reali testati
- [ ] Almeno 2 file OIC reali testati
- [ ] Auto-mapping validato (>90% success rate)
- [ ] Wizard mapping manuale funzionante
- [ ] Test end-to-end import + KPI calc
- [ ] Dimensioni XBRL parsate correttamente
- [ ] Tracciabilità verificata (fact→KPI)
- [ ] Performance benchmark raggiunti
- [ ] Error handling testato (file corrotti, timeout)
- [ ] Documentazione ADR completata
- [ ] UI XBRL accessibile (WCAG AA)
- [ ] Test coverage >80%

---

**Documento generato**: 27 Ottobre 2025  
**Versione**: 1.0  
**Owner**: Team CorpVitals24  
**Prossimo Review**: Dopo Step 4D (Taxonomy Management UI)

