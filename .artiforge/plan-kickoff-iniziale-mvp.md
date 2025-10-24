## Piano Artiforge — Kickoff iniziale — Bootstrap CorpVitals24 (MVP base)

Fonte unica: documenti in `/documenti` (AGENTS.md, DEVELOPMENT_GUIDE.md, STACK_VERSIONS.md, PROJECT_STRUCTURE.md, CODING_STANDARDS.md, BEST_PRACTICES.md, SECURITY_GUIDE.md, ARCHITECTURE.md, DATABASE_DESIGN.md, API_SPEC.md, OBSERVABILITY.md, DEV_WORKFLOW.md, PERFORMANCE.md, CONFIGURATION.md, CONFIGS.md, EXAMPLES.md, analisi-funzionale.md).

Stack: PHP 8.3, Laravel 12.x, Vue 3.4, TS 5.6 strict, Vite 5, Tailwind 3, Postgres 16, Redis 7, Node 20. Qualità: ESLint 8 + Prettier 3 + PHP-CS-Fixer 3. CI: GitHub Actions. Osservabilità: JSON logging, OTel (placeholder), Prometheus (stub).

Regole: PSR-12, Controller sottili → Services, Repositories Postgres, FormRequest, Problem Details JSON, TS strict, import dinamici ECharts/RevoGrid, i18n lazy, Headless UI, Conventional Commits, lint bloccante in CI.

### Steps

1. Initialize Git e scaffold Laravel 12 con Composer/Laravel Installer; `.env.example`; script `scripts/setup.sh`.
2. Configurare Postgres/Redis in `.env.example` e `config/*`.
3. Installare e configurare Sanctum SPA; middleware `EnsureFrontendRequestsAreStateful` nel gruppo `web`.
4. Installare Spatie Permission con `teams=true`; modello `Team`; seed ruoli `admin|manager|viewer`.
5. Migrazioni iniziali: `tenants`, `companies`, `periods`, `kpis`, `kpi_values` con indici/unici come da DATABASE_DESIGN.md.
6. Application layer: DTO, `KpiRepository` (stub, cache Redis), `KpiService` (TTL breve, chiave deterministica companyId×periodId).
7. API v1: prefisso `/api/v1`, middleware `auth:sanctum` + `throttle:api (60/min)`, Problem Details globale; rotte health/companies/kpis/imports.
8. Frontend: Inertia + Vue 3 + TS, Vite alias `@`, Tailwind, Pinia, i18n; pagine Dashboard e Companies; import dinamici ECharts/RevoGrid; focus outline coerente.
9. Qualità e CI: ESLint/Prettier/TS strict, PHP-CS-Fixer, Husky + lint-staged, workflow CI (lint/test/typecheck/build/audit, build Docker su tag).
10. Osservabilità: `/api/v1/health`, canale logging JSON, placeholder OTel e Prometheus.
11. Docker (bozza): Dockerfile php-fpm, nginx config, docker-compose (app/web/db/redis).
12. Documentazione: README con setup e link a `/documenti`; aggiornare `documenti/todo.md` al termine.
13. Verifica end-to-end locale (install, key, migrate, seed, lint, test, build, health).

Note: Laddove applicabile, usare envelope `{ data, meta, links }` per liste; evitare N+1 con eager loading; errori API sempre in Problem Details JSON.


