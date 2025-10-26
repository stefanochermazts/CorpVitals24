# Configurazione & Gestione Segreti — CorpVitals24

Questa guida definisce come gestire configurazioni multi‑ambiente e segreti in modo sicuro e riproducibile.

## Indice
- Convenzioni `.env` e mapping in `config/*`
- Esempio `.env.example`
- Cache configurazione e bootstrap
- Segreti: Docker Secrets / Vault
- Snippet `docker-compose`
- Check runtime chiavi critiche

---

## Convenzioni `.env`
- Non committare mai `.env`.
- Ogni variabile deve avere un fallback ragionevole in `config/*` oppure essere obbligatoria (vedi check runtime).
- Variabili chiave:
  - App: `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_KEY`
  - DB: `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - Cache/Queue: `REDIS_HOST`, `QUEUE_CONNECTION`
  - Auth SPA: `SESSION_DOMAIN`, `SANCTUM_STATEFUL_DOMAINS`, `SESSION_SECURE_COOKIE`
  - Log: `LOG_CHANNEL=json`, `LOG_LEVEL=info`

## `.env.example`
```
APP_NAME=CorpVitals24
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=json
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=corpvitals
DB_USERNAME=postgres
DB_PASSWORD=postgres

REDIS_HOST=127.0.0.1
QUEUE_CONNECTION=database

SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SESSION_SECURE_COOKIE=false
```

## Cache configurazione
- In stage/prod eseguire:
```
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
- Nota: ogni modifica a file `config/*` richiede rigenerare la cache.

## Segreti (Docker/Vault)
- Docker Secrets: montare file con chiavi e leggerli all'avvio.
- Vault (HashiCorp o cloud provider): recupero runtime con caching e rotation.

Esempio lettura secret da file:
```php
// app/Support/env.php
function envFromFile(string $path, ?string $default = null): ?string {
  return is_readable($path) ? trim(file_get_contents($path)) : $default;
}
```

## docker-compose (estratto)
```
services:
  app:
    build: ./docker/php-fpm
    env_file: .env
    secrets:
      - db_password
  db:
    image: postgres:16
    environment:
      POSTGRES_PASSWORD_FILE: /run/secrets/db_password
secrets:
  db_password:
    file: ./secrets/DB_PASSWORD
```

## Check runtime chiavi critiche
```php
// app/Providers/AppServiceProvider.php (boot)
$required = ['APP_KEY','DB_HOST','DB_DATABASE','DB_USERNAME'];
$missing = array_values(array_filter($required, fn($k) => blank(env($k))));
if ($missing) {
    logger()->error('Missing env keys', ['keys' => $missing]);
    abort(500, 'Server misconfigured');
}
```

Best practice:
- Nominare chiaramente variabili per dominio (es. `KPI_SNAPSHOT_TTL`).
- Evitare chiavi duplicate tra ambienti; centralizzare in `docs/STACK_VERSIONS.md` e questa guida.
