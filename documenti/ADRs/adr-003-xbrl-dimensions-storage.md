# ADR-003 — Storage Dimensioni XBRL (explicit/typed)

Stato: Accettata  
Data: 2025-10-27  
Versione: 1.0

## Contesto

I facts XBRL possono avere **dimensioni** (segment/scenario) sia **explicit** sia **typed**. Le dimensioni sono essenziali per:
- analisi consolidate vs separate,
- segmentazione geografica/di business,
- scenario analysis e note.

Serve una rappresentazione flessibile e interrogabile.

## Decisione

Usare un approccio ibrido:
- Colonna `dimensions` **JSONB** su `filing_facts` per query rapide aggregate e conservazione fedele.
- Tabella normalizzata `dimension_values` per join efficienti su ricerche specifiche (axis/member/typed_value) e ordinamento.

## Conseguenze

Pro:
- Bilanciamento tra flessibilità (JSONB) e queryability (tabella dedicata).
- Supporto sia explicit sia typed dimensions.
- Indici GIN per ricerche complesse.

Contro:
- Duplicazione parziale dell'informazione (JSONB + righe dedicate).
- Necessità di mantenere consistenza tra le due rappresentazioni in fase di ingest.

## Dettagli Implementativi

- `filing_facts.dimensions` JSONB: `{ segment: {...}, scenario: {...} }`.
- `dimension_values`: FK a `filing_fact_id`, `dimension_qname`, `member_qname` (nullable), `typed_value` (nullable), `axis_order`.
- Indici: GIN su JSONB; BTree su `dimension_qname`, `member_qname`.
- Service `DimensionService` per estrazione e persistenza coerente.

## Migrazione/Deployment

- Migrations: `create_dimension_defs`, `create_dimension_values`.
- Seed opzionale `dimension_defs` per tassonomie note (EntityAxis, Consolidated/Separate, ecc.).

## Collegamenti

- ADR-001 (Arelle)
- ADR-002 (Dual-source)
- `documenti/ARCHITECTURE.md`


