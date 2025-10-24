# API Specification — CorpVitals24 (v1)

Contratto REST per integrazione SPA (Inertia/Vue) e client esterni. Tutte le risposte sono JSON.

## Indice
- Principi e formato
- Versioning e compatibilità
- Autenticazione & sicurezza
- Convenzioni endpoint e payload
- Errori (Problem Details JSON)
- Paginazione, filtri e sort
- Rate limiting e idempotenza
- Risorse principali (esempi)
- OpenAPI 3.1 (generazione)
- Test di contratto

---

## Principi e formato
- JSON UTF‑8; header obbligatori: `Accept: application/json`, `Content-Type: application/json`.
- Naming campi in JSON: camelCase (es. `baseCurrency`), DB rimane snake_case.
- Envelope risposte di lista: `{ data: [...], meta: { pagination... }, links: {...} }`.

## Versioning
- Prefisso: `/api/v1/...`.
- Breaking changes → nuova major (`/api/v2`). Deprecazioni annunciate con header `Deprecation`.

## Autenticazione & sicurezza
- `auth:sanctum` per SPA e client attendibili.
- Token personali (opzionali) per integrazioni machine‑to‑machine con scope.
- Rate limit applicato (vedi sezione dedicata).

Rotte base:
```php
// routes/api.php
Route::prefix('v1')->middleware(['auth:sanctum','throttle:api'])->group(function () {
  Route::get('/kpis', [KpiController::class,'index']);
  Route::post('/kpis/calculate', [KpiController::class,'calculate']);
  Route::get('/companies', [CompanyController::class,'index']);
  Route::post('/imports', [ImportController::class,'upload']);
});
```

## Convenzioni endpoint e payload
- GET è idempotente; POST crea/avvia processi; PUT/PATCH aggiornano; DELETE rimuove (soft delete dove previsto).
- Timestamps in ISO‑8601 `YYYY-MM-DDTHH:mm:ssZ`.

Esempio risposta lista:
```json
{
  "data": [{ "id": 1, "name": "Acme", "baseCurrency": "EUR" }],
  "meta": { "total": 120, "perPage": 25, "currentPage": 1 },
  "links": { "next": "/api/v1/companies?page=2" }
}
```

## Errori — Problem Details JSON
Formato standard (RFC 9457 adattato):
```json
{
  "type": "https://corpvitals24/docs/errors#validation",
  "title": "Validation Error",
  "status": 422,
  "detail": "Uno o più campi non sono validi.",
  "instance": "/api/v1/kpis",
  "errors": { "field": ["message"] }
}
```

Mappature tipiche:
- 400 Input malformato; 401 Non autenticato; 403 Vietato; 404 Non trovato; 409 Conflitto; 422 Validazione; 429 Troppi tentativi; 500 Errore interno.

## Paginazione, filtri, sort
- Paginazione: query `?page=1&perPage=25` e headers `Link`, `X-Total-Count` opzionali.
- Filtri: `?filter[name]=acme&filter[sector]=retail`.
- Ordine: `?sort=-createdAt,name` (prefisso `-` per desc).

Header esempio:
```
Link: </api/v1/companies?page=2>; rel="next", </api/v1/companies?page=5>; rel="last"
X-Total-Count: 120
```

## Rate limiting e idempotenza
- Default: 60 req/min per utente/IP (`throttle:api`). Endpoint sensibili possono ridurre a 10 req/min.
- Idempotenza: per POST ripetibili usare header `Idempotency-Key: <uuid4>`; il server deve cache‑are per finestra breve.

## Risorse principali (esempi)

### Companies
GET `/api/v1/companies`
```json
{
  "data": [
    { "id": 1, "name": "Acme", "baseCurrency": "EUR", "sector": "Retail" }
  ],
  "meta": { "total": 1, "perPage": 25, "currentPage": 1 },
  "links": {}
}
```

### KPI
GET `/api/v1/kpis?periodId=123`
```json
{ "data": [ { "code": "EBITDA_MARGIN", "value": 0.151, "unit": "%", "state": "green" } ] }
```

POST `/api/v1/kpis/calculate`
```json
{ "periodId": 123, "kpiCodes": ["EBITDA_MARGIN","ROE"] }
```
Risposta 202 con job id (se asincrono):
```json
{ "jobId": "f3f2c3...", "status": "queued" }
```

### Imports
POST `/api/v1/imports` (multipart)
- campi: `periodId` numerico, `upload` file `csv/xlsx`.
Risposte: 201 su successo; 400/422 su validazione.

## OpenAPI 3.1 (generazione)
Libreria suggerita: `L5-Swagger` oppure `goldspecdigital/laravel-swagger`.

Esempio annotazione controller (swagger-php):
```php
/**
 * @OA\Get(
 *   path="/api/v1/companies",
 *   summary="Lista aziende",
 *   security={{"sanctum":{}}},
 *   @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK")
 * )
 */
```

Build doc:
- Route doc: `/api/documentation` (L5‑Swagger) o export JSON: `php artisan swagger:generate`.
- Validazione schema in CI con `swagger-cli validate`.

## Test di contratto
- Feature: assert su status, struttura JSON, header di paginazione, errori Problem Details.
- Schema: validare risposte contro OpenAPI con librerie dedicate.

Esempio Feature (Laravel):
```php
public function test_companies_index_returns_paginated_list(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);
    $res = $this->getJson('/api/v1/companies?page=1&perPage=25');
    $res->assertOk()->assertJsonStructure(['data','meta'=>['total','perPage','currentPage']]);
}
```
