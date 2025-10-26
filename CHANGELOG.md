# Changelog - CorpVitals24

Tutte le modifiche significative a questo progetto saranno documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.0.0/),
e questo progetto aderisce al [Semantic Versioning](https://semver.org/lang/it/).

---

## [Unreleased]

### Pianificato v1.1
- Grafici interattivi con Apache ECharts
- Export XLSX/CSV con Laravel Excel
- Report PDF con Browsershot
- Internazionalizzazione IT/EN
- Import XBRL/iXBRL
- Griglie virtualizzate con RevoGrid

---

## [1.0.0] - 2025-10-26

### 🎉 Release Iniziale - MVP

Prima release production-ready di CorpVitals24.

### Added

#### Frontend
- **Vue 3.5** Setup con Composition API + TypeScript strict mode
- **Inertia.js** SPA adapter integrato
- **Tailwind CSS 4** per styling moderno e responsive
- **Pinia** state management con:
  - `authStore` per autenticazione
  - `dashboardStore` per KPI
  - `appStore` per UI state globale
- **Composables**:
  - `useStoreSync` per sync Inertia shared props
  - `useFlash` per flash messages
  - `usePageProps` per page props tipizzati
- **Components**:
  - `FlashMessages.vue` con Headless UI transitions
  - `Login.vue` con VeeValidate + Zod
  - `Dashboard/Index.vue` con KPI cards
  - `Welcome.vue` homepage

#### Backend
- **Laravel 12** base setup
- **Sanctum** SPA authentication cookie-based
- **Spatie Permission** RBAC multi-tenant
- **Controllers**:
  - `LoginController` per auth
  - `DashboardController` per KPI dashboard
- **Services**:
  - `DashboardService` con cache Redis
  - `KpiService` per business logic
- **Repositories**:
  - `KpiRepository` con query ottimizzate
- **Middleware**:
  - `SecurityHeaders` (8 headers OWASP)
  - `RequestId` per log correlation
  - `EnsureUserHasTeam` per multi-tenancy
  - `HandleInertiaRequests` per shared data
- **Policies**:
  - `TeamPolicy` per team authorization
  - `CompanyPolicy` per company authorization

#### Database
- **PostgreSQL 16** schema con 7 tabelle:
  - `tenants` - Top-level multi-tenant entity
  - `teams` - Spatie Permission teams
  - `companies` - Business entities
  - `users` - Users con team_id/company_id
  - `periods` - Accounting periods
  - `kpis` - KPI definitions
  - `kpi_values` - KPI values per period
- **Migrations** per tutte le tabelle
- **Factories** (7) per test data generation
- **Seeders** (5):
  - `RolesTableSeeder` - 3 ruoli (admin, manager, viewer)
  - `TenantsSeeder` - 3 tenants + 14 companies + 9 users
  - `KpisSeeder` - 15 KPI standard
  - `PeriodsSeeder` - 168 periodi mensili
  - `KpiValuesSeeder` - ~1500 valori di test

#### Security
- **8 Security Headers**:
  - Content-Security-Policy (environment-aware)
  - Strict-Transport-Security (HSTS)
  - X-Content-Type-Options
  - X-Frame-Options
  - X-XSS-Protection
  - Referrer-Policy
  - Permissions-Policy
  - X-Permitted-Cross-Domain-Policies
- **4-Tier Rate Limiting**:
  - API: 60 req/min
  - Auth: 5 login/min (brute force protection)
  - Web: 120 req/min
  - Global: 1000 req/hour (DDoS protection)
- **CORS** configuration con whitelist
- **Cookie Security**: HttpOnly, Secure, SameSite=lax
- **Helpers**: `SecurityHelper` con 10 metodi sanitization
- **Custom 429 page** per rate limit

#### Documentation
- **README.md** completo con setup instructions
- **DEPLOYMENT.md** guida production deployment
- **SECURITY_IMPLEMENTATION.md** (700+ righe)
- **ARCHITECTURE.md** architettura sistema
- **DATABASE_DESIGN.md** schema database
- **API_SPEC.md** specifiche API
- **BEST_PRACTICES.md** best practices
- **CODING_STANDARDS.md** standard codice
- **PERFORMANCE.md** ottimizzazioni
- **OBSERVABILITY.md** monitoring
- **DEV_WORKFLOW.md** workflow sviluppo
- **stores/README.md** guida Pinia stores

#### Configuration
- **.env.example** con tutte le variabili necessarie
- **config/cors.php** per CORS policy
- **config/session.php** secure defaults
- **config/sanctum.php** stateful domains
- **config/permission.php** multi-tenancy abilitato

#### Testing
- **Feature tests** per login/logout
- **TypeScript** type checking configurato
- **ESLint** + Prettier setup
- **PHP-CS-Fixer** per code style

### Changed
- Rate limiter spostato da `bootstrap/app.php` a `AppServiceProvider` per corretto lifecycle
- Session driver default: `redis` invece di `file`
- App locale: `it` invece di `en`

### Fixed
- Facade initialization error per `RateLimiter`
- Team isolation in Spatie Permission seeders
- N+1 queries in KPI repository con eager loading
- TypeScript errors in Inertia shared props
- Ziggy route helper import issues

### Security
- Implementati tutti controlli OWASP Top 10
- CSRF protection automatica via Sanctum
- XSS prevention con CSP + sanitization
- SQL Injection prevention con Eloquent ORM
- Brute force protection con rate limiting
- Multi-tenant data isolation

---

## [0.1.0] - 2025-10-22

### Added
- Documentazione tecnica iniziale (14 documenti)
- Stack versions e rationale
- Project structure planning
- Coding standards definition
- Security guidelines

---

## Legenda Emoji

- 🎉 Release maggiore
- ✨ Nuova feature
- 🐛 Bug fix
- 🔒 Security fix
- 📝 Documentazione
- ⚡ Performance
- 🎨 UI/UX
- 🔧 Configurazione
- 🗃️ Database
- 🔥 Breaking change

---

[Unreleased]: https://github.com/youruser/CorpVitals24/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/youruser/CorpVitals24/compare/v0.1.0...v1.0.0
[0.1.0]: https://github.com/youruser/CorpVitals24/releases/tag/v0.1.0

