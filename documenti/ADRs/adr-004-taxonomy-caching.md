# ADR-004 — Caching Tassonomie XBRL in Redis

Stato: Accettata  
Data: 2025-10-27  
Versione: 1.0

## Contesto

Il caricamento e la risoluzione delle tassonomie (IFRS/ESEF, OIC) e delle relative mappe di concetti
(`taxonomy_maps`) può essere costoso. Le richieste ripetute di import/parsing devono beneficiare
di cache per ridurre latenza e carico sul database.

## Decisione

Usare **Redis** per cachare:
- Metadati tassonomie attive (chiavi `taxonomies:*`).
- Mapping concetto → ValoriBase per singola taxonomy (chiavi `taxonomy_map:{taxonomy_id}`).
- Definizioni dimensioni (chiavi `dimension_defs:{taxonomy_id}`).

TTL predefinito: **24h** con invalidazione esplicita su modifica (CRUD tassonomie/mapping).

## Conseguenze

Pro:
- Riduzione latenza e query DB ripetute.
- Scalabilità migliore sotto carichi di import multipli.

Contro:
- Coerenza cache da gestire con attenzione (invalidare su update/delete).

## Dettagli Implementativi

- Configurazione in `config/xbrl.php` → `taxonomy_cache_ttl`.
- Repository layer utilizza `Cache::remember()` con chiavi namespaced per tenant/taxonomy.
- Invalidazione con eventi Eloquent `saved`/`deleted` su `Taxonomy`, `TaxonomyMap`, `DimensionDef`.

## Migrazione/Deployment

- Verificare Redis disponibile (`CACHE_DRIVER=redis`).
- (Opz.) Esportare metriche Prometheus su cache hit/miss.

## Collegamenti

- ADR-001 (Arelle)
- ADR-002 (Dual-source Import)
- ADR-003 (Storage Dimensioni)
- `documenti/ARCHITECTURE.md`


