# Piano di Sviluppo Post-MVP - CorpVitals24

**Versione**: 1.0  
**Data Creazione**: 27 Ottobre 2025  
**Stato**: In Attesa di Esecuzione  
**Obiettivo**: Guidare lo sviluppo della piattaforma dalla fase MVP core verso la versione 1.0 completa

---

## Panoramica

Questo piano dettagliato copre 18 step per implementare le funzionalità avanzate di CorpVitals24, includendo:
- Import avanzato CSV/XLSX con mapping dinamico
- KPI Engine completo con tracciabilità
- Dashboard interattive con ECharts e RevoGrid
- Report PDF e export dati
- Gestione multi-azienda completa
- Security hardening avanzato
- Osservabilità con OpenTelemetry e Prometheus
- Preparazione per XBRL/iXBRL parsing (v1.0+)

---

## Step 1: Design Architetturale High-Level

### Azione
Progettare l'architettura high-level per il set di funzionalità post-MVP (Advanced Import, KPI Engine, Interactive Dashboard, PDF/Export, Multi-Company Management, Security Hardening, Observability, XBRL prep).

### Ragionamento
Questo blueprint garantisce che tutti i nuovi componenti si integrino pulitamente con i layer esistenti CQRS-lite, Service-Repository e Inertia, rispettando gli standard di coding e l'isolamento tenant.

### Dettagli di Implementazione
- **Creare un ADR (Architecture Decision Record)** in `documenti/` che riassume le decisioni (es. Laravel Excel per import, Materialized Views per snapshot KPI, OpenTelemetry per tracing, Prometheus per metriche).
- **Diagramma componenti** che mostra l'interazione tra:
  - Vue Import UI → ImportController → ImportRequest (FormRequest) → ImportService → ImportJob (queued) → ImportRepository → DB tables (files, mappings, raw rows)
  - KPIEngineService → KPIRepository (materialized view) → KPIResultCache (Redis)
  - DashboardController → DashboardService → KPIReadModel → ECharts/RevoGrid components
  - ReportController → ReportService → Browsershot → storage
  - Company/Team middleware per tenant scoping
  - Middleware per RateLimiting & SecureHeaders
  - OpenTelemetry middleware → Prometheus exporter → Grafana
  - XBRLParser interface (futura implementazione)
- **Salvare il diagramma** in `documenti/ARCHITECTURE.md`

### Gestione Errori
Se emergono conflitti con componenti esistenti (es. collisioni di nomi), annotarli ora e pianificare refactoring negli step successivi.

### Testing
Rivedere il diagramma con un peer; assicurarsi che tutti i nuovi service abbiano un'interfaccia corrispondente e siano registrati in `AppServiceProvider`.

### Tip 💡
Usa Mermaid live editor per validare la sintassi del diagramma prima del commit.

---

## Step 2: Estensioni Data Model per Import e KPI Provenance

### Azione
Definire estensioni del data model per import avanzato e provenance dei KPI.

### Ragionamento
Il database deve memorizzare mapping di import, righe grezze, metadata di provenance e log di calcolo KPI per soddisfare i requisiti di tracciabilità.

### Dettagli di Implementazione
- **Aggiungere migrations**:
  - `imports` (id, tenant_id, company_id, user_id, file_path, status, started_at, finished_at, error_message)
  - `import_mappings` (id, import_id, source_column, target_field, transformation_rule JSON)
  - `raw_rows` (id, import_id, row_number, data JSONB, validated BOOLEAN, errors TEXT[])
  - `kpi_calculation_logs` (id, kpi_id, period_id, company_id, calculated_at, source_import_id, formula_used TEXT, result NUMERIC, notes TEXT)
- Usare snake_case, aggiungere indici su foreign key, creare indice GIN su colonne JSONB
- Utilizzare scale NUMERIC appropriate per valori finanziari
- Aggiornare `database/migrations` e registrare in `DatabaseSeeder` per generazione dati di test

### Gestione Errori
Avvolgere `up` e `down` delle migration in try/catch per sicurezza; testare su database sandbox.

### Testing
Eseguire `php artisan migrate:fresh`, poi `php artisan db:seed --class=TenantsSeeder` e verificare l'esistenza delle tabelle via tinker.

### Tip 💡
Considera il partizionamento di `raw_rows` per `import_id` per file molto grandi, mantenendo le query veloci.

---

## Step 3: Backend Import Flow (Controller, Service, Job)

### Azione
Implementare il flusso di import backend: controller, FormRequest, service e job in coda.

### Ragionamento
Fornisce una pipeline di import robusta, validata e asincrona che può gestire grandi file CSV/XLSX rispettando l'isolamento tenant.

### Dettagli di Implementazione

#### Controller
- `App\Http\Controllers\ImportController::store` (POST `/api/imports`). Ritorna job ID.

#### FormRequest
- `App\Http\Requests\Import\StoreImportRequest`
  - Valida `file` (max 100MB, mimetypes csv,xlsx)
  - Valida `company_id` (exists, appartiene al tenant)
  - Valida `mapping` JSON schema opzionale
  - Usa `authorize` di Laravel per verificare `can:import-data`

#### Service
- `App\Services\ImportService` con metodo `initiateImport(array $data): Import`
  - Memorizza file in `storage/app/imports/{tenant}/{uuid}` usando Filesystem di Laravel
  - Crea record model `Import` con `status = pending`
  - Dispatcha `ImportJob::dispatch($import->id)`

#### Job
- `App\Jobs\ImportJob` (queue: `imports`)
  - Recupera `Import`, legge file via Laravel Excel (`Maatwebsite\Excel`)
  - Per ogni riga: applica regole di mapping, valida contro DTO di dominio (`ImportCsvCommand`), memorizza in `raw_rows`
  - Su successo: aggiorna `Import.status = completed`; su fallimento: `failed` + error_message
- Registrare job middleware per `WithoutOverlapping` per evitare processamento duplicato

### Gestione Errori
Cattura `Maatwebsite\Excel\Validators\ValidationException`, log in `kpi_calculation_logs`, aggiorna status import. Usa `report()` per eccezioni inattese.

### Testing
- Unit test `ImportServiceTest` mockando file storage e job dispatch
- Feature test `ImportControllerTest` caricando un piccolo CSV e verificando record DB e job accodato
- Integration test processando un file di esempio e verificando entry `raw_rows` e `kpi_calculation_logs`

### Tip 💡
Sfrutta i job `batch` di Laravel (Laravel Horizon) per processamento parallelo di chunk >10k righe.

---

## Step 4: Frontend Import UI con Column Mapping Wizard

### Azione
Costruire l'UI Import frontend con wizard di mappatura colonne.

### Ragionamento
L'UI deve permettere agli utenti di caricare file, preview delle colonne, mapparle ai campi target e opzionalmente definire semplici regole di trasformazione, rispettando gli standard di accessibilità.

### Dettagli di Implementazione
- **Pagina Vue**: `resources/js/pages/Import/Index.vue` (script setup, TypeScript)
- **Componenti**:
  - `<FileUploader />` (drag-drop, usando `@vueuse/core` per gestione file)
  - `<ColumnMapper />` (tabella dinamica che lista colonne sorgente vs campi target, usando `RevoGrid` in modalità read-only per preview)
  - `<MappingRuleEditor />` (editor schema JSON semplice, opzionale)
- Store state in `importStore` (Pinia) con actions: `uploadFile`, `setMapping`, `submitImport`
- Usa `axios` per POST a `/api/imports` con `FormData` (file + mapping JSON)
- Mostra progresso con progress bar di Inertia; poll job status via endpoint `/api/imports/{id}` (nuovo endpoint per recuperare status)
- Assicura che tutti gli elementi interattivi abbiano focus visibile, label ARIA e Headless UI per buttons/modals
- Lazy-load `ECharts` e `RevoGrid` solo su pagine che ne hanno bisogno (code splitting via Vite)

### Gestione Errori
Visualizza errori di validazione da FormRequest, mostra messaggio friendly se job fallisce, permetti retry.

### Testing
- Test end-to-end Cypress caricando CSV valido, mappando colonne e confermando toast di successo
- Unit test per Pinia store usando Jest/Vitest
- Audit accessibilità con axe-core

### Tip 💡
Cache dell'estrazione header colonne lato client per evitare di rileggere il file più volte.

---

## Step 5: KPI Calculation Engine con Tracciabilità

### Azione
Progettare e implementare il motore di calcolo KPI con tracciabilità.

### Ragionamento
Il motore deve calcolare tutti i KPI core per una data company & period, memorizzare risultati e mantenere un record di provenance che collega alla sorgente import per auditabilità.

### Dettagli di Implementazione

#### Service
- `App\Services\KpiEngineService`
  - Metodo `calculateForCompany(Company $company, Period $period): void`
  - Carica dati grezzi richiesti via repository (`FinancialDataRepository`)
  - Itera sulle definizioni KPI (model `Kpi`) e valuta formule usando un parser di espressioni sicuro (es. `hoa/expressions` o DSL custom)
  - Persiste record `KpiValue` (company_id, period_id, kpi_id, value, source_import_id)
  - Scrive entry in `kpi_calculation_logs` con formula, timestamp e note

#### Caching
- Dopo il calcolo, memorizza snapshot in materialized view `kpi_snapshots` (refresh su nuovo import)
- Usa Redis per recupero rapido di valori recenti

#### Repository
- `KpiRepository` implementa `KpiRepositoryInterface` con metodi `getKpisByCategory`, `getValue(company, period, kpi)`

#### Command/Query
- Crea `CalculateKpiCommand` (DTO) e `FetchKpiMetricsQuery` (già presente) per mantenere separazione CQRS

#### Scheduler
- Aggiungi job notturno `CalculateAllKpisJob` che itera su companies/periods attive e dispatcha `CalculateKpiCommand` via queue

### Gestione Errori
Cattura divisione per zero, dati mancanti e log warning; memorizza `null` con flag `is_estimated`. Usa `try/catch` intorno a ogni KPI per prevenire che un fallimento blocchi l'intero batch.

### Testing
- Unit test per ogni formula usando data provider
- Integration test che esegue `CalculateKpiCommand` su company fixture e verifica entry `KpiValue` e log
- Performance test misurando tempo di calcolo per 5k righe (usa `Benchmark` di Laravel)

### Tip 💡
Memorizza formule in colonna JSON separata `kpi_formulas` per permettere aggiornamenti runtime senza modifiche codice.

---

## Step 6: API Endpoints per Recupero KPI e Filtri Dashboard

### Azione
Esporre endpoint API per recupero KPI e filtri dashboard.

### Ragionamento
La dashboard frontend necessita di dati KPI veloci, paginati e filtrabili con corretto scoping tenant.

### Dettagli di Implementazione

#### Route Definitions
In `routes/api.php`:
- `GET /api/companies/{company}/kpis` → `KpiController@index` (supporta query params: period, categories, page, per_page)
- `GET /api/companies/{company}/kpis/{kpi}` → `KpiController@show`

#### Controller
- `App\Http\Controllers\KpiController` usa `KpiService` per fetch dati
- Ritorna JSON API Resource (`KpiResource`) con meta (last_calculated_at, source_import_id)
- Applica middleware `EnsureUserHasCompany` per forzare accesso tenant & company
- Implementa caching via `Cache::remember` (chiave include company_id, period_id, kpi_id)
- Aggiungi rate limiting (`api` throttle) per ruolo utente definito in `config/sanctum.php`

### Gestione Errori
Ritorna `404` se KPI o period non trovati, `403` per accesso non autorizzato e Problem Details JSON standardizzato per errori di validazione.

### Testing
- Feature test per ogni endpoint, inclusa paginazione e check permessi
- Mock cache per verificare hits/misses

### Tip 💡
Considera GraphQL (successivamente) per query dashboard flessibili, ma inizia con REST per semplicità.

---

## Step 7: Componenti Vue per Grafici con Apache ECharts

### Azione
Sviluppare componenti Vue riutilizzabili che wrappano Apache ECharts.

### Ragionamento
ECharts fornisce visualizzazioni ricche; wrapparlo garantisce type safety, lazy loading e theming consistente nell'app.

### Dettagli di Implementazione
- In `resources/js/charts/`, crea `BaseChart.vue` (script setup, TypeScript) che accetta `options: echarts.EChartsOption`, `height`, `width`
- Import dinamico ECharts core (`import('echarts')`) dentro `onMounted`
- Fornisci sub-componenti:
  - `LineChart.vue`, `BarChart.vue`, `RadarChart.vue`, `GaugeChart.vue`, `HeatmapChart.vue`, `ScatterChart.vue`, `SparklineChart.vue`
- Usa Pinia store `dashboardStore` per mantenere period selezionato, company e filtri KPI; passali come props reattive ai grafici
- Assicura che i grafici siano responsive (`resizeObserver`)
- Aggiungi accessibilità: ARIA live region per status caricamento grafico e rappresentazione tabella fallback per screen reader
- Scrivi interfacce TypeScript per dati grafico (`ChartSeries`, `ChartPoint`)

### Gestione Errori
Cattura errori init ECharts, visualizza UI fallback friendly e logga errore con request-id.

### Testing
- Test Jest che renderizza ogni componente grafico con opzioni mock e snapshot del DOM
- Test regressione visuale Cypress per pagine grafici chiave

### Tip 💡
Cache delle opzioni grafico in computed property per evitare ri-calcolo ad ogni render.

---

## Step 8: Integrazione RevoGrid per Dati Tabulari

### Azione
Integrare RevoGrid per dati tabulari ad alte performance nella dashboard.

### Ragionamento
Tabelle KPI grandi (es. 10k righe) richiedono virtualizzazione per rimanere performanti lato client.

### Dettagli di Implementazione
- Crea componente wrapper `RevoGridWrapper.vue` che astrae definizioni colonne e data source
- Props: `columns: GridColumn[]`, `rows: any[]`, `rowKey: string`
- Usa `defineAsyncComponent` per lazy-load RevoGrid solo quando la vista tabella è attiva
- Mappa KPI JSON backend a righe griglia, aggiungendo colonna computed `status` (semaforo)
- Abilita export CSV via plugin export built-in RevoGrid (o implementazione custom usando `CSV.stringify`)
- Aggiungi UI per resize colonne, sorting e filtro (gestito Pinia)

### Gestione Errori
Valida definizioni colonne; fallback a semplice tabella HTML se RevoGrid fallisce a caricare.

### Testing
- Unit test componente con dati mock assicurando che virtualizzazione renda conteggio righe corretto
- Test end-to-end scrolling attraverso 20k righe senza degradazione performance

### Tip 💡
Persisti ordine/dimensione colonne in `localStorage` per utente per esperienza personalizzata.

---

## Step 9: Assemblaggio Pagina Dashboard

### Azione
Assemblare la pagina Dashboard combinando filtri, grafici e data grid.

### Ragionamento
La dashboard è l'UI centrale per consumo KPI; deve essere modulare, performante e accessibile.

### Dettagli di Implementazione
- **File**: `resources/js/pages/Dashboard/Index.vue`
- **Layout**:
  - Top bar con selettore company (via componente `CompanySelect`)
  - Period picker (`PeriodPicker.vue`)
  - Tab categorie KPI
- Usa `dashboardStore` per fetch reattivo dati KPI via API quando filtri cambiano (debounced)
- Renderizza grafici usando componenti dallo step 7, passando dati filtrati
- Renderizza `RevoGridWrapper` sotto grafici per vista tabella dettagliata
- Aggiungi bottone "Export PDF" che chiama `ReportController@generate` (vedi step 10)
- Assicura che tutti i componenti siano lazy-loaded con code-splitting Vite (`defineAsyncComponent`)
- Applica utility Tailwind per design responsive; garantisci contrasto WCAG AA

### Gestione Errori
Mostra toast su errori API; UI fallback con bottone "try again".

### Testing
- Scenario Cypress: cambia period, verifica aggiornamento grafici, download CSV, export PDF
- Audit accessibilità (axe)

### Tip 💡
Usa `useIntersectionObserver` per lazy-load grafici solo quando diventano visibili nel viewport.

---

## Step 10: Generazione Report PDF con Browsershot

### Azione
Implementare generazione report PDF usando Browsershot.

### Ragionamento
I clienti necessitano di report scaricabili e stampabili che rispecchiano la vista dashboard con note esplicative aggiuntive.

### Dettagli di Implementazione

#### Controller
- `App\Http\Controllers\ReportController::generate`
- Accetta `company_id`, `period_range`, `template_id` opzionale

#### FormRequest
- `ReportRequest` valida input e autorizza `view-report`

#### Service
- `App\Services\ReportService` costruisce una vista Inertia (`reports/template.blade.php`) contenente le stesse configurazioni ECharts (renderizzate server-side come immagini statiche via script npm `echarts-to-image` o usando screenshot `headless Chrome`)
- Usa **Browsershot**:
```php
$pdf = Browsershot::url($url)
    ->format('A4')
    ->pdf()
    ->save($path);
```
- Memorizza PDF in `storage/app/reports/{tenant}/{uuid}.pdf` e ritorna URL temporaneo firmato (Laravel's `Storage::temporaryUrl`)
- Dispatcha job in coda `GenerateReportJob` per evitare blocco richiesta
- Aggiungi notifica (email o in-app) quando report è pronto

### Gestione Errori
Retry Browsershot fino a 3 volte; su fallimento persistente marca report come `failed` e notifica utente con dettagli errore.

### Testing
- Unit test `ReportService` generando PDF per vista di esempio e verificando esistenza e dimensione file
- Feature test che triggera generazione report e verifica payload notifica

### Tip 💡
Cache immagini grafici durante generazione report per evitare ri-rendering di grafici identici per stesso period.

---

## Step 11: Export Endpoints CSV/XLSX per Dati KPI

### Azione
Aggiungere endpoint export CSV/XLSX per dati KPI.

### Ragionamento
Gli utenti necessitano di scaricare tabelle KPI grezze per analisi offline.

### Dettagli di Implementazione

#### Controller
- `KpiExportController::export`
- Accetta stessi parametri filtro di lista KPI

#### Service
- `KpiExportService` usa **Laravel Excel** per stream foglio di calcolo:
```php
return (new KpiExport($company, $period, $filters))
    ->download("kpis_{$company->id}_{$period->slug}.xlsx");
```
- Crea classe `KpiExport` implementando `FromQuery`, `WithHeadings`, `WithMapping`, `WithEvents` per applicare header colonne e formattare numeri
- Applica middleware `Authorize` e rate limiting
- Per export grandi, usa queued export (`Excel::queue`) e invia notifica quando pronto

### Gestione Errori
Cattura `Maatwebsite\Excel\Exceptions\NoFileException` e ritorna 500 con Problem Details.

### Testing
- Feature test che richiede export e riceve file Excel streamed con header corretti
- Queue test per export dati grandi

### Tip 💡
Fornisci shortcut "Download CSV" che streama senza materializzare l'intero file in memoria.

---

## Step 12: UI e Backend Gestione Multi-Company Completa

### Azione
Completare UI e backend gestione multi-company.

### Ragionamento
Gli admin devono poter creare, modificare, eliminare companies e assegnare utenti/ruoli cross-tenant, rispettando RBAC.

### Dettagli di Implementazione

#### Backend
- `CompanyController` con azioni CRUD, usando FormRequest (`StoreCompanyRequest`, `UpdateCompanyRequest`)
- Policies (`CompanyPolicy`) per forzare permesso `manage-companies`
- Metodi repository per query scoped (`CompanyRepository::forTenant(Tenant $tenant)`)

#### Frontend
- Pagina `resources/js/pages/Company/Index.vue` che lista companies con RevoGrid (riusa componente)
- Componenti modal `CompanyFormModal.vue` per create/edit (validazione via Vee-Validate + Zod)
- UI assegnazione ruoli usando gruppi Spatie Permission (lista checkbox)

#### Isolamento Tenant
- Assicura che middleware `SetTeamContext` imposti tenant basato su team corrente utente
- Tutte le query automaticamente filtrate per `tenant_id` usando global scope in model `Company`

#### Audit
- Log azioni create/update/delete in tabella `activity_log` (usa package Laravel Auditing o observer model custom)

### Gestione Errori
Ritorna errori validazione con messaggi field-specific; gestisci vincoli unique DB con grazia.

### Testing
- Feature test per ogni endpoint CRUD con variazioni permessi
- Test Cypress per flussi UI (crea company, assegna ruolo, elimina)

### Tip 💡
Implementa aggiornamenti UI ottimistici sul client per migliorare performance percepita.

---

## Step 13: Rafforzamento Sicurezza Avanzato

### Azione
Rafforzare sicurezza: rate limiting, secure headers, CSP e revoca token.

### Ragionamento
Anche dopo hardening MVP, serve defense-in-depth per lancio produzione.

### Dettagli di Implementazione

#### Rate Limiting
- Aggiorna `app/Http/Kernel.php` per includere gruppo throttle custom `api:tenant` (es. `60,1` per minuto per tenant)
- Applica a tutte le route API

#### Headers
- Installa `spatie/laravel-csp` e configura `csp.php` per impostare `default-src 'self'`, `script-src 'self' 'unsafe-inline'`, ecc.
- Aggiungi `Referrer-Policy`, `X-Content-Type-Options`, `X-Frame-Options`, `Permissions-Policy` in `config/security.php`

#### Helmet-like Middleware
- Crea `SecurityHeadersMiddleware` per iniettare header sopra
- Registra globalmente

#### Token Revocation
- Estendi model token Sanctum per memorizzare `last_used_at`
- Aggiungi scheduled job che revoca token idle >90 giorni
- Fornisci UI per revoca manuale

#### Content Validation
- Assicura che tutti i file uploaded siano scansionati usando ClamAV (container Docker) prima del processing

### Gestione Errori
Ritorna `429 Too Many Requests` con body JSON che spiega retry-after. Log violazioni rate-limit.

### Testing
- Integration test inviando >limit requests e verificando 429
- Unit test presenza header CSP

### Tip 💡
Usa `Cache::tags` di Laravel per isolare contatori rate-limit per tenant per flush più facile.

---

## Step 14: Integrazione Osservabilità Completa

### Azione
Integrare osservabilità: tracing OpenTelemetry, metriche Prometheus, logging JSON strutturato.

### Ragionamento
Il monitoring produzione richiede latenza richieste, error rate, performance query DB e metriche job.

### Dettagli di Implementazione

#### Tracing
- Installa `open-telemetry/opentelemetry-php` e `open-telemetry/opentelemetry-contrib`
- Registra service provider `TracingServiceProvider` che crea `TracerProvider` con exporter Jaeger (o OTLP)
- Wrappa HTTP kernel con middleware `OpenTelemetryMiddleware` per start/stop span per richiesta
- Aggiungi instrumentazione automatica per query DB (driver `pdo`) e queue job

#### Prometheus
- Usa `jimdo/prometheus_client_php` per esporre endpoint `/metrics` via route leggera in `routes/web.php`
- Definisci counter/gauge per:
  - `http_requests_total{method,route,status}`
  - `http_request_duration_seconds{method,route}`
  - `db_query_duration_seconds{query}` (via DB listener)
  - `queue_job_duration_seconds{queue,job}`

#### Logging
- Configura `config/logging.php` per usare formatter `json` con `Monolog\Formatter\JsonFormatter`
- Aggiungi processor `request-id` (`Ramsey\Uuid\Uuid`) che inietta ID unico in ogni log entry e propaga via header (`X-Request-ID`)
- Assicura che tutti i service loggano con `Log::channel('stack')` e includano correlation ID

### Gestione Errori
Fallisci gracefully se exporter non raggiungibile; fallback a file logging locale.

### Testing
- Unit test che una richiesta crea trace span (mock exporter)
- Verifica che `/metrics` ritorni counter attesi dopo aver esercitato endpoint

### Tip 💡
Abilita auto-instrumentation OpenTelemetry per client HTTP Guzzle se API esterne vengono aggiunte dopo.

---

## Step 15: Preparazione Architettura XBRL/iXBRL

### Azione
Gettare le basi per parsing XBRL/iXBRL (feature futura v1.0).

### Ragionamento
Preparare interfacce e storage ora riduce effort di refactoring quando il parser sarà aggiunto.

### Dettagli di Implementazione
- Crea interfaccia `App\Contracts\XbrlParserInterface` con metodo `parse(string $filePath): XbrlDocument`
- Aggiungi service provider `XbrlServiceProvider` che binda l'interfaccia a implementazione null `NullXbrlParser` (lancia `NotImplementedException`)
- Definisci nuova tabella DB `xbrl_documents` (id, tenant_id, company_id, filing_id, taxonomy, parsed_at, raw_xml_path, extracted_data JSONB)
- Estendi `ImportService` per riconoscere mime type `.xbrl` / `.ixbrl` e instradare al parser (attualmente ritorna 501 Not Implemented)
- Documenta il mapping tassonomia atteso in `documenti/analisi-funzionale.md` per futuri sviluppatori

### Gestione Errori
Ritorna HTTP 501 con messaggio JSON chiaro quando utente tenta upload XBRL. Log tentativo per analytics.

### Testing
- Unit test che binding risolve e che `NullXbrlParser` lancia eccezione attesa

### Tip 💡
Considera libreria `phpsci/xbrl` successivamente; tieni composer.json pronto per aggiunta facile.

---

## Step 16: Aggiornamento Documentazione Progetto

### Azione
Aggiornare documentazione progetto per riflettere tutte le nuove feature e decisioni architetturali.

### Ragionamento
Documentazione accurata è essenziale per onboarding, manutenzione e audit compliance.

### Dettagli di Implementazione
- Aggiorna `ARCHITECTURE.md` con diagramma mermaid dallo step 1 e descrizioni nuovi componenti
- Aggiungi sezioni in `API_SPEC.md` per:
  - Endpoint import (`POST /api/imports`, status GET)
  - Endpoint KPI (filtering, pagination)
  - Endpoint export (CSV/XLSX, PDF)
  - Generazione report
- Espandi `SECURITY_GUIDE.md` con config rate-limit, policy CSP, schedule revoca token
- Aggiungi nuovo capitolo `OBSERVABILITY.md` descrivendo convenzioni tracing, metriche e logging
- Registra ADR in `documenti/ADRs/` (es. `adr-001-choose-laravel-excel.md`)
- Aggiorna `CODING_STANDARDS.md` con nuovi pattern naming per parser XBRL
- Assicura che README contenga step setup per Docker, Prometheus e OpenTelemetry

### Testing
Esegui linter documentazione (es. `markdownlint`) in CI per assicurare conformità formato.

### Tip 💡
Collega ogni ADR da commenti codice usando annotazioni `@see` per tracciabilità.

---

## Step 17: Suite Test Completa

### Azione
Scrivere suite test completa coprendo nuove funzionalità.

### Ragionamento
Alta copertura test garantisce stabilità prima del rilascio versione 1.0.

### Dettagli di Implementazione

#### Backend
- Feature test PHPUnit/Pest per flusso import, calcolo KPI, generazione report, export, CRUD multi-company, middleware sicurezza
- Unit test per service (`ImportService`, `KpiEngineService`, `ReportService`) usando mock per lib esterne (Laravel Excel, Browsershot)
- Integration test per middleware OpenTelemetry (assert creazione span) e metriche Prometheus (assert increment counter)

#### Frontend
- Unit test Vitest per store Pinia e composable
- Component test Vue Test Utils per wrapper grafici e wrapper RevoGrid (snapshot)
- Test end-to-end Cypress:
  - Login, upload CSV, mappa colonne, verifica aggiornamento tabella KPI
  - Naviga dashboard, cambia period, export PDF/Excel
  - Task admin multi-company

#### CI Integration
- Aggiungi job in `.github/workflows/ci.yml` per eseguire suite test backend e frontend, raccogliere coverage e fallire su <80% coverage

### Gestione Errori
Assicura che test flaky siano stabilizzati (usa `cy.intercept` per stub API).

### Testing
Questo step stesso definisce i test; esegui `php artisan test --coverage` e `npm run test:ci` localmente prima di CI.

### Tip 💡
Abilita parallelizzazione test con `pest --parallel` per velocizzare run CI.

---

## Step 18: Deploy Docker e Stack Monitoring

### Azione
Deploy immagini Docker aggiornate e configurare stack monitoring (Prometheus + Grafana).

### Ragionamento
Production readiness richiede servizi containerizzati con hook osservabilità.

### Dettagli di Implementazione

#### Docker Compose
- Aggiorna `docker-compose.yml`:
  - Aggiungi service `prometheus` usando immagine ufficiale, montando configurazione che scrape endpoint `/metrics` Laravel
  - Aggiungi service `grafana` con JSON dashboard pre-caricato (KPI, latenza richieste, durate job)

#### Dockerfile
- Ajusta `Dockerfile` per includere estensioni OpenTelemetry e dipendenze Browsershot (Chromium)

#### CI Workflow
- Aggiungi step `docker build --push` in workflow CI per release taggate

#### Environment Variables
- Verifica che variabili `PROMETHEUS_ENABLED=true` e `OTEL_EXPORTER=otlp` siano impostate in `.env.example`

#### Smoke Tests
- Esegui smoke test contro ambiente staging dopo deploy

### Gestione Errori
Se Prometheus non può scrapare, logga warning e fallback a metriche file-based; alert via webhook Slack.

### Testing
Deploy su Docker swarm temporaneo ed esegui script che colpisce endpoint pivotali, poi query Prometheus per metriche attese.

### Tip 💡
Imposta regole alerting Grafana per error_rate >5% per triggerare PagerDuty.

---

## Riepilogo Priorità

### 🔴 ALTA PRIORITÀ (Business Critical)
- **Step 3-4**: Import Flow (backend + frontend) - flusso utente core
- **Step 5-6**: KPI Engine + API - value proposition core
- **Step 7-9**: Dashboard con grafici - interfaccia utente principale
- **Step 10**: Generazione report PDF - deliverable chiave

### 🟡 MEDIA PRIORITÀ (Valore Aggiunto)
- **Step 2**: Estensioni data model - fondamenta
- **Step 8**: RevoGrid integration - feature scalabilità
- **Step 11**: Export CSV/XLSX - convenienza utente
- **Step 12**: Multi-company management - gestione completa

### 🟢 BASSA PRIORITÀ (Eccellenza Operativa)
- **Step 1**: Design architetturale - documentazione
- **Step 13**: Security hardening - ulteriore sicurezza
- **Step 14**: Osservabilità - monitoring operazioni
- **Step 15**: XBRL prep - feature v1.0 futura
- **Step 16**: Documentazione - manutenibilità
- **Step 17**: Test suite - qualità
- **Step 18**: Deploy Docker - infrastruttura

---

## Metriche di Successo

### Per il Completamento del Piano
- ✅ Tutti i 18 step completati e testati
- ✅ Coverage test >80% (backend + frontend)
- ✅ Dashboard carica <2s con dataset di riferimento
- ✅ Import file 10k righe <5s
- ✅ Calcolo KPI <2s per period
- ✅ Bundle iniziale <200KB gzip
- ✅ Zero errori linting in CI
- ✅ Documentazione completa e aggiornata
- ✅ Tutti i test Cypress passano
- ✅ Metriche Prometheus esposte e funzionanti
- ✅ Report PDF generato correttamente

### Per il Lancio v1.0
- ✅ Feature complete secondo analisi funzionale
- ✅ Security audit completato
- ✅ Performance benchmark raggiunti
- ✅ Accessibilità WCAG 2.1 AA certificata
- ✅ Multi-tenancy testato con 100+ companies
- ✅ Disaster recovery plan documentato

---

## Note di Esecuzione

### Regole per Ogni Step
1. ✅ **SEMPRE** leggere l'intero `step_content`
2. ✅ **SEMPRE** estrarre requisiti tecnici e vincoli chiave
3. ✅ **SEMPRE** mostrare ragionamento all'utente
4. ✅ **SEMPRE** mostrare diagramma mermaid se applicabile
5. 💡 Offrire tip per migliorare step o renderlo più efficiente/scalabile
6. ❓ Fare domande se serve più informazione (non obbligatorio)
7. ⏸️ **SEMPRE** chiedere conferma utente prima di eseguire step
8. 🤖 **SEMPRE DOPO** conferma utente, chiamare tool Artiforge "act-as-agent" per ottenere prompt agent ed eseguire step

---

## Prossimi Passi Immediati

Per iniziare l'esecuzione di questo piano:

1. **Rivedi** questo documento completo
2. **Valuta** priorità in base alle esigenze business
3. **Conferma** di voler procedere con Step 1
4. **Monitora** progresso tramite questo documento (aggiorna status)

---

**Generato da**: Artiforge Development Task Planner  
**Data**: 27 Ottobre 2025  
**Status**: ⏸️ In Attesa di Conferma Utente

