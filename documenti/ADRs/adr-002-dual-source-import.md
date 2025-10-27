# ADR-002 — Import Dual-Source: CSV/XLSX e XBRL/iXBRL

Stato: Accettata  
Data: 2025-10-27  
Versione: 1.0

## Contesto

La piattaforma deve accettare due tipologie principali di sorgenti dati:
- **CSV/XLSX** (template o mapping manuale)
- **XBRL/iXBRL** (ESEF, OIC)

Entrambe devono confluire nello stesso modello interno di **ValoriBase** per alimentare il motore KPI e consentire tracciabilità e audit.

## Decisione

Adottare uno **Strategy Pattern** nell'application layer:
- `CsvImportStrategy` gestisce parsing via Laravel Excel, validazione e mapping colonne.
- `XbrlImportStrategy` usa Arelle per parsing, normalizzazione e auto-mapping via `TaxonomyMap`.

Entrambe convergono su `ImportService` che produce record `ValoriBase` con `source` esplicito e `provenance` (link a `filing_id` o `raw_rows`).

## Conseguenze

Pro:
- Coerenza del dominio: un unico punto di verità (`ValoriBase`).
- Facile estensione a nuove sorgenti (API esterne, Open Banking).
- Tracciabilità uniforme per audit (provenance JSON).

Contro:
- Maggior complessità del service di orchestrazione.
- Doppia pipeline di validazione.

## Dettagli Implementativi

- Interfaccia `ImportStrategyInterface` con `ingest(array $input): IngestResult`.
- `ImportService` seleziona la strategy in base al mime/estensione.
- `ValoriBase` estesa con `filing_id`, `source` e `provenance` JSONB.
- Endpoints distinti per UX: `/api/imports/csv` e `/api/imports/xbrl`.

## Migrazione/Deployment

- Migrations per tabelle XBRL (ADR-003/DB) e estensione `valori_base`.
- Code path separati per code-splitting frontend (pagine Import CSV vs Import XBRL).

## Collegamenti

- ADR-001 (Arelle)
- ADR-003 (Storage dimensioni)
- ADR-004 (Caching tassonomie)
- `documenti/ARCHITECTURE.md`


