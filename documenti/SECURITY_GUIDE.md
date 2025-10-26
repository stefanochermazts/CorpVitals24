# Security Guide — CorpVitals24

Questa guida definisce l'implementazione di sicurezza per CorpVitals24: autenticazione (Sanctum), autorizzazione (RBAC multi‑tenant), protezione dei dati (in transito/at‑rest), security headers, CSRF, rate limiting, auditing e checklist OWASP di base.

## Indice
- Autenticazione (Laravel Sanctum per SPA)
- Autorizzazione (RBAC per tenant con spatie/laravel-permission)
- CSRF & CORS per SPA
- HTTP Security Headers (CSP, HSTS, Referrer-Policy, ecc.)
- Rate limiting
- Protezione dati (cifratura at‑rest, pgcrypto, gestione file)
- Gestione segreti & configurazione
- Logging & Audit
- Checklist OWASP ASVS L1 (baseline)

---

## Autenticazione — Laravel Sanctum (SPA cookies)

- Flusso consigliato per SPA Inertia: cookie‑based session con Sanctum e protezione CSRF nativa.
- Requisiti env:
  - `APP_URL=https://app.example.com`
  - `SESSION_DOMAIN=.example.com`
  - `SANCTUM_STATEFUL_DOMAINS=app.example.com`
  - `SESSION_SECURE_COOKIE=true` (prod), `SESSION_SAME_SITE=lax` o `strict`

Configurazione (estratti):
```php
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')), // + domini SPA
    'guard' => ['web'],
];
```

Middleware kernel:
```php
// app/Http/Kernel.php (estratto)
protected $middlewareGroups = [
    'web' => [
        // ...
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ],
];
```

Rotte protette:
```php
// routes/api.php (estratto)
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // ... API protette
});
```

Note:
- Impostare cookie Secure/HttpOnly in produzione.
- Disabilitare `debug` in prod e imporre HTTPS con HSTS.

---

## Autorizzazione — RBAC multi‑tenant

Libreria: `spatie/laravel-permission` (opzione "teams" per multi‑tenant).

Installazione (concettuale):
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

Abilitare "teams" per tenant:
```php
// config/permission.php (estratto)
'teams' => true,
```

Modello User:
```php
// app/Models/User.php (estratto)
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable {
  use HasRoles;
  protected $guard_name = 'web';
}
```

Esempio Policy:
```php
// app/Policies/CompanyPolicy.php (estratto)
public function view(User $user, Company $company): bool {
  return $user->companies()->whereKey($company->id)->exists();
}
```

Linee guida:
- Definire ruoli per tenant: `admin`, `manager`, `viewer`.
- Usare Policies per oggetti dominio (Company, Report, Import).
- Minimizzare permessi "wildcard"; preferire permessi granulari.

---

## CSRF & CORS (SPA)

- CSRF token è gestito in automatico da Sanctum nel gruppo `web`.
- Per richieste API cross‑subdomain, impostare `SANCTUM_STATEFUL_DOMAINS` e `SESSION_DOMAIN`.
- CORS (se necessario per domini separati):
```php
// config/cors.php (estratto)
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['https://app.example.com'],
'allowed_methods' => ['*'],
'allowed_headers' => ['Content-Type','X-CSRF-TOKEN','X-Requested-With','Authorization'],
'supports_credentials' => true,
```

---

## HTTP Security Headers

Impostare headers a livello middleware/app web server.

Middleware CSP semplificato:
```php
// app/Http/Middleware/CspMiddleware.php
public function handle($request, Closure $next) {
  $response = $next($request);
  $csp = "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self';";
  $response->headers->set('Content-Security-Policy', $csp);
  $response->headers->set('X-Content-Type-Options', 'nosniff');
  $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
  $response->headers->set('Referrer-Policy', 'no-referrer');
  $response->headers->set('Permissions-Policy', 'geolocation=()');
  return $response;
}
```

HSTS (web server):
```
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

Note:
- Aggiornare `script-src`/`style-src` se si usano CDN con nonce/hash.
- Evitare `unsafe-inline` in produzione: preferire nonce o hash.

---

## Rate limiting

Usare limiti differenziati per risorse sensibili.

Definizione personalizzata:
```php
// app/Providers/RouteServiceProvider.php (boot)
RateLimiter::for('api', function (Request $request) {
  $userKey = optional($request->user())->id ?: $request->ip();
  return Limit::perMinute(60)->by($userKey);
});
```

Applicazione:
```php
// routes/api.php
Route::middleware('throttle:api')->group(function () { /* ... */ });
```

---

## Protezione dati (at‑rest, DB, file)

- In transito: TLS ovunque.
- At‑rest (DB): usare `pgcrypto` per campi sensibili, oppure cifratura lato app.

Esempio pgcrypto (concettuale):
```sql
-- installazione estensione
CREATE EXTENSION IF NOT EXISTS pgcrypto;
-- cifrare un campo
UPDATE users SET ssn_enc = pgp_sym_encrypt(ssn_plain::text, :key);
```

Cifratura applicativa:
```php
// app/Services/SecretsService.php (estratto)
public function encrypt(string $plaintext): string {
  return encrypt($plaintext);
}
public function decrypt(string $cipher): string {
  return decrypt($cipher);
}
```

File upload protetti:
- Conservare file su disco con permessi ristretti; per S3 abilitare SSE.
- Salvare hash e metadati (mimetype, size) per audit; verificare tipo MIME lato server.

---

## Gestione segreti & configurazione

- Segreti in `.env` o Docker Secrets/Vault; mai committare `.env`.
- Rotazione chiavi (`APP_KEY`) richiede piano di re‑encryption per dati cifrati.
- `config:cache` in build; validare chiavi critiche all'avvio e abortare se mancanti.

---

## Logging & Audit

- Log JSON strutturato, con `request-id`, `user-id`, `company-id`.
- Audit eventi: login/logout, creazione/lettura report, import/export dati, modifiche ruoli/permessi.
- Conservazione log conforme a privacy; evitare dati sensibili nei log.

Esempio audit semplice:
```php
// app/Listeners/LogReportExported.php (concettuale)
Log::info('report_exported', [ 'user_id' => $user->id, 'company_id' => $company->id, 'report_id' => $report->id ]);
```

---

## Checklist OWASP ASVS L1 (baseline)

- [ ] HTTPS obbligatorio, HSTS abilitato.
- [ ] Session cookies: Secure, HttpOnly, SameSite.
- [ ] CSRF abilitato su tutti i POST/PUT/PATCH/DELETE.
- [ ] Rate limiting su endpoint critici.
- [ ] Input validation server‑side (FormRequest) + client‑side.
- [ ] RBAC e Policy per ogni risorsa sensibile.
- [ ] Security headers: CSP, X‑CTO, X‑Frame‑Options, Referrer‑Policy, Permissions‑Policy.
- [ ] Secrets fuori dal repo; vault/secrets manager consigliato.
- [ ] Log sicuri; audit eventi chiave.
- [ ] Dependency audit in CI (`composer audit`, `npm audit`).

---

## CI: controlli di sicurezza

- Composer audit: `composer audit` (o `symfony/security-checker` equivalenti).
- npm audit: `npm audit --audit-level=high`.
- Lint headers: test feature che verifica presenza headers su risposte critiche.
- SAST opzionale (es. PHPStan/Larastan per code quality, SonarQube o CodeQL per analisi).
