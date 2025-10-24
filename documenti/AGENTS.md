## Project Overview

- **Scopo**: piattaforma multi-azienda per importare dati contabili/finanziari, calcolare KPI standard, visualizzarli in dashboard interattive e generare report.
- **Target**: PMI, startup, CFO fractional, studi commercialisti, incubatori.
- **Obiettivi**: import guidato (CSV/XLSX, XBRL/iXBRL in v1+), KPI engine tracciabile, dashboard con trend/confronti, report PDF, multi-tenant con ruoli.
- **Architettura**: layering chiaro (Presentation, Application, Domain, Infrastructure), CQRS‑lite (Commands/Queries + DTO), DI container.

## Technology Stack

- **Linguaggi**: PHP 8.3; TypeScript 5.6
- **Framework**: Laravel 12.x; Vue 3.4 con Inertia.js; Vite 5; Tailwind CSS 3
- **State/UX**: Pinia 2; vue‑i18n 9; Headless UI 1.x
- **Grafici e griglie**: Apache ECharts 5.x; RevoGrid 4.x (Web Component)
- **Validazione**: vee‑validate 4.x + Zod 3.x
- **DB e caching**: PostgreSQL 16.x; Redis 7 (cache/queue)
- **Export/Report**: Browsershot 6.x (PDF); Laravel Excel 3.x (XLSX/CSV)
- **Qualità/Tooling**: ESLint 8.x; Prettier 3.x; PHP‑CS‑Fixer 3.x; Husky 9; lint‑staged 15
- **Testing**: PHPUnit 10.x; Pest 2.x
- **Osservabilità**: OpenTelemetry PHP SDK 1.x; Prometheus Exporter 2.x/3.x
- **CI/CD**: GitHub Actions

## Coding Standards

- **PHP/Laravel**
  - PSR‑12; tipizzazione forte; Controller sottili che delegano a Services.
  - Services a singola responsabilità, DI via costruttore; Repositories con query Postgres ottimizzate ed eager loading (evitare N+1).
  - Validazione in `FormRequest` con messaggi localizzati; eccezioni di dominio mappate a Problem Details JSON.
  - Convenzioni DB: tabelle/colonne snake_case; tipi numerici adeguati (NUMERIC per valori finanziari; JSONB per metadati quando utile).
- **TypeScript/Vue 3**
  - SFC con `<script setup lang="ts">`; `strict` abilitato; evitare `any`.
  - Logica di pagina minima: usare `components/` e `composables/` per riuso; store Pinia tipizzati.
  - Import dinamici per moduli pesanti (ECharts, RevoGrid); i18n per namespace lazy‑loaded.
- **Accessibilità & UI**
  - Focus visibile coerente su tutti gli elementi interattivi; contrasto AA.
  - Headless UI per gestione ARIA/tastiera; descrizioni testuali per grafici e dati complessi.
- **Naming & struttura**
  - PHP: Classi PascalCase; metodi/variabili camelCase; DB snake_case.
  - Vue: componenti PascalCase; store con suffisso `Store`; utilities con nomi espliciti.
- **Commit/Linting**
  - Conventional Commits; ESLint+Prettier per TS/Vue; PHP‑CS‑Fixer per PHP; CI blocca violazioni.

## Project Structure

```
CorpVitals24/
  app/
    DTO/
    Services/
    Repositories/
    Models/
    Http/
      Controllers/
      Middleware/
      Requests/
    Policies/
    Providers/
  bootstrap/
  config/
  database/
    migrations/
    seeders/
    factories/
  resources/
    js/
      pages/
      components/
      stores/
      i18n/
      charts/
      grids/
      composables/
      utils/
    css/
    views/
  routes/
    api.php
    web.php
  public/
  storage/
  tests/
    Unit/
    Feature/
  docker/
    php-fpm/
    nginx/
    docker-compose.yml
  .github/
    workflows/
  docs/
    analisi-funzionale.md
    STACK_VERSIONS.md
    PROJECT_STRUCTURE.md
    registro-attivita.md
    todo.md
  scripts/
```

## External Resources

- **Documentazione**: Laravel, Vue 3, Inertia.js, Tailwind CSS, Headless UI, Apache ECharts, RevoGrid, PostgreSQL, OpenTelemetry PHP SDK, Prometheus.
- **Librerie**: `spatie/laravel-permission` (RBAC multi‑tenant), `laravel/browsershot` (PDF), `maatwebsite/excel` (XLSX/CSV).

## Additional Context

- **Sicurezza**: Sanctum per SPA (cookie‑based), RBAC multi‑tenant, headers (CSP, HSTS, X‑CTO, X‑Frame‑Options, Referrer‑Policy, Permissions‑Policy), rate limiting differenziato, segreti via `.env`/Secrets.
- **API**: REST v1 (`/api/v1`) con Problem Details JSON; envelope per liste; paginazione/filtri/sort standard; idempotenza via `Idempotency-Key` dove necessario.
- **Database**: schema normalizzato su PostgreSQL 16; MV per snapshot KPI; indici composti e GIN su JSONB; partizionamento per dataset grandi.
- **Osservabilità**: logging JSON con correlazione `request-id`; metriche Prometheus (latency per rotta, error rate, job/DB); tracing OpenTelemetry.
- **Performance**: cache Redis con chiavi deterministiche per azienda/periodo e TTL breve; import dinamico front‑end; code‑splitting; virtualizzazione griglie; bundle iniziale dashboard < 200KB gzip.
- **Non‑funzionali**: localizzazione IT/EN, accessibilità AA, dashboard < 2s con dataset di riferimento.

## Testing Instructions

- **Prerequisiti**
  - PHP 8.3 + Composer; Node 20 + npm; PostgreSQL 16; Redis 7.
- **Setup rapido**
  - Copia `.env.example` in `.env` e configura DB/Redis/APP_URL.
  - Installa dipendenze: `composer install` e `npm ci`.
  - Genera app key: `php artisan key:generate`.
  - Migrazioni/seed (se previsti): `php artisan migrate --seed`.
- **Test**
  - PHP: `composer test` (PHPUnit/Pest).
  - Typecheck: `npm run typecheck`.
  - Lint: `composer lint` e `npm run lint`.
  - (Opz.) E2E/a11y: Cypress con `axe-core` su dashboard.

## Build Steps

- **Sviluppo locale**
  - Avvio backend: `php artisan serve` (o stack Docker in `docker/`).
  - Frontend: `npm run dev`.
- **Build produzione**
  - Cache config/route/view: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
  - Build SPA: `npm run build`.
  - CI (GitHub Actions): install dipendenze, lint, test, typecheck, build; audit dipendenze; build Docker su tag.


