# Quick Start Guide - CorpVitals24

Setup veloce per sviluppatori che vogliono iniziare subito.

---

## ⚡ Setup Rapido (5 minuti)

### 1. Prerequisiti

Assicurati di avere installato:
- PHP 8.3+
- Composer 2.7+
- Node.js 20+
- PostgreSQL 16+
- Redis 7+

### 2. Clone & Install

```bash
# Clone repository
git clone https://github.com/youruser/CorpVitals24.git
cd CorpVitals24

# Install dependencies
composer install
npm ci
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Modifica `.env`:

```env
DB_DATABASE=corpvitals24
DB_USERNAME=postgres
DB_PASSWORD=postgres

REDIS_HOST=127.0.0.1
```

### 4. Setup Database

```bash
# Create database
createdb corpvitals24

# Migrate + seed
php artisan migrate --seed
```

### 5. Start Development Servers

```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite (HMR)
npm run dev
```

### 6. Login

Apri [http://localhost:8000](http://localhost:8000) e accedi con:

```
Email: studio-rossi-admin@example.com
Password: password
```

🎉 **Done!** Sei pronto per sviluppare.

---

## 🔑 Credenziali Demo

### Teams Disponibili

| Team | Admin Email | Manager Email | Viewer Email |
|------|-------------|---------------|--------------|
| **Studio Rossi** | studio-rossi-admin@example.com | studio-rossi-manager@example.com | studio-rossi-viewer@example.com |
| **CFI** | cfi-admin@example.com | cfi-manager@example.com | cfi-viewer@example.com |
| **BAN** | ban-admin@example.com | ban-manager@example.com | ban-viewer@example.com |

**Password per tutti**: `password`

---

## 📂 Struttura Progetto

```
CorpVitals24/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controller sottili
│   │   ├── Middleware/       # Security, Inertia, ecc.
│   │   └── Requests/         # Form validation
│   ├── Services/             # Business logic
│   ├── Repositories/         # Data access
│   ├── Models/               # Eloquent models
│   ├── Policies/             # Authorization
│   └── Helpers/              # Utility functions
├── resources/
│   ├── js/
│   │   ├── pages/            # Vue pages (Inertia)
│   │   ├── components/       # Vue components
│   │   ├── stores/           # Pinia stores
│   │   ├── composables/      # Vue composables
│   │   └── types/            # TypeScript types
│   ├── css/                  # Tailwind CSS
│   └── views/                # Blade views
├── routes/
│   ├── web.php               # Web routes
│   └── api.php               # API routes
├── database/
│   ├── migrations/           # DB migrations
│   ├── seeders/              # Seeders
│   └── factories/            # Factories
└── documenti/                # Documentation
```

---

## 🛠️ Comandi Utili

### Development

```bash
# Start servers
php artisan serve                 # Laravel dev server
npm run dev                       # Vite HMR

# Database
php artisan migrate               # Run migrations
php artisan migrate:fresh --seed # Fresh DB + seed
php artisan db:seed               # Seed only

# Cache
php artisan cache:clear           # Clear all caches
php artisan config:clear          # Clear config cache
php artisan route:clear           # Clear route cache
```

### Testing

```bash
# Backend
composer test                     # PHPUnit/Pest
composer lint                     # PHP-CS-Fixer

# Frontend
npm run typecheck                 # TypeScript check
npm run lint                      # ESLint
npm run format                    # Prettier
```

### Build

```bash
npm run build                     # Production build
php artisan optimize              # Optimize app
```

---

## 🎯 Task Comuni

### Aggiungere un Nuovo KPI

1. **Database**: Aggiungi in `KpisSeeder.php`

```php
['code' => 'NEW_KPI', 'name' => 'Nome KPI', 'description' => '...', 'formula_refs' => []]
```

2. **Seed**: `php artisan db:seed --class=KpisSeeder`

### Creare un Nuovo Controller

```bash
php artisan make:controller NomeController
```

Segui il pattern: Controller → Service → Repository

### Aggiungere una Nuova Page Vue

1. Crea file: `resources/js/pages/NomePage.vue`
2. Aggiungi route in `routes/web.php`:

```php
Route::get('/nome', fn() => Inertia::render('NomePage'))->name('nome');
```

### Creare un Nuovo Store Pinia

1. Crea `resources/js/stores/nomeStore.ts`
2. Export in `resources/js/stores/index.ts`
3. Usa con: `const store = useNomeStore()`

---

## 🐛 Troubleshooting

### Errore: "Facade root has not been set"

```bash
php artisan config:clear
php artisan cache:clear
```

### Errore: Database connection failed

Verifica PostgreSQL:

```bash
psql -U postgres -l
createdb corpvitals24
```

### Errore: Redis connection refused

Verifica Redis:

```bash
redis-cli ping
# Se non parte:
sudo systemctl start redis-server
```

### Vite HMR non funziona

Assicurati che Vite server sia attivo:

```bash
npm run dev
# Deve mostrare: http://localhost:5173
```

---

## 📚 Documentazione

Per documentazione dettagliata, vedi:

- [README.md](./README.md) - Setup completo
- [DEPLOYMENT.md](./documenti/DEPLOYMENT.md) - Deploy production
- [ARCHITECTURE.md](./documenti/ARCHITECTURE.md) - Architettura
- [CODING_STANDARDS.md](./documenti/CODING_STANDARDS.md) - Standard codice
- [SECURITY_IMPLEMENTATION.md](./documenti/SECURITY_IMPLEMENTATION.md) - Security

---

## 💡 Tips

### IDE Setup (VS Code)

Installa extensions:
- Laravel Extension Pack
- Vue Language Features (Volar)
- TypeScript Vue Plugin (Volar)
- Tailwind CSS IntelliSense
- PHP Intelephense

### Git Hooks

```bash
npm install            # Installa Husky hooks
# Pre-commit: lint-staged
# Pre-push: tests
```

### Debug

```bash
# Laravel debug bar (dev only)
composer require barryvdh/laravel-debugbar --dev

# Vue DevTools
# Install browser extension
```

---

## 🚀 Next Steps

Dopo il setup:

1. ✅ Familiarizza con [ARCHITECTURE.md](./documenti/ARCHITECTURE.md)
2. ✅ Leggi [CODING_STANDARDS.md](./documenti/CODING_STANDARDS.md)
3. ✅ Esplora il codice in `app/` e `resources/js/`
4. ✅ Prova a creare una nuova feature
5. ✅ Contribuisci con una PR!

---

## 📞 Hai Bisogno di Aiuto?

- **Docs**: [/documenti](./documenti)
- **Issues**: [GitHub Issues](https://github.com/youruser/CorpVitals24/issues)
- **Email**: support@corpvitals24.test

---

**Buon Coding!** 🚀

