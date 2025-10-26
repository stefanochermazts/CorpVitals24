# Security Implementation - CorpVitals24

## Overview
Questo documento descrive tutte le misure di sicurezza implementate in CorpVitals24 per proteggere l'applicazione e i dati degli utenti.

---

## 1. HTTP Security Headers

### Middleware: `SecurityHeaders.php`
Implementa tutti gli header di sicurezza raccomandati da OWASP.

#### Headers Implementati:

**Content-Security-Policy (CSP)**
- Previene attacchi XSS limitando le sorgenti di contenuti
- Development: Permette `unsafe-inline` e `unsafe-eval` per Vite HMR
- Production: Rimuove automaticamente `unsafe-inline` e `unsafe-eval`
- Configurazione:
  ```
  default-src 'self'
  script-src 'self'
  style-src 'self' https://fonts.bunny.net
  img-src 'self' data: https:
  font-src 'self' https://fonts.bunny.net
  connect-src 'self'
  frame-ancestors 'none'
  base-uri 'self'
  form-action 'self'
  object-src 'none'
  ```

**Strict-Transport-Security (HSTS)**
- Forza connessioni HTTPS per 1 anno
- Include subdomain
- Preload ready
- Solo in HTTPS: `max-age=31536000; includeSubDomains; preload`

**X-Content-Type-Options**
- Previene MIME-type sniffing
- Valore: `nosniff`

**X-Frame-Options**
- Previene clickjacking
- Valore: `DENY`

**X-XSS-Protection**
- Filtro XSS browser (legacy support)
- Valore: `1; mode=block`

**Referrer-Policy**
- Controlla informazioni referrer
- Valore: `strict-origin-when-cross-origin`

**Permissions-Policy**
- Disabilita feature browser non necessarie
- Disabilitati: `geolocation`, `microphone`, `camera`, `payment`

**X-Permitted-Cross-Domain-Policies**
- Previene cross-domain policy files
- Valore: `none`

---

## 2. Rate Limiting

### Implementazione Multi-Tier
Rate limiting differenziato per diversi tipi di richieste.

#### API Rate Limiter
- **Limite**: 60 richieste/minuto
- **Chiave**: User ID (autenticato) o IP
- **Response**: Problem Details JSON (RFC 7807)
- **Uso**: Tutte le API routes (`/api/*`)

#### Auth Rate Limiter
- **Limite**: 5 tentativi/minuto
- **Chiave**: IP address
- **Protezione**: Brute force login attacks
- **Response**: Redirect con messaggio errore
- **Uso**: Login routes

#### Web Rate Limiter
- **Limite**: 120 richieste/minuto
- **Chiave**: IP address
- **Uso**: Navigazione generale web

#### Global Rate Limiter
- **Limite**: 1000 richieste/ora
- **Chiave**: IP address
- **Protezione**: DDoS attacks
- **Response**: View personalizzata `errors/429.blade.php`

### Configurazione
```php
// AppServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    $key = optional($request->user())->getAuthIdentifier() ?: $request->ip();
    return Limit::perMinute(60)->by($key);
});

RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

### Rate Limit Headers
Response headers per monitoraggio:
- `X-RateLimit-Limit`: Limite totale
- `X-RateLimit-Remaining`: Richieste rimanenti
- `Retry-After`: Secondi da attendere (se limit exceeded)

---

## 3. CORS Configuration

### File: `config/cors.php`

**Paths Protetti**:
- `/api/*` - Tutte le API routes
- `/sanctum/csrf-cookie` - CSRF token endpoint

**Configurazione**:
```php
'allowed_methods' => ['*'],
'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS')),
'allowed_headers' => ['*'],
'exposed_headers' => ['X-Request-ID', 'X-RateLimit-Limit', 'X-RateLimit-Remaining'],
'supports_credentials' => true,
```

**Environment Variable**:
```
CORS_ALLOWED_ORIGINS="http://localhost:8000,http://localhost:5173"
```

---

## 4. Session & Cookie Security

### Session Configuration (`config/session.php`)

**Driver**: Redis (performance + security)
```
SESSION_DRIVER=redis
SESSION_LIFETIME=120 # minutes
```

**Cookie Security**:
```
SESSION_SECURE_COOKIE=false  # true in production (HTTPS only)
SESSION_HTTP_ONLY=true       # Previene accesso JavaScript
SESSION_SAME_SITE=lax        # Protezione CSRF
SESSION_PARTITIONED_COOKIE=false
```

### Cookie Attributes
- **HttpOnly**: ✅ JavaScript non può accedere al cookie
- **Secure**: ✅ Solo HTTPS in production
- **SameSite=lax**: ✅ Protezione CSRF, permette GET cross-site
- **Path=/**: ✅ Valido per tutto il dominio
- **Domain=null**: ✅ Solo dominio corrente

---

## 5. Authentication Security

### Laravel Sanctum
- **Type**: Cookie-based SPA authentication
- **CSRF Protection**: ✅ Automatico via Sanctum
- **Token Storage**: HTTPOnly cookies (non accessibili da JS)

**Stateful Domains**:
```
SANCTUM_STATEFUL_DOMAINS="localhost,127.0.0.1,localhost:5173"
```

### Multi-Tenant RBAC
- **Package**: `spatie/laravel-permission`
- **Team Isolation**: ✅ Roles/permissions scoped per team
- **Middleware**: `EnsureUserHasTeam` verifica appartenenza

---

## 6. Input Sanitization

### Helper: `SecurityHelper.php`

**Metodi Disponibili**:

```php
// Sanitize HTML (XSS prevention)
SecurityHelper::sanitizeHtml($input, $allowBasicHtml = false);

// Escape output for HTML
SecurityHelper::escapeHtml($output);

// Sanitize filename (directory traversal prevention)
SecurityHelper::sanitizeFilename($filename);

// Validate & sanitize URL
SecurityHelper::sanitizeUrl($url, ['http', 'https']);

// Sanitize email
SecurityHelper::sanitizeEmail($email);

// Mask sensitive data for logs
SecurityHelper::maskSensitiveData($data, $visibleChars = 3);

// IP whitelist check
SecurityHelper::isIpAllowed($ip, $allowedRanges);
```

**Uso Consigliato**:
```php
// In Controller/Service
$cleanInput = SecurityHelper::sanitizeHtml($request->input('description'));

// In Blade (già safe con {{ }} ma per sicurezza extra)
{{ SecurityHelper::escapeHtml($userInput) }}

// Log con dati mascherati
Log::info('User email: ' . SecurityHelper::maskSensitiveData($user->email));
```

---

## 7. Request Tracing

### Middleware: `RequestId.php`

**Features**:
- Genera UUID univoco per ogni richiesta
- Usa header `X-Request-ID` se fornito dal client
- Aggiunge `X-Request-ID` alla response
- Permette correlazione log tra frontend/backend

**Headers**:
```
Request:  X-Request-ID: <uuid> (optional)
Response: X-Request-ID: <uuid> (sempre presente)
```

**Log Correlation**:
```php
// Nel log, usa:
Log::info('Processing request', [
    'request_id' => $request->attributes->get('request-id'),
    'user_id' => $user->id,
]);
```

---

## 8. Error Handling & Problem Details

### Format: RFC 7807 (Problem Details JSON)

**Errori API** ritornano JSON strutturato:
```json
{
  "type": "https://corpvitals24.test/validation-error",
  "title": "Validation Failed",
  "status": 422,
  "detail": "The given data was invalid.",
  "instance": "https://corpvitals24.test/api/v1/kpis",
  "errors": {
    "code": ["The code field is required."]
  }
}
```

**Content-Type**: `application/problem+json`

**Errori Gestiti**:
- `ValidationException` → 422
- `ModelNotFoundException` → 404
- `HttpException` → status code specifico
- Altro → 500

---

## 9. Middleware Stack Order

### Global Middleware (tutte le richieste)
1. `SecurityHeaders` - Headers di sicurezza
2. ... (Laravel default middleware)

### Web Group Middleware
1. `EnsureFrontendRequestsAreStateful` - Sanctum SPA
2. `HandleInertiaRequests` - Shared data Inertia
3. `RequestId` - Request tracing
4. `throttle:web` - Rate limiting (120/min)

### API Group Middleware
1. `RequestId` - Request tracing
2. `throttle:api` - Rate limiting (60/min)

### Route-Specific Middleware
- `throttle:auth` - Login routes (5/min)
- `ensure.team` - Multi-tenant verification
- `auth` - Authentication check

---

## 10. Security Checklist ✅

### Headers
- ✅ Content-Security-Policy
- ✅ Strict-Transport-Security (HSTS)
- ✅ X-Content-Type-Options
- ✅ X-Frame-Options
- ✅ X-XSS-Protection
- ✅ Referrer-Policy
- ✅ Permissions-Policy
- ✅ X-Permitted-Cross-Domain-Policies

### Authentication & Authorization
- ✅ Cookie-based Sanctum SPA auth
- ✅ CSRF protection
- ✅ HttpOnly cookies
- ✅ Secure cookies (HTTPS in prod)
- ✅ SameSite=lax cookies
- ✅ Multi-tenant RBAC
- ✅ Team isolation middleware

### Rate Limiting
- ✅ API rate limiting (60/min)
- ✅ Auth rate limiting (5/min) - Brute force protection
- ✅ Web rate limiting (120/min)
- ✅ Global rate limiting (1000/hour) - DDoS protection
- ✅ Custom 429 error page

### Input/Output
- ✅ Input sanitization helpers
- ✅ XSS prevention
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ Directory traversal prevention
- ✅ URL/Email validation
- ✅ Filename sanitization

### CORS & API
- ✅ CORS configuration
- ✅ Allowed origins whitelist
- ✅ Credentials support
- ✅ Exposed security headers
- ✅ Problem Details JSON (RFC 7807)

### Monitoring & Logging
- ✅ Request ID correlation
- ✅ Sensitive data masking
- ✅ Security event logging
- ✅ Rate limit monitoring headers

---

## 11. Production Checklist

Prima del deploy in produzione:

```bash
# 1. Environment
- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] SESSION_SECURE_COOKIE=true
- [ ] Aggiorna CORS_ALLOWED_ORIGINS con domini reali
- [ ] Configura SANCTUM_STATEFUL_DOMAINS

# 2. HTTPS
- [ ] Certificato SSL installato
- [ ] Force HTTPS redirect
- [ ] HSTS preload submission

# 3. Database
- [ ] Password forti
- [ ] Firewall DB (solo app server)
- [ ] Backup automatici
- [ ] SSL/TLS connection

# 4. Cache & Queue
- [ ] Redis password set
- [ ] Redis firewall rules
- [ ] TLS encryption per Redis

# 5. Monitoring
- [ ] Log aggregation setup
- [ ] Error tracking (Sentry, Bugsnag)
- [ ] Metrics collection (Prometheus)
- [ ] Uptime monitoring

# 6. Testing
- [ ] Security audit con OWASP ZAP
- [ ] Penetration testing
- [ ] Load testing rate limiters
- [ ] CSRF protection test
```

---

## 12. Security Contacts

**Reporting Security Issues**:
- Email: security@corpvitals24.test
- Response time: 24 hours
- PGP Key: [fornire key]

**Security Updates**:
- Check for Laravel security releases
- Monitor dependencies (npm audit, composer audit)
- Subscribe to security mailing lists

---

## 13. References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [Content Security Policy](https://content-security-policy.com/)
- [RFC 7807 - Problem Details](https://tools.ietf.org/html/rfc7807)
- [HSTS Preload](https://hstspreload.org/)
- [SameSite Cookies](https://web.dev/samesite-cookies-explained/)

