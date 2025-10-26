# Esempi Pratici — CorpVitals24

Snippet pronti all'uso per accelerare lo sviluppo seguendo gli standard del progetto.

## Controller — Problem Details e DI Service
```php
// app/Http/Controllers/KpiController.php
class KpiController extends Controller {
  public function __construct(private App\Services\KpiService $service) {}
  public function index(Request $request) {
    try {
      $companyId = (int) $request->user()->current_company_id;
      $periodId = (int) $request->query('period_id');
      $data = $this->service->getSnapshot($companyId, $periodId);
      return response()->json(['data' => $data]);
    } catch (ValidationException $e) {
      return response()->json([
        'type' => 'https://corpvitals24/docs/errors#validation',
        'title' => 'Validation Error',
        'status' => 422,
        'detail' => $e->getMessage(),
        'errors' => $e->errors(),
      ], 422);
    } catch (Throwable $e) {
      logger()->error('KPI index failed', ['ex' => $e]);
      return response()->json([
        'type' => 'https://corpvitals24/docs/errors#server',
        'title' => 'Server Error',
        'status' => 500,
        'detail' => 'Errore interno',
      ], 500);
    }
  }
}
```

## Service — DI e caching
```php
// app/Services/KpiService.php
namespace App\Services;
use App\Repositories\KpiRepository;
use Illuminate\Support\Facades\Cache;

class KpiService implements KpiServiceInterface {
  public function __construct(private KpiRepository $repository) {}
  public function getSnapshot(int $companyId, int $periodId): array {
    $key = "kpi:{$companyId}:{$periodId}";
    return Cache::remember($key, now()->addMinutes(10), fn() => $this->repository->fetchSnapshot($companyId, $periodId));
  }
}
```

## Model + FormRequest
```php
// app/Models/Kpi.php
class Kpi extends Model {
  protected $fillable = ['code','name','description','formula_refs'];
  protected $casts = ['formula_refs' => 'array'];
}

// app/Http/Requests/CalculateKpiRequest.php
class CalculateKpiRequest extends FormRequest {
  public function rules(): array {
    return [
      'period_id' => ['required','integer','exists:periods,id'],
      'kpi_codes' => ['required','array','min:1'],
      'kpi_codes.*' => ['string','exists:kpis,code'],
    ];
  }
}
```

## Policy/Guard tenant-aware
```php
// app/Policies/CompanyPolicy.php
class CompanyPolicy {
  public function view(User $user, Company $company): bool {
    return $user->companies()->whereKey($company->id)->exists();
  }
}

// app/Providers/AuthServiceProvider.php (boot)
Gate::policy(Company::class, CompanyPolicy::class);
```

## Config module
```php
// config/kpi.php
return [
  'snapshot_ttl_minutes' => env('KPI_SNAPSHOT_TTL', 10),
  'max_period_span' => env('KPI_MAX_PERIOD_SPAN', 10),
];
```

## Dockerfile e docker-compose
```dockerfile
# docker/php-fpm/Dockerfile
FROM php:8.3-fpm
RUN apt-get update && apt-get install -y git unzip libpq-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql zip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
```

```yaml
# docker/docker-compose.yml (estratto)
services:
  app:
    build: ./php-fpm
    volumes: [ "../:/var/www/html" ]
    depends_on: [ db, redis ]
  web:
    image: nginx:1.27-alpine
    volumes:
      - "../:/var/www/html"
      - "./nginx/default.conf:/etc/nginx/conf.d/default.conf"
    ports: [ "8080:80" ]
  db:
    image: postgres:16
    environment:
      POSTGRES_DB: corpvitals
      POSTGRES_USER: laravel
      POSTGRES_PASSWORD: secret
  redis:
    image: redis:7
```

## package.json (scripts)
```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "typecheck": "vue-tsc --noEmit",
    "lint": "eslint resources/js --ext .ts,.vue",
    "format": "prettier -w .",
    "prepare": "husky install"
  }
}
```

## vite.config.ts & tailwind.config.cjs
```ts
// vite.config.ts
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
export default defineConfig({ plugins: [vue()], resolve: { alias: { '@': '/resources/js' } } });
```

```js
// tailwind.config.cjs
module.exports = { content: ['./resources/**/*.{vue,js,ts,blade.php}'], theme: { extend: {} }, plugins: [] };
```

