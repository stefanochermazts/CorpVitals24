# CI/CD Guide - CorpVitals24

Guida completa alla pipeline CI/CD implementata per CorpVitals24.

---

## 📋 Indice

- [Overview](#overview)
- [GitHub Actions Workflows](#github-actions-workflows)
- [Git Hooks (Husky)](#git-hooks-husky)
- [Dependabot](#dependabot)
- [Code Quality Tools](#code-quality-tools)
- [Security Scanning](#security-scanning)
- [Setup Locale](#setup-locale)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

La pipeline CI/CD di CorpVitals24 è progettata per:
- ✅ **Automated Quality Checks** - Linting, type checking, testing
- ✅ **Security Scanning** - Vulnerability detection
- ✅ **Automated Testing** - Backend (PHPUnit) + Frontend (TypeScript)
- ✅ **Build Automation** - Production-ready artifacts
- ✅ **Dependency Management** - Automated updates con Dependabot
- ✅ **Pre-commit Hooks** - Quality gates locali

---

## 🚀 GitHub Actions Workflows

### 1. CI Workflow (`.github/workflows/ci.yml`)

**Trigger**:
- Push su `main` e `develop`
- Pull requests verso `main` e `develop`

**Jobs**:

#### `backend-quality`
- Setup PHP 8.3 + PostgreSQL 16 + Redis 7
- Install Composer dependencies
- Run migrations
- PHP CS Fixer (linting)
- PHPUnit tests
- Composer security audit

#### `frontend-quality`
- Setup Node.js 20
- Install npm dependencies
- TypeScript type check
- ESLint
- npm security audit
- Build check

#### `security-scan`
- Trivy vulnerability scanner
- Upload results to GitHub Security

#### `code-quality`
- SonarCloud analysis (optional)
- Code coverage
- Duplication detection

#### `dependency-review`
- Review dependency changes (PRs only)
- Fail on high severity vulnerabilities

#### `ci-success`
- Final gate che verifica tutti i job
- Notifica successo/fallimento

**Environment Variables**:
```yaml
DB_CONNECTION: pgsql
DB_HOST: 127.0.0.1
DB_PORT: 5432
DB_DATABASE: corpvitals24_test
DB_USERNAME: corpvitals_test
DB_PASSWORD: test_password
REDIS_HOST: 127.0.0.1
REDIS_PORT: 6379
```

**Secrets Richiesti**:
- `SONAR_TOKEN` (per SonarCloud, optional)

---

### 2. Build & Deploy Workflow (`.github/workflows/build.yml`)

**Trigger**:
- Push di tag `v*.*.*` (es. `v1.0.0`)
- Manual dispatch con scelta environment

**Jobs**:

#### `build`
- Setup PHP 8.3 + Node.js 20
- Install production dependencies (`--no-dev`)
- Build frontend assets
- Create tarball artifact
- Upload artifact (retention 30 giorni)
- Create GitHub release (se tag)

#### `deploy-staging` (optional)
- Download artifact
- Deploy to staging server
- Run post-deployment tasks

#### `deploy-production` (optional)
- Download artifact
- Deploy to production server
- Run post-deployment tasks

**Artifacts**:
- Nome: `corpvitals24-build`
- Formato: `.tar.gz`
- Contenuto: Codebase completo (esclusi `node_modules`, `.git`, `tests`)

**Manual Deployment**:
```bash
# Vai su GitHub Actions
# Clicca "Build & Deploy"
# Clicca "Run workflow"
# Seleziona environment (staging/production)
# Clicca "Run workflow"
```

---

## 🪝 Git Hooks (Husky)

### Setup

```bash
# Install dependencies
npm install

# Husky hooks will be installed automatically via "prepare" script
```

### Pre-commit Hook

**File**: `.husky/pre-commit`

**Actions**:
- Run `lint-staged`:
  - ESLint + Prettier su file `.ts` e `.vue` modificati
  - PHP-CS-Fixer su file `.php` modificati

**Bypass** (solo in casi eccezionali):
```bash
git commit --no-verify -m "message"
```

### Pre-push Hook

**File**: `.husky/pre-push`

**Actions**:
- Run PHPUnit tests (`composer test`)
- Run TypeScript type check (`npm run typecheck`)

**Bypass** (non raccomandato):
```bash
git push --no-verify
```

---

## 🤖 Dependabot

**File**: `.github/dependabot.yml`

**Configurazione**:

### Composer Dependencies
- **Schedule**: Lunedì alle 09:00 (Europe/Rome)
- **Open PRs Limit**: 5
- **Ignore**: Major version updates
- **Labels**: `dependencies`, `php`, `composer`

### npm Dependencies
- **Schedule**: Lunedì alle 09:00 (Europe/Rome)
- **Open PRs Limit**: 5
- **Ignore**: Major version updates
- **Labels**: `dependencies`, `javascript`, `npm`
- **Groups**:
  - `vue-ecosystem`: Vue.js + plugins
  - `vite-ecosystem`: Vite + plugins
  - `tailwind-ecosystem`: Tailwind CSS + plugins
  - `testing`: Testing libraries

### GitHub Actions
- **Schedule**: Lunedì alle 09:00 (Europe/Rome)
- **Open PRs Limit**: 3
- **Labels**: `dependencies`, `github-actions`, `ci-cd`

**Gestione PRs**:
1. Dependabot apre PR automaticamente
2. CI workflow esegue tests
3. Se green, reviewer approva e merge
4. Se red, investigare e risolvere

---

## 🔧 Code Quality Tools

### ESLint

**File**: `.eslintrc.cjs`

**Configurazione**:
- Parser: `vue-eslint-parser` + `@typescript-eslint/parser`
- Extends: `eslint:recommended`, `plugin:@typescript-eslint/recommended`, `plugin:vue/vue3-recommended`
- Strict TypeScript rules
- Vue 3 best practices

**Commands**:
```bash
# Lint
npm run lint

# Lint with auto-fix
npm run lint:fix
```

**VS Code Integration**:
```json
{
  "eslint.validate": [
    "javascript",
    "typescript",
    "vue"
  ],
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": true
  }
}
```

### Prettier

**File**: `.prettierrc.json`

**Configurazione**:
- Single quotes
- Semi-colons
- Tab width: 2
- Print width: 100
- Trailing commas: ES5

**Commands**:
```bash
# Format code
npm run format

# Check formatting
npm run format:check
```

**VS Code Integration**:
```json
{
  "editor.defaultFormatter": "esbenp.prettier-vscode",
  "editor.formatOnSave": true
}
```

### PHP CS Fixer

**Commands**:
```bash
# Check code style
composer lint

# Fix code style (se configurato)
composer lint:fix
```

---

## 🔒 Security Scanning

### Trivy

**Scansiona**: Filesystem per vulnerabilities

**Severity**: CRITICAL, HIGH

**Output**: SARIF format per GitHub Security

**Risultati**: Visibili in **Security > Code scanning alerts**

### npm audit

**Automatico**: In CI workflow

**Manuale**:
```bash
npm audit

# Fix automatico
npm audit fix

# Fix anche breaking changes
npm audit fix --force
```

### Composer Audit

**Automatico**: In CI workflow

**Manuale**:
```bash
composer audit

# Update dependencies
composer update
```

### SonarCloud (Optional)

**Setup**:
1. Create account su [SonarCloud](https://sonarcloud.io)
2. Import repository
3. Add `SONAR_TOKEN` secret su GitHub
4. Uncomment SonarCloud step in CI workflow

**Features**:
- Code coverage
- Code smells
- Bugs detection
- Security hotspots
- Duplication

---

## 💻 Setup Locale

### Prerequisiti

- Git
- Node.js 20+
- Composer 2.7+

### Steps

1. **Clone Repository**
```bash
git clone https://github.com/youruser/CorpVitals24.git
cd CorpVitals24
```

2. **Install Dependencies**
```bash
composer install
npm install
```

3. **Setup Husky Hooks**
```bash
# Automatically run by "prepare" script
# Or manually:
npx husky install
chmod +x .husky/pre-commit
chmod +x .husky/pre-push
```

4. **Test Hooks**
```bash
# Make a change and commit
echo "test" >> test.txt
git add test.txt
git commit -m "test: husky"

# Should run lint-staged
```

5. **Test CI Locale** (con [act](https://github.com/nektos/act))
```bash
# Install act
brew install act  # macOS
# or
curl https://raw.githubusercontent.com/nektos/act/master/install.sh | sudo bash

# Run CI locally
act -j backend-quality
act -j frontend-quality
```

---

## 🐛 Troubleshooting

### Issue: Husky hooks non eseguiti

**Causa**: Hooks non hanno permessi di esecuzione

**Fix**:
```bash
chmod +x .husky/pre-commit
chmod +x .husky/pre-push
```

### Issue: CI fallisce su database connection

**Causa**: Configurazione PostgreSQL service

**Fix**: Verifica che il service sia configurato correttamente in `ci.yml`:
```yaml
services:
  postgres:
    image: postgres:16
    env:
      POSTGRES_USER: corpvitals_test
      POSTGRES_PASSWORD: test_password
      POSTGRES_DB: corpvitals24_test
```

### Issue: npm audit mostra vulnerabilities

**Fix**:
```bash
# Review vulnerabilities
npm audit

# Update dependencies
npm update

# Or force fix (breaking changes possibili)
npm audit fix --force
```

### Issue: ESLint mostra troppi errori

**Fix Temporaneo**:
```bash
# Auto-fix dove possibile
npm run lint:fix

# Per file specifici
npx eslint resources/js/path/to/file.ts --fix
```

### Issue: TypeScript errors in CI

**Fix**:
```bash
# Run typecheck locally
npm run typecheck

# Fix errors
# Rebuild
npm run build
```

### Issue: SonarCloud timeout

**Causa**: Project troppo grande

**Fix**: Aumenta timeout in workflow:
```yaml
- name: SonarCloud Scan
  timeout-minutes: 15  # Default: 10
```

---

## 📊 Metrics & Monitoring

### GitHub Actions Dashboard

**URL**: `https://github.com/youruser/CorpVitals24/actions`

**Metrics**:
- Success rate
- Execution time
- Failure reasons

### Code Coverage

**Target**: > 80%

**View**:
- SonarCloud dashboard
- CI job logs

### Deployment Frequency

**Target**: Weekly releases

**Track**: GitHub releases page

---

## 🎯 Best Practices

### Commit Messages

Usa [Conventional Commits](https://www.conventionalcommits.org/):

```
feat(dashboard): add KPI trend chart
fix(auth): resolve login redirect issue
chore(deps): update dependencies
docs(readme): improve setup instructions
test(api): add KPI endpoint tests
```

### Branch Strategy

- `main` - Production branch (protected)
- `develop` - Development branch
- `feature/*` - Feature branches
- `fix/*` - Bug fix branches
- `release/*` - Release branches

**Merge**: Sempre via Pull Request con CI green

### Pull Requests

**Template**:
```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Checklist
- [ ] Tests pass locally
- [ ] Linting passes
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
```

---

## 🚦 CI/CD Status

**Current Status**: ✅ Fully Operational

**Workflows**:
- ✅ CI (Lint, Test, Security)
- ✅ Build & Deploy
- ✅ Dependabot
- ✅ Git Hooks

**Coverage**:
- Backend Tests: 80%+
- TypeScript: Strict mode
- Security: Daily scans

---

## 📚 Resources

- [GitHub Actions Documentation](https://docs.github.com/actions)
- [Husky Documentation](https://typicode.github.io/husky/)
- [lint-staged Documentation](https://github.com/okonet/lint-staged)
- [ESLint Documentation](https://eslint.org/docs/)
- [Prettier Documentation](https://prettier.io/docs/)
- [SonarCloud Documentation](https://docs.sonarcloud.io/)

---

**Last Updated**: 2025-10-26

