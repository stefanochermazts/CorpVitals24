# ADR-001 — Scelta Parser XBRL: Arelle (Python)

Stato: Accettata  
Data: 2025-10-27  
Versione: 1.0

## Contesto

La piattaforma deve supportare l'import di bilanci **XBRL/iXBRL** (ESEF e OIC). Il parsing richiede gestione di:
- iXBRL embedded in XHTML
- Tassonomie IFRS/ESEF e OIC con versioning
- Contexts (instant/duration), units, decimals
- Dimensioni XBRL (explicit e typed)

Le alternative considerate:
- Libreria PHP (es. lyquidity/xbrl): semplice integrazione, ma copertura iXBRL limitata e manutenzione incerta.
- Implementazione custom con XMLReader: effort elevato e rischio alto.
- **Arelle (Python)**: standard de-facto adottato da ESMA/SEC, copertura ampia e aggiornamenti costanti.

## Decisione

Usare **Arelle (Python)** come parser primario, invocato da PHP via subprocess (Symfony Process) o via Arelle Web Server (HTTP) dentro un container dedicato. Creiamo un **wrapper service** (`XbrlParserService`) per incapsulare l'invocazione e normalizzare l'output in JSON strutturato.

## Conseguenze

Pro:
- Parsing robusto di XBRL/iXBRL, inclusi dimensioni e tassonomie.
- Allineamento con standard e aggiornamenti regolatori.
- Riduzione rischio di bug nel parsing.

Contro:
- Dipendenza da **Python** e immagine/container aggiuntivo in produzione.
- Overhead di processo/HTTP tra PHP e Arelle (mitigabile con Web Server persistente).

## Dettagli Implementativi

- Config `config/xbrl.php` per `ARELLE_PATH`, timeout e dimensioni massime.
- Wrapper `App/Services/XbrlParserService` con metodi `parse(file)` e `detectTaxonomy(file)`.
- Log dedicati: `storage/logs/arelle.log` e `arelle-taxonomy-detect.log`.
- Fallback detection basata su namespace in assenza di metadati affidabili.

## Alternative Scartate

1) lyquidity/xbrl (PHP): insufficiente per iXBRL complessi e gestione dimensioni.
2) Parser custom: costo/beneficio sfavorevole e manutenzione critica.

## Migrazione/Deployment

- Aggiungere service Docker `arelle` oppure predisporre `/opt/Arelle` su host.
- Variabili `.env`: `ARELLE_PATH`, `XBRL_PARSE_TIMEOUT`, `XBRL_MAX_FILE_SIZE`.
- Healthcheck: comando `--help` o chiamata HTTP `/rest` se in modalità server.

## Collegamenti

- `documenti/XBRL_INTEGRATION_GUIDE.md`
- `documenti/ARCHITECTURE.md`
- ADR-002 (Import dual-source), ADR-003 (Dimensioni), ADR-004 (Caching tassonomie)


