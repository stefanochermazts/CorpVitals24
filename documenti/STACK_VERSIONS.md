# Stack & Versions — CorpVitals24

Questa tabella è la fonte di verità per l'intero team e la CI. Mantiene allineate le versioni tra ambienti, prevenendo divergenze tra `composer.lock` e lockfile npm.

| Componente | Versione | Fonte | Note aggiornamento |
|---|---|---|---|
| PHP | 8.3 | runtime/docker | Aggiornare base image, verificare estensioni PHP |
| Laravel | 12.x | composer | `composer require laravel/framework:^12` |
| Inertia.js | 0.12.x | npm | Verificare adapter Laravel Inertia |
| Vue | 3.4.x | npm | Compatibile con `<script setup>` + TS |
| TypeScript | 5.6.x | npm | Verificare `vue-tsc` in CI |
| Vite | 5.x | npm | Code-splitting dinamico grafici |
| Tailwind CSS | 3.x | npm | Content paths aggiornati a Inertia |
| Headless UI | 1.x | npm | Componenti accessibili |
| Apache ECharts | 5.x | npm | Import dinamico per bundle leggeri |
| RevoGrid | 4.x | npm | Web Component; virtualizzazione |
| PostgreSQL | 16.x | runtime | Funzioni finestra, MV, GIN/BTREE |
| Pinia | 2.x | npm | Store tipizzati |
| vue‑i18n | 9.x | npm | Lazy-load namespaces |
| Day.js / Luxon | 1.x / 3.x | npm | Scegliere uno come default; timezone |
| vee‑validate | 4.x | npm | Integrazione con Zod |
| Zod | 3.x | npm | Schemi condivisi frontend |
| Browsershot | 6.x | composer | Requisito headless Chrome |
| Laravel Excel | 3.x | composer | Import/Export CSV/XLSX |
| axe‑core | 4.x | npm | A11y in e2e/CI |
| Storybook (opz.) | 8.x | npm | Documentazione pattern UI |
| ESLint | 8.x | npm (dev) | Config Vue + TS + Prettier |
| Prettier | 3.x | npm (dev) | Formattazione coerente |
| PHP‑CS‑Fixer | 3.x | composer (dev) | PSR‑12 |
| PHPUnit | 10.x | composer (dev) | Test unit/feature |
| Pest | 2.x | composer (dev) | DX test migliorata |
| OpenTelemetry PHP SDK | 1.x | composer | Tracing distribuito |
| Prometheus Exporter | 2.x/3.x | composer | Metriche per Grafana |

## Come aggiornare in sicurezza

1. Backend (Composer):
   - Aggiorna un singolo pacchetto: `composer update vendor/package --with-all-dependencies`
   - Aggiornamento framework: `composer update laravel/framework --with-all-dependencies`
   - Esegui test: `composer test`

2. Frontend (npm):
   - Aggiorna mirato: `npm i package@latest`
   - Allineamento minor/patch: `npm update`
   - Lint/Build: `npm run lint && npm run build`

3. Lockfile e CI:
   - Committa sia `composer.lock` che `package-lock.json`
   - La CI deve validare corrispondenza versioni rispetto a questo file

## Note

- Le versioni marcate `x` indicano minor/patch più recente compatibile. Bloccare major in produzione.
- Per librerie opzionali (Storybook, OpenTelemetry, Exporter Prometheus) installare solo se richieste dalla release.


