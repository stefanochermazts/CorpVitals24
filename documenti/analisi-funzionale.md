# Blueprint funzionale — Piattaforma KPI Contabili & Finanziari

## 0) Stack tecnico scelto (UI dashboard & griglie)

**Backend:** Laravel 12
**SPA layer:** Inertia.js + **Vue 3 (TypeScript)** + Vite
**UI base:** Tailwind CSS + Headless UI (componenti accessibili)
**Grafici:** **Apache ECharts** (linee, barre, radar, gauge, heatmap, scatter, sparkline)
**Griglie dati:** **RevoGrid** (Web Component) – scelta primaria
**Database:** **PostgreSQL 16+**
**State & utilità:** Pinia (store), vue‑i18n (localizzazione), Day.js/Luxon (date), vee‑validate (form)
**Export/report:** Laravel Browsershot (PDF), Laravel Excel (XLSX/CSV)
**A11y & qualità:** axe‑core (accessibilità), Storybook facoltativo per pattern

### Motivi della scelta RevoGrid

* **Performance**: virtualizzazione aggressiva per dataset di grandi dimensioni (milioni di celle).
* **Funzioni core MVP**: sorting, filtering, column resize/reorder, frozen/pinned columns, editing cella, selezione range, copy/paste.
* **Server‑side friendly**: paging, filter e sort delegabili all’API; schema colonne **JSON** per migrazioni future.
* **Integrazione Vue**: uso come Web Component dentro Inertia/Vue; wrapper opzionale.
* **Costo**: licenza **MIT** → ideale per MVP.

### Accorgimenti di integrazione

* **Adapter dati unico** (paging/sort/filter) tra griglia e API Laravel per evitare lock‑in.
* **Column schema** in JSON (id, field, header, type, formatter, editable, width, pin) salvabile per utente/tenant.
* **A11y**: focus management custom, ruoli ARIA grid, scorciatoie tastiera, high‑contrast; testi alternativi nelle tabelle esportate.
* **Pivot opzionale**: per viste pivot complesse, valutare **Perspective (FINOS)** come componente dedicato solo in v1+ senza sostituire RevoGrid.
* **Theming/white‑label**: CSS variables + Tailwind tokens; dark mode.

### Motivi della scelta **PostgreSQL 16+**

* **Analytics‑first**: window functions, CTE ricorsive, **materialized views**, partial/functional index → perfetto per trend KPI, confronti A/B, drill‑down.
* **Precisione finanziaria**: `NUMERIC` affidabile per valute/percentuali; operatori/aggregazioni maturi.
* **JSONB** con indici GIN: ottimo per `provenance_json`, mapping tassonomie e metadati XBRL.
* **Partizionamento & time‑series** nativi → storicizzazione efficiente di **FilingFact** e KPI.
* **Estensioni** utili: `pg_stat_statements` (tuning), `pg_partman` (partizioni), `pgvector` (embedding AI in v1+), FTS robusto per note/report.

### Linee guida pratiche (Postgres)

* **Tipi**: Valute `NUMERIC(18,2)`; Rapporti/Percentuali `NUMERIC(18,6)`; date `timestamptz`.
* **Schema**: materialized view per "KPI snapshot"; GIN su `provenance_json`; indici funzionali (es. `lower(name)`), partial index per query frequenti.
* **Partizionamento**: per **anno fiscale** e/o per `azienda_id×anno` su **FilingFact** e **KPIValore`**.
* **Performance**: `work_mem` adeguato per aggregazioni; `pg_stat_statements` attivo; autovacuum configurato.
* **Laravel**: driver `pgsql`; migrazioni con `decimal`→NUMERIC; utilizzare window functions via query builder/`DB::raw` quando serve.

*Obiettivo*: definire specifiche funzionali, flussi, dati e roadmap per realizzare un MVP rapido e scalabile verso una versione commerciale v1.

---

## 1) Visione & Valore

* **Per chi**: PMI, startup, CFO fractional, studi commercialisti, incubatori.
* **Problema**: numeri dispersi (Excel, gestionali, PDF) e KPI poco leggibili/contestualizzati.
* **Soluzione**: piattaforma che importa i dati, calcola KPI standard, li interpreta (AI), simula scenari e genera report chiari.
* **Differenziali**: insight in linguaggio naturale, benchmark di settore, simulazioni *what‑if*, health score sintetico, multi‑azienda.

---

## 2) Ambito MVP

**Incluso**:

1. Import dati (Excel/CSV template + mapping manuale).
2. KPI Engine (20–30 KPI core).
3. Dashboard KPI (trend, confronti periodo, semafori, grafici).
4. Report PDF (sintesi + note automatiche base).
5. Gestione multi‑azienda (ruoli base).

**Escluso (post‑MVP)**: integrazioni dirette con gestionali, open banking, benchmark esterni automatici, scenari avanzati, automazioni alert.

---

## 3) Personae & Ruoli

* **Owner/CEO**: visione sintetica, pochi KPI, report condivisibili.
* **CFO/Controller**: dettaglio, drill‑down, esportazioni.
* **Consulente/Studio**: multi‑azienda, comparazioni, white‑label (v1).

**Ruoli applicativi (MVP)**

* **Admin** (per tenant): gestisce utenti, aziende, piani.
* **Manager azienda**: importa dati, genera report, vede tutto della propria azienda.
* **Viewer**: sola lettura dashboard/report.

---

## 4) Moduli Funzionali (MVP)

### 4.1 Import Dati

* **Sorgenti**: upload Excel/CSV; copia/incolla tabella; import da PDF via OCR (facoltativo).
* **Template** (minimo):

  * **Stato Patrimoniale**: Attivo corrente, Attivo non corrente, Passivo corrente, Passivo non corrente, Patrimonio netto, Disponibilità liquide, Crediti commerciali, Debiti commerciali, Rimanenze.
  * **Conto Economico**: Ricavi, Costo del venduto, Costi operativi (personale, generali), Ammortamenti, Oneri/Proventi finanziari, Imposte, Utile/Perdita.
  * **Flussi cassa (opzionale MVP)**: CFO, CFI, CFF.
* **Pulizia/Validazione**: formati numerici, periodi (anno/mese/trimestre), coerenza base (es. Attivo = Passivo+PN), segnalazione gap.
* **Mappatura**: wizard per associare colonne del file al template; salvataggio *mapping profile* per riutilizzo.

### 4.2 KPI Engine

* **Input**: dataset normalizzato per periodo (min. annuale; opzionale trimestrale/mensile).
* **Output**: calcolo KPI, normalizzazione (%) / multipli, classificazioni (buono/attenzione/critico) con soglie configurabili.
* **Tracciabilità**: per ogni KPI, mostra formula, input utilizzati e periodo di riferimento.

### 4.3 Dashboard KPI

* **Home azienda**: Health Score, 5 KPI “vitali”, grafico trend generale, alert.
* **Sezioni**: Redditività, Liquidità, Solidità, Efficienza, Crescita.
* **Interazione**: cambio periodo, confronto A vs B (es. 2023 vs 2024), drill‑down su formula e valori grezzi.

### 4.4 Tipologie di Grafici nella Dashboard

Per garantire una visualizzazione chiara e completa dei KPI:

**Grafici principali per ciascuna sezione**

* **Trend temporali (Line chart)**: visualizzazione multi‑anno dei KPI come ROI, ROE, EBITDA margin, ROS.
* **Confronti periodo (Bar chart)**: variazione anno su anno o trimestre su trimestre di ricavi, utile netto, margini.
* **Distribuzioni (Stacked bar)**: composizione dei costi operativi o struttura dell’attivo/passivo.
* **Radar chart (Health Score)**: riepilogo sintetico dei KPI di area (redditività, liquidità, solidità, efficienza).
* **Gauge / semicircle meter**: visualizzazione del livello di KPI singolo rispetto alle soglie (es. Current ratio).
* **Scatter plot (opzionale)**: correlazioni (es. ROI vs Debt/Equity) per analisi di rischio.
* **Heatmap**: intensità performance per KPI su base annuale.

**Dashboard Home**

* Grafico principale: *line chart combinato* dei 5 KPI chiave.
* Box sintetici con valore attuale, variazione %, semaforo e mini‑sparkline.
* Health Score gauge complessivo.
* Alert box con variazioni più rilevanti.

**Requisiti UX per grafici**

* Tooltip con definizione KPI e variazione % vs periodo.
* Colori coerenti con semaforo KPI (verde, giallo, rosso).
* Accessibilità: pattern texture per utenti daltonici, descrizione testuale per screen reader.
* Possibilità di esportazione immagine (PNG) o embed nel report PDF.

### 4.5 Report & Export

* **Report PDF**: copertina (logo, azienda, periodo), executive summary (3‑5 bullet), tabelle KPI con semafori, grafici trend principali, note auto‑generate (template regole).
* **Esportazioni**: CSV/Excel dei KPI calcolati; immagine grafici (PNG).

### 4.6 Gestione Multi‑azienda

* **Anagrafica**: ragione sociale, settore (ATECO opz.), valuta base, anno fiscale.
* **Selezione azienda attiva**, switch rapido, permessi per utente.

---

## 4bis) Compatibilità UE (ESEF/iXBRL & XBRL nazionale)

**Obiettivo**: rendere l'import nativamente compatibile con i bilanci europei standardizzati.

### Standard supportati

* **ESEF iXBRL (società quotate)**: file XHTML con tag XBRL inline basati su IFRS; gestione estensioni nazionali.
* **XBRL nazionale (PMI non quotate)**: tassonomie locali (es. Italia OIC/InfoCamere) allineate alla Direttiva 2013/34/UE.

### Modalità d'import

1. **Upload iXBRL/XBRL** → rilevamento automatico della tassonomia (IFRS/ESEF vs locale).
2. **Parsing** → estrazione facts XBRL con motore parser (es. compatibile Arelle) e normalizzazione unità/valute/periodi.
3. **Mapping** → collegamento dei tag standard alle **ValoriBase** dello schema interno.
4. **Fallback** → wizard di mappatura manuale per tag custom/estesi (salvabile come *MappingProfile* riutilizzabile).

### Esempi di mapping (concettuale)

* `ifrs-full:Revenue` **→** `Ricavi`
* `itcc-ci:CostiProduzione` **→** `Costo del venduto (COGS)`
* `ifrs-full:OperatingProfitLoss` **→** `Risultato Operativo (EBIT)`
* `itcc-sp:TotaleAttivo` **→** `Totale Attivo`
* `itcc-sp:TotalePassivo` **→** `Totale Passivo`
* `itcc-ci:UtilePerditaEsercizio` **→** `Utile netto`

### Regole di normalizzazione

* **Periodi**: supporto *duration* (es. FY2024) e *instant* (es. 31‑12‑2024); calcolo medie (ROE/ROA) su valori medi periodo.
* **Valute**: conversione verso la valuta aziendale; salvataggio della valuta originale per audit.
* **Unità & scale**: gestione `decimals`/`precision`; uniformare a interi o due decimali.
* **Segni**: armonizzazione segno costi/oneri (evitare KPI invertiti).

### UX import dedicata

* Schermata "**Import ESEF/XBRL**" con: selezione file → preview facts rilevanti → suggerimenti di mapping → validazione → salva dataset.
* Log di parsing con errori/avvisi (tag non riconosciuti, periodi sovrapposti, unità mancanti).

### Sicurezza & tracciabilità

* Conservazione file originale; hash; audit di chi/quando ha importato.
* Traccia `fact_id → KPI` per spiegabilità (drill‑down formula → fact di origine).

---

## 5) Catalogo KPI

> Nota: denominazioni e formule devono essere esposte in app con tooltip e guida.

### 5.1 Redditività

* **EBITDA**; **EBITDA margin** = EBITDA / Ricavi.
* **EBIT**; **EBIT margin** = EBIT / Ricavi.
* **ROS** = Risultato Operativo / Ricavi.
* **ROE** = Utile Netto / Patrimonio Netto medio.
* **ROA** = Risultato Operativo / Totale Attivo medio.
* **ROI** = (Ricavi − Costi Operativi) / Capitale Investito medio.

### 5.2 Liquidità

* **Current Ratio** = Attivo Corrente / Passivo Corrente.
* **Quick Ratio** = (Attivo Corrente − Rimanenze) / Passivo Corrente.
* **Cash Ratio** = Liquidità / Passivo Corrente.
* **Capitale Circolante Netto** = Attivo Corrente − Passivo Corrente.

### 5.3 Solidità/Struttura

* **Debt/Equity** = Debiti Totali / Patrimonio Netto.
* **Leverage** = Totale Attivo / Patrimonio Netto.
* **Copertura Immobilizzazioni** = (PN + Passivo non corrente) / Immobilizzazioni.

### 5.4 Efficienza Operativa

* **Rotazione Magazzino** = Costo del Venduto / Rimanenze medie.
* **DSO** (gg incasso) = Crediti Comm. / Ricavi giornalieri.
* **DPO** (gg pagamento) = Debiti Comm. / Acquisti giornalieri (proxy: COGS).
* **DIH** (Days in Inventory) = Rimanenze medie / COGS giornaliero.
* **CCC** (Cash Conversion Cycle) = DSO + DIH − DPO.

### 5.5 Crescita & Resilienza

* **YoY Ricavi/EBITDA/Utile** (%).
* **Interest Coverage** = EBIT / Oneri finanziari.
* **Cash Burn (mensile)** & **Runway** (mesi) se dati cassa disponibili.

**Soglie semaforiche (default suggerite)**

* Current Ratio: <1.0 rosso; 1.0–1.5 giallo; >1.5 verde.
* Debt/Equity: >2.0 rosso; 1.0–2.0 giallo; <1.0 verde.
* EBITDA margin: <5% rosso; 5–15% giallo; >15% verde.
  *(Tutte editabili a livello azienda/settore in v1)*

---

## 6) Insight Engine (Base, MVP)

* **Regole template** (senza LLM):

  * Se KPI varia oltre ±X% → generare nota (miglioramento/peggioramento) con causa probabile (da relazione con 2–3 KPI correlati).
  * Evidenziare top 3 criticità (semafori rossi) e top 3 punti di forza (verdi).
* **Output**: blocco *Executive Summary* + note per sezione (max 3 frasi/area).
* **Trasparenza**: per ogni nota, link ai KPI citati.

*(LLM generativo, chat e spiegazioni in linguaggio naturale sono previsti in 2.0)*

---

## 7) Flussi & User Journey (MVP)

1. **Onboarding tenant** → crea primo utente Admin.
2. **Crea azienda** → inserisci anagrafica, valuta, anno fiscale.
3. **Importa dati** → carica file → mappa colonne → valida → salva dataset per periodo.
4. **Calcolo KPI** → esegui engine → vedi semafori e trend.
5. **Esplora dashboard** → filtra periodo → confronta anni → drill‑down.
6. **Genera report PDF** → scegli layout → scarica/condividi.
7. **Gestione utenti** → invita Manager/Viewer per azienda.

**Edge Cases**

* Dati parziali per un periodo → KPI incompleti: evidenzia “n.d.” e impatto.
* Valute diverse tra periodi → obbligo di normalizzazione (MVP: singola valuta per azienda).
* Anni non allineati (fiscale ≠ solare) → visualizzazione per anno fiscale definito.

---

## 8) Modello Dati Logico (senza codice)

* **Tenant** (id, nome, piano, impostazioni).
* **Utente** (id, nome, email, ruoli: global e per‑azienda).
* **Azienda** (id, tenant_id, ragione_sociale, settore, valuta_base, anno_fiscale_start).
* **Periodo** (id, azienda_id, tipo: anno/trimestre/mese, start, end, stato, valuta).
* **Filing** (id, azienda_id, tipo: XBRL/iXBRL/CSV, percorso_file, hash, **taxonomy_id**, valuta_origine, esenzione_scaling, created_by, created_at).
* **FilingFact** (id, filing_id, **concept_qname**, **context_ref** (periodo/entità), unit_ref, decimals, valore_raw, valore_norm, note).
* **Taxonomy** (id, nome, versione, paese, base: IFRS/OIC, url_schema).
* **TaxonomyMap** (id, taxonomy_id, concept_qname, **valore_base_target** ∈ {Ricavi, COGS, Opex, Ammortamenti, Liquidità, CreditiComm, DebitiComm, Rimanenze, TotaleAttivo, TotalePassivo, PatrimonioNetto, OneriFin, Imposte, …}, regole_segno, moltiplicatore, note).
* **ValoriBase** (id, periodo_id, voce, importo, source: manuale/XBRL, provenienza_filing_id).
* **KPI** (id, codice, nome, descrizione, formula_riferimenti).
* **KPIValore** (id, periodo_id, kpi_id, valore, unita, classe: %/x/gg, stato_semaforo, provenance_json).
* **Report** (id, azienda_id, periodo_range, layout, note_json, file_path).
* **MappingProfile** (id, azienda_id, regole_colonne, regole_concetti_xbrl, creato_da).

**Relazioni**: Tenant 1‑N Azienda; Azienda 1‑N Periodo & Filing; Filing 1‑N FilingFact; Taxonomy 1‑N TaxonomyMap; Periodo 1‑N ValoriBase & KPIValore.

---

## 9) Requisiti Non Funzionali

* **Usabilità**: dashboard responsive, 3 click per raggiungere KPI critico.
* **Performance**: import ≤ 10k righe < 5s; calcolo KPI medio < 2s/periodo.
* **Sicurezza**: RBAC per tenant/azienda; cifratura at‑rest dei file importati; audit base (login, export).
* **Privacy**: Dati contabili = *confidenziali*. Informativa + DPA.
* **Localizzazione**: IT/EN (MVP: IT).
* **Accessibilità**: contrasto, focus order, tabelle con header; WCAG 2.1 AA (minimo: navigazione tastiera, alt grafici con descrizioni testuali).

---

## 10) Roadmap

**MVP (4–5 settimane)**

1. Setup progetto, modelli dati, auth & ruoli.
2. Import + mapping + validazione (Excel/CSV) **+ struttura base per Filing/FilingFact**.
3. KPI Engine + semafori + audit calcoli.
4. Dashboard base (Home + 5 sezioni).
5. Report PDF + esportazioni KPI.
6. Multi‑azienda & ruoli; hardening sicurezza.

**v1.0 (6–8 settimane dopo)**

* **Parsing XBRL/iXBRL (ESEF & tassonomie nazionali)** con libreria compatibile (es. Arelle) e normalizzazione unità/valute/periodi.
* **TaxonomyMap**: mapping di default (IFRS/ESEF e IT‑OIC) + UI per override per azienda.
* Insight Engine con LLM (spiegazioni NL) & Chat “Perché?”.
* Scenario simulator (what‑if parametrici).
* Benchmark settore (seed manuale → integrazioni esterne).
* Alert periodici; White‑label; Stripe; Localizzazione EN completa.

**v1.1+**

* Estensioni tassonomiche aggiuntive (DE/FR/ES), gestione multi‑istanza di concetti equivalenti, auto‑apprendimento mapping da correzioni utente.
* Open Banking per KPI di cassa; SSO (Azure AD/Google).
* Pipeline Monte Carlo light nello Scenario Lab.

---

## 11) Accettazione (Criteri di Done)

* **Import**: almeno 1 file template e 1 file mappato custom importati con successo; log errori consultabile.
* **KPI**: tutte le formule del catalogo calcolate correttamente su 3 periodi; confronto A/B funzionante.
* **Dashboard**: caricamento < 2s con dataset di prova; drill‑down attivo.
* **Report**: PDF generato con executive summary e semafori; nessun “n.d.” senza spiegazione.

---

## 12) Analytics & Telemetria (interno prodotto)

* Tempo medio import, % errori mappatura, KPI più consultati, sezioni con rimbalzo alto.
* Tracciamento export/report (conteggio per azienda/utente).
* Eventi chiave: primo import riuscito, primo report, 3 accessi in 7 gg.

---

## 13) Sicurezza & Compliance (baseline)

* **Accesso**: MFA consigliata; sessioni con timeout; password policy.
* **Dati**: cifratura at‑rest (file upload) e in transito (TLS).
* **Audit**: eventi login, creazione/lettura report, esportazioni.
* **Retention**: politica di conservazione file importati; opzione *soft delete*.
* **Compliance**: GDPR; traccia configurabile per DORA/ISO 27001 in v1 (es. classificazione dati, backup, business continuity, registro trattamenti).

---

## 14) UI/UX (linee guida sintetiche)

* **Home**: Health Score + 5 KPI chiave + trend unico + alert box.
* **Liste**: aziende, periodi, importazioni con stato (successo/errore).
* **Card KPI**: valore, variazione vs periodo, stato semaforo, link a formula.
* **Grafici**: linee per trend; barre per confronti; radar per health score (v1, vedi §4.4).
* **Accessibilità**: tabelle con caption, grafici con descrizioni testuali, focus visibile.

---

## 15) Estensioni v1+ (Backlog sintetico)

* Integrazioni: TeamSystem, Zucchetti, Xero, QuickBooks, CSV bancari;
* Open Banking (saldo, movimenti per cash KPI);
* Scenario Lab avanzato (sensibilità multiparametrica, Monte Carlo light);
* Benchmark feed esterni (ISTAT/AIDA/Cerved con licenza);
* Alert & Scheduler (email/Telegram);
* Health Score configurabile per settore/stadio aziendale;
* White‑label, domini custom, SSO (Azure AD, Google).

---

## 16) Glossario (estratto)

* **Periodo**: intervallo temporale di riferimento (anno/trimestre/mese).
* **ValoriBase**: voci contabili normalizzate usate nelle formule KPI.
* **KPI**: indicatore derivato secondo formula dichiarata e tracciabile.
* **Health Score**: indice sintetico 0–100 derivato da pesi su gruppi KPI (v1).

---

## 17) Materiali operativi (allegati futuri)

* Template Excel/CSV (Stato Patrimoniale/Conto Economico).
* Tabella di mapping standard (IT‑GAAP/IFRS/ESEF → ValoriBase).
* Manuale formule con esempi numerici (dataset fittizio).
* Styleguide report PDF (layout A4 verticale + versione *compact*).
