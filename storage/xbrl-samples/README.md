# XBRL Sample Dataset

Dataset di file XBRL/iXBRL per testing della piattaforma CorpVitals24.

## Struttura

```
xbrl-samples/
├── esef/           # ESEF iXBRL (società quotate EU)
├── oic/            # Italian OIC XBRL (PMI)
├── synthetic/      # File sintetici per unit test
└── README.md
```

## File Sintetici

File generati automaticamente per testing unitario:

### IFRS
- `sample-ifrs-2023.xbrl` - Bilancio sintetico IFRS 2023
  - Revenue: €5M
  - Assets: €10M
  - Equity: €4M

### Italian OIC
- `sample-oic-2023.xbrl` - Bilancio sintetico OIC 2023
  - Ricavi: €2.5M
  - Totale Attivo: €5M
  - Patrimonio Netto: €2M

## Acquisizione File Reali

### ESEF (iXBRL)
**Fonte**: ESMA Filings Database  
**URL**: https://filings.esma.europa.eu/  
**Licenza**: Public domain per uso non commerciale  

**Come scaricare**:
1. Filtra per paese, anno, tipo documento
2. Scarica package ESEF (.zip)
3. Estrai file .xhtml
4. Copia in `esef/`

### OIC (XBRL)
**Fonte**: InfoCamere Registro Imprese  
**URL**: https://xbrl.registro-imprese.it/  
**Accesso**: Registrazione richiesta  

**Come scaricare**:
1. Registrati/Login
2. Cerca bilanci PMI
3. Scarica .xbrl
4. Copia in `oic/`

## Note Legali

⚠️ **IMPORTANTE**:
- File utilizzabili **SOLO** per testing/sviluppo
- **NON** redistribuire pubblicamente
- Rispetta termini d'uso ESMA/InfoCamere
- I dati reali sono sensibili e confidenziali

## Validazione

Valida file XBRL con Arelle:

```bash
# Verifica sintassi
python3 /opt/Arelle/arelleCmdLine.py \
  --file sample-ifrs-2023.xbrl \
  --validate

# Estrai facts
python3 /opt/Arelle/arelleCmdLine.py \
  --file sample-ifrs-2023.xbrl \
  --facts output.json
```

## Testing

Usa questi file nei test:

```php
// PHPUnit test
$xbrlPath = storage_path('xbrl-samples/synthetic/sample-ifrs-2023.xbrl');
$result = $xbrlParser->parse($xbrlPath);
$this->assertArrayHasKey('facts', $result);
```

## Statistiche

| Tipo | Count | Dimensione Media | Fonte |
|------|-------|------------------|-------|
| Synthetic | 2 | ~5KB | Auto-generated |
| ESEF | TBD | ~2-5MB | ESMA |
| OIC | TBD | ~500KB | InfoCamere |

Aggiorna queste statistiche dopo download reali.
