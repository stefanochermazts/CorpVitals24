# CorpVitals24

<div align="center">

![CorpVitals24 Logo](https://via.placeholder.com/200x80/4F46E5/FFFFFF?text=CorpVitals24)

**Piattaforma multi-azienda per analisi KPI contabili e finanziari**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=flat&logo=vue.js&logoColor=white)](https://vuejs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.9-3178C6?style=flat&logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?style=flat&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=flat&logo=redis&logoColor=white)](https://redis.io)

</div>

---

## 📋 Indice

- [Panoramica](#panoramica)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Prerequisiti](#prerequisiti)
- [Installazione](#installazione)
- [Configurazione](#configurazione)
- [Testing](#testing)
- [Deployment](#deployment)
- [Documentazione](#documentazione)
- [Sicurezza](#sicurezza)
- [Credenziali Demo](#credenziali-demo)
- [Contribuire](#contribuire)
- [Licenza](#licenza)

---

## 🎯 Panoramica

**CorpVitals24** è una piattaforma SaaS multi-tenant per l'importazione, calcolo e visualizzazione di KPI (Key Performance Indicators) contabili e finanziari, progettata per PMI, startup, CFO fractional, studi commercialisti e incubatori.

### Obiettivi

- ✅ **Import guidato** dati contabili (CSV/XLSX, XBRL/iXBRL in roadmap)
- ✅ **KPI Engine** tracciabile con formule configurabili
- ✅ **Dashboard interattive** con trend, confronti e drill-down
- ✅ **Report PDF** personalizzabili
- ✅ **Multi-tenant** con RBAC granulare

---

## ✨ Features

### Core Features (v1.0 MVP)

- 🔐 **Autenticazione Sicura**
  - Cookie-based SPA auth (Laravel Sanctum)
  - Multi-tenant RBAC (Spatie Permission)
  - Team isolation e company scoping
  - Rate limiting anti-brute-force (5 tentativi/min)

- 📊 **Dashboard KPI**
  - 15 KPI standard pre-configurati (ROI, ROE, EBITDA, etc.)
  - Visualizzazione real-time con cache Redis
  - Cards colorate e responsive
  - Period-based filtering

- 🛡️ **Security Hardening**
  - 8 Security Headers (CSP, HSTS, X-Frame-Options, etc.)
  - 4-tier Rate Limiting (API, Auth, Web, Global)
  - CORS configurabile
  - Input sanitization helpers
  - Request ID tracing

- 🎨 **Modern Frontend**
  - Vue 3.5 Composition API + TypeScript strict
  - Inertia.js SPA adapter (no API backend needed)
  - Tailwind CSS 4 per styling
  - Pinia state management
  - Flash notifications con Headless UI

- 🗄️ **Database & Performance**
  - PostgreSQL 16 con schema normalizzato
  - Redis 7 per cache e queues
  - Repository Pattern con eager loading
  - Cache TTL 5 minuti

### Roadmap v1.1+

- 📈 Grafici interattivi (Apache ECharts)
- 📤 Export XLSX/CSV (Laravel Excel)
- 📄 Report PDF (Browsershot)
- 🌍 Internazionalizzazione IT/EN (vue-i18n)
- 📊 Griglie virtualizzate (RevoGrid)
- 🔍 Import XBRL/iXBRL
- 📡 OpenTelemetry tracing
- 📊 Prometheus metrics

---

## 🛠️ Tech Stack

### Backend
- **Framework**: Laravel 12.x
- **Language**: PHP 8.3
- **Database**: PostgreSQL 16.x
- **Cache/Queue**: Redis 7.x
- **Auth**: Laravel Sanctum
- **RBAC**: Spatie Laravel Permission
- **Testing**: PHPUnit 10.x, Pest 2.x

### Frontend
- **Framework**: Vue 3.5 (Composition API)
- **Language**: TypeScript 5.9 (strict mode)
- **SPA Adapter**: Inertia.js
- **Styling**: Tailwind CSS 4.x
- **State**: Pinia 2.x
- **UI Components**: Headless UI 1.x
- **Form Validation**: VeeValidate 4.x + Zod 3.x
- **Build Tool**: Vite 7.x

### Infrastructure
- **Web Server**: Nginx
- **PHP**: PHP-FPM 8.3
- **Containerization**: Docker (in roadmap)
- **CI/CD**: GitHub Actions (in roadmap)

---

## 📦 Prerequisiti

- **PHP** >= 8.3 con ext: `mbstring`, `xml`, `curl`, `openssl`, `pdo_pgsql`, `redis`
- **Composer** >= 2.7
- **Node.js** >= 20.x
- **npm** >= 10.x
- **PostgreSQL** >= 16.x
- **Redis** >= 7.x

### Verifica Prerequisiti

```bash
# PHP version
php -v

# Composer version
composer --version

# Node.js version
node -v

# npm version
npm -v

# PostgreSQL version
psql --version

# Redis version
redis-cli --version
```

---

## 🚀 Installazione

### 1. Clona il Repository

```bash
git clone https://github.com/tuouser/CorpVitals24.git
cd CorpVitals24
```

### 2. Installa Dipendenze Backend

```bash
composer install
```

### 3. Installa Dipendenze Frontend

```bash
npm ci
```

### 4. Configura Environment

```bash
cp .env.example .env
php artisan key:generate
```

Modifica `.env` con le tue credenziali:

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=corpvitals24
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### 5. Setup Database

```bash
# Crea il database
createdb corpvitals24

# Esegui migrations + seed
php artisan migrate --seed
```

Questo creerà:
- ✅ 3 Teams (Studio Rossi, CFI, BAN)
- ✅ 9 Utenti (3 per team: admin, manager, viewer)
- ✅ 14 Companies
- ✅ 15 KPI standard
- ✅ 168 Periodi mensili (12 per company)
- ✅ ~1500 KPI Values

### 6. Build Frontend (Development)

```bash
# Dev server con HMR
npm run dev
```

In un altro terminale:

```bash
# Laravel dev server
php artisan serve
```

### 7. Accedi all'Applicazione

Apri [http://localhost:8000](http://localhost:8000)

---

## ⚙️ Configurazione

### Security Headers

I security headers sono configurati automaticamente (vedi `app/Http/Middleware/SecurityHeaders.php`):

- Content-Security-Policy (CSP)
- Strict-Transport-Security (HSTS)
- X-Content-Type-Options
- X-Frame-Options
- X-XSS-Protection
- Referrer-Policy
- Permissions-Policy

### Rate Limiting

Configurazione rate limits (vedi `app/Providers/AppServiceProvider.php`):

- **API**: 60 req/min per user/IP
- **Auth**: 5 login/min per IP (brute force protection)
- **Web**: 120 req/min per IP
- **Global**: 1000 req/hour per IP (DDoS protection)

### CORS

Configura origins consentiti in `.env`:

```env
CORS_ALLOWED_ORIGINS="http://localhost:8000,http://localhost:5173"
```

### Session & Cookies

```env
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false  # true in production
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

---

## 🧪 Testing

### Backend Tests

```bash
# PHPUnit/Pest
composer test

# Con coverage
composer test -- --coverage
```

### Frontend Tests

```bash
# TypeScript type checking
npm run typecheck

# Linting
npm run lint
```

### Code Quality

```bash
# PHP CS Fixer
composer lint

# ESLint
npm run lint

# Prettier
npm run format
```

---

## 🚀 Deployment

Vedi [DEPLOYMENT.md](./documenti/DEPLOYMENT.md) per istruzioni dettagliate.

### Quick Production Build

```bash
# 1. Build frontend
npm run build

# 2. Cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Optimize autoloader
composer install --optimize-autoloader --no-dev

# 4. Run migrations
php artisan migrate --force
```

### Environment Production

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
CORS_ALLOWED_ORIGINS="https://yourdomain.com"
```

---

## 📚 Documentazione

La documentazione completa si trova in `/documenti`:

### Guide Principali
- [AGENTS.md](./documenti/AGENTS.md) - Guida per AI agents
- [ARCHITECTURE.md](./documenti/ARCHITECTURE.md) - Architettura sistema
- [DATABASE_DESIGN.md](./documenti/DATABASE_DESIGN.md) - Schema database
- [SECURITY_IMPLEMENTATION.md](./documenti/SECURITY_IMPLEMENTATION.md) - Security guide

### Best Practices
- [BEST_PRACTICES.md](./documenti/BEST_PRACTICES.md) - Best practices generali
- [CODING_STANDARDS.md](./documenti/CODING_STANDARDS.md) - Standard di codice
- [DEV_WORKFLOW.md](./documenti/DEV_WORKFLOW.md) - Workflow sviluppo

### Technical Docs
- [API_SPEC.md](./documenti/API_SPEC.md) - API specification
- [PERFORMANCE.md](./documenti/PERFORMANCE.md) - Performance optimization
- [OBSERVABILITY.md](./documenti/OBSERVABILITY.md) - Monitoring & logging

### Frontend Docs
- [resources/js/stores/README.md](./resources/js/stores/README.md) - Pinia stores guide

---

## 🔒 Sicurezza

### Reporting Security Issues

Se trovi una vulnerabilità di sicurezza, **NON** aprire una issue pubblica. Invia una email a:

📧 **security@corpvitals24.test**

Risponderemo entro 24 ore.

### Security Features

- ✅ OWASP Top 10 compliance
- ✅ CSRF protection (Laravel + SameSite cookies)
- ✅ XSS prevention (CSP + input sanitization)
- ✅ SQL Injection prevention (Eloquent ORM)
- ✅ Rate limiting anti-brute-force
- ✅ Secure session management (HttpOnly, Secure, SameSite)
- ✅ Request ID tracing per audit trail
- ✅ Multi-tenant data isolation

Vedi [SECURITY_IMPLEMENTATION.md](./documenti/SECURITY_IMPLEMENTATION.md) per dettagli.

---

## 🎭 Credenziali Demo

Dopo il seed, puoi accedere con:

### Team: Studio Rossi

| Ruolo | Email | Password | Permessi |
|-------|-------|----------|----------|
| Admin | `studio-rossi-admin@example.com` | `password` | All (10/10) |
| Manager | `studio-rossi-manager@example.com` | `password` | 8/10 |
| Viewer | `studio-rossi-viewer@example.com` | `password` | 3/10 |

### Team: CFI (Consulenza Finanziaria Italia)

| Ruolo | Email | Password | Permessi |
|-------|-------|----------|----------|
| Admin | `cfi-admin@example.com` | `password` | All (10/10) |
| Manager | `cfi-manager@example.com` | `password` | 8/10 |
| Viewer | `cfi-viewer@example.com` | `password` | 3/10 |

### Team: BAN (Business Advisors Network)

| Ruolo | Email | Password | Permessi |
|-------|-------|----------|----------|
| Admin | `ban-admin@example.com` | `password` | All (10/10) |
| Manager | `ban-manager@example.com` | `password` | 8/10 |
| Viewer | `ban-viewer@example.com` | `password` | 3/10 |

⚠️ **ATTENZIONE**: Cambia queste password in produzione!

---

## 👥 Contribuire

Contributions are welcome! Per contribuire:

1. Fork il repository
2. Crea un branch per la tua feature (`git checkout -b feature/amazing-feature`)
3. Commit le modifiche (`git commit -m 'Add amazing feature'`)
4. Push al branch (`git push origin feature/amazing-feature`)
5. Apri una Pull Request

### Coding Standards

- Segui [PSR-12](https://www.php-fig.org/psr/psr-12/) per PHP
- Usa TypeScript strict mode per frontend
- Scrivi test per nuove features
- Mantieni copertura test > 80%
- Conventional Commits per commit messages

---

## 📄 Licenza

Questo progetto è proprietario. Tutti i diritti riservati.

© 2025 CorpVitals24. All rights reserved.

---

## 📞 Contatti

- **Website**: [https://corpvitals24.test](https://corpvitals24.test)
- **Email**: info@corpvitals24.test
- **Support**: support@corpvitals24.test
- **Security**: security@corpvitals24.test

---

## 🙏 Riconoscimenti

Costruito con:

- [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- [Vue.js](https://vuejs.org) - The Progressive JavaScript Framework
- [Inertia.js](https://inertiajs.com) - The Modern Monolith
- [Tailwind CSS](https://tailwindcss.com) - A utility-first CSS framework
- [PostgreSQL](https://www.postgresql.org) - The World's Most Advanced Open Source Database
- [Redis](https://redis.io) - The open source, in-memory data store

---

<div align="center">

Made with ❤️ by the CorpVitals24 Team

[⬆ Torna su](#corpvitals24)

</div>
