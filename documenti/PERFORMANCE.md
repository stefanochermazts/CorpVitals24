# Performance & Caching — CorpVitals24

Strategie per mantenere UI reattiva e backend scalabile con dataset KPI.

## Indice
- Caching backend (Redis, Response Cache)
- Ottimizzazioni query Postgres (indici, MV, EXPLAIN)
- Frontend (Vite code-splitting, lazy load ECharts/RevoGrid)
- Anti-pattern e checklist

---

## Caching backend
- Cache dei risultati costosi con chiave deterministica per azienda/periodo.
- TTL breve (5–15 min) con invalidazione su import o recalcolo.
- Evitare cache stampede: `Cache::lock()` o jitter sul TTL.

Esempio:
```php
// app/Services/KpiService.php (estratto)
public function getSnapshot(int $companyId, int $periodId): array {
  $key = "kpi:{$companyId}:{$periodId}";
  return Cache::remember($key, now()->addMinutes(10), fn() => $this->repository->fetchSnapshot($companyId, $periodId));
}
```

Response cache (se appropriato):
```php
// Esempio con spatie/laravel-responsecache
// Rotte GET idempotenti e non sensibili: cache responses per utente/tenant
```

## Query Postgres
- Indici composti su filtri frequenti: `(company_id, period_id, kpi_id)`.
- GIN su JSONB quando filtrato; evitare funzioni non sargable negli WHERE.
- Usare `EXPLAIN ANALYZE` per guidare il tuning.
- Visite materializzate per dashboard home / report.

Esempio indice:
```sql
CREATE INDEX CONCURRENTLY idx_kpi_values_company_period_kpi ON kpi_values(company_id, period_id, kpi_id);
```

## Frontend
- Vite code-splitting: import dinamici per ECharts e RevoGrid.
- Virtualizzazione RevoGrid di default; limitare colonne visibili.
- Debounce su input filtri (200–400ms). Evitare re-render inutili (keyed lists, memoization).

Esempio import dinamico:
```ts
const echarts = await import('echarts');
```

Vite config (alias & splitting):
```ts
// vite.config.ts (estratto)
export default defineConfig({
  build: { rollupOptions: { output: { manualChunks: { echarts: ['echarts'] } } } },
  resolve: { alias: { '@': '/resources/js' } }
});
```

## Anti‑pattern e checklist
- Evitare query N+1: usare eager loading nei repository.
- Non cache‑are errori di validazione o risposte auth.
- Misurare prima di ottimizzare: baseline con metriche e profiling.
- Controllare peso bundle: < 200KB gzip per first load della dashboard.
