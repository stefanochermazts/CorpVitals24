# Best Practices — Errori, Logging, Validazione, Test

Questa guida raccoglie pratiche consigliate per robustezza, osservabilità e qualità del codice in CorpVitals24.

## Indice
- Error handling (Problem Details, mapping eccezioni)
- Logging (JSON strutturato, correlazione, livelli)
- Validazione (FormRequest, vee-validate + Zod)
- Test (unit, feature, e2e, a11y)

---

## Error handling
- Usare Problem Details JSON (RFC 9457) per le API.
- Mappare le eccezioni comuni: `ValidationException` (422), `AuthenticationException` (401), `AuthorizationException` (403), `ModelNotFoundException` (404), `ThrottleRequestsException` (429).
- Definire eccezioni di dominio (es. `ImportException`, `KpiCalculationException`) e loggarle con contesto.
- Non esporre stack trace in produzione.

Esempio handler:
```php
// app/Exceptions/Handler.php (estratto)
protected function invalidJson($request, ValidationException $exception)
{
    return response()->json([
        'type' => 'https://corpvitals24/docs/errors#validation',
        'title' => 'Validation Error',
        'status' => 422,
        'detail' => 'Uno o più campi non sono validi.',
        'errors' => $exception->errors(),
    ], 422);
}
```

Controller robusto:
```php
// app/Http/Controllers/ImportController.php (estratto)
public function upload(ImportRequest $request, ImportService $service)
{
    try {
        $result = $service->import($request->validated());
        return response()->json(['data' => $result], 201);
    } catch (ImportException $e) {
        logger()->warning('Import failed', ['file' => $request->file('upload')?->getClientOriginalName(), 'reason' => $e->getMessage()]);
        return response()->json([
            'type' => 'https://corpvitals24/docs/errors#import',
            'title' => 'Import Error',
            'status' => 400,
            'detail' => $e->getMessage(),
        ], 400);
    } catch (Throwable $e) {
        logger()->error('Unhandled error during import', ['ex' => $e]);
        return response()->json([
            'type' => 'https://corpvitals24/docs/errors#server',
            'title' => 'Server Error',
            'status' => 500,
            'detail' => 'Errore interno',
        ], 500);
    }
}
```

## Logging
- Formato JSON strutturato (Monolog JsonFormatter); un file al giorno, retention configurata.
- Includere `request-id`, `user-id`, `company-id`, `tenant-id` quando disponibili.
- Livelli: `debug` (sviluppo), `info` (eventi normali), `warning` (errori recuperabili), `error` (eccezioni), `critical` (indisponibilità servizio).
- Non loggare dati sensibili (password, token, numeri carte).

Config canale JSON:
```php
// config/logging.php (estratto)
'channels' => [
  'stack' => [ 'driver' => 'stack', 'channels' => ['json'] ],
  'json' => [
    'driver' => 'monolog',
    'handler' => Monolog\\Handler\\StreamHandler::class,
    'with' => ['stream' => storage_path('logs/laravel.json')],
    'formatter' => Monolog\\Formatter\\JsonFormatter::class,
  ],
]
```

Middleware request-id:
```php
// app/Http/Middleware/RequestId.php
public function handle($request, Closure $next)
{
    $id = $request->headers->get('X-Request-Id') ?? (string) Str::uuid();
    Log::withContext(['request_id' => $id]);
    $response = $next($request);
    $response->headers->set('X-Request-Id', $id);
    return $response;
}
```

## Validazione
- Backend: `FormRequest` con regole, autorizzazione, messaggi localizzati.
- Frontend: vee-validate + Zod; riuso degli stessi schemi quando possibile per coerenza.
- Validare upload (dimensione, mime, estensioni), limiti numerici e formati data/valuta.

Esempio FormRequest:
```php
// app/Http/Requests/ImportRequest.php
public function rules(): array
{
    return [
        'period_id' => ['required','integer','exists:periods,id'],
        'upload' => ['required','file','mimes:csv,xlsx','max:10240'],
    ];
}
```

Esempio schema Zod:
```ts
// resources/js/validation/importSchema.ts
import { z } from 'zod';
export const importSchema = z.object({
  period_id: z.number().int().positive(),
  file: z.instanceof(File),
});
```

## Test
- Unit: servizi con repository mock (Pest). Focus su logica/business e edge case.
- Feature: test API (status, payload, headers di paginazione, errori Problem Details).
- E2E: flussi critici (import → calcolo KPI → dashboard), opzionale Cypress; integrare axe-core per a11y.
- Performance smoke: tempi risposta endpoint KPI e rendering griglia/grafici.

Esempio Pest unit test:
```php
// tests/Unit/KpiServiceTest.php
it('returns snapshot from repository', function () {
    $repo = Mockery::mock(App\\Repositories\\KpiRepository::class);
    $repo->shouldReceive('fetchSnapshot')->once()->andReturn(['kpi' => []]);
    $service = new App\\Services\\KpiService($repo);
    expect($service->getSnapshot(1, 1))->toBe(['kpi' => []]);
});
```

Esempio Feature test API:
```php
// tests/Feature/Api/KpiTest.php
public function test_kpi_index_returns_problem_details_on_validation_error(): void
{
    $user = User::factory()->create();
    $this->actingAs($user);
    $res = $this->getJson('/api/v1/kpis');
    $res->assertStatus(422)->assertJsonStructure(['type','title','status','detail']);
}
```

E2E con axe-core (esempio):
```js
// cypress/e2e/a11y-dashboard.cy.ts
it('dashboard is accessible', () => {
  cy.visit('/dashboard');
  cy.injectAxe();
  cy.checkA11y(null, { runOnly: ['wcag2a','wcag2aa'] });
});
```
