#!/bin/bash

################################################################################
# Download XBRL/iXBRL Sample Dataset
# 
# Questo script scarica file XBRL di esempio da fonti pubbliche per testing
# della piattaforma CorpVitals24.
#
# Fonti:
# - ESMA Filings Database (ESEF iXBRL)
# - InfoCamere (Italian OIC XBRL - manuale)
# - File sintetici (generati automaticamente)
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Directories
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
SAMPLES_DIR="$PROJECT_ROOT/storage/xbrl-samples"
ESEF_DIR="$SAMPLES_DIR/esef"
OIC_DIR="$SAMPLES_DIR/oic"
SYNTHETIC_DIR="$SAMPLES_DIR/synthetic"

echo -e "${BLUE}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║      CorpVitals24 - XBRL Dataset Acquisition Tool       ║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Ensure directories exist
mkdir -p "$ESEF_DIR" "$OIC_DIR" "$SYNTHETIC_DIR"

################################################################################
# Function: Create synthetic XBRL file
################################################################################
create_synthetic_xbrl() {
    local filename=$1
    local taxonomy=$2
    
    echo -e "${YELLOW}Creating synthetic file: $filename${NC}"
    
    if [ "$taxonomy" == "IFRS" ]; then
        cat > "$SYNTHETIC_DIR/$filename" <<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<xbrl xmlns="http://www.xbrl.org/2003/instance"
      xmlns:xbrli="http://www.xbrl.org/2003/instance"
      xmlns:ifrs-full="http://xbrl.ifrs.org/taxonomy/2023-03-23/ifrs-full"
      xmlns:iso4217="http://www.xbrl.org/2003/iso4217"
      xmlns:xlink="http://www.w3.org/1999/xlink">
  
  <!-- Contexts -->
  <context id="ctx_2023_duration">
    <entity>
      <identifier scheme="http://www.example.com">SYNTHETIC-001</identifier>
    </entity>
    <period>
      <startDate>2023-01-01</startDate>
      <endDate>2023-12-31</endDate>
    </period>
  </context>
  
  <context id="ctx_2023_instant">
    <entity>
      <identifier scheme="http://www.example.com">SYNTHETIC-001</identifier>
    </entity>
    <period>
      <instant>2023-12-31</instant>
    </period>
  </context>
  
  <!-- Units -->
  <unit id="EUR">
    <measure>iso4217:EUR</measure>
  </unit>
  
  <!-- Income Statement Facts -->
  <ifrs-full:Revenue contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    5000000
  </ifrs-full:Revenue>
  
  <ifrs-full:CostOfSales contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -3000000
  </ifrs-full:CostOfSales>
  
  <ifrs-full:OperatingExpense contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -800000
  </ifrs-full:OperatingExpense>
  
  <ifrs-full:DepreciationAndAmortisationExpense contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -200000
  </ifrs-full:DepreciationAndAmortisationExpense>
  
  <ifrs-full:FinanceCosts contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -100000
  </ifrs-full:FinanceCosts>
  
  <ifrs-full:IncomeTaxExpense contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -270000
  </ifrs-full:IncomeTaxExpense>
  
  <ifrs-full:ProfitLoss contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    630000
  </ifrs-full:ProfitLoss>
  
  <!-- Balance Sheet Facts -->
  <ifrs-full:Assets contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    10000000
  </ifrs-full:Assets>
  
  <ifrs-full:CurrentAssets contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    4000000
  </ifrs-full:CurrentAssets>
  
  <ifrs-full:NoncurrentAssets contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    6000000
  </ifrs-full:NoncurrentAssets>
  
  <ifrs-full:CashAndCashEquivalents contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    500000
  </ifrs-full:CashAndCashEquivalents>
  
  <ifrs-full:TradeAndOtherCurrentReceivables contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    1200000
  </ifrs-full:TradeAndOtherCurrentReceivables>
  
  <ifrs-full:Inventories contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    800000
  </ifrs-full:Inventories>
  
  <ifrs-full:Liabilities contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    6000000
  </ifrs-full:Liabilities>
  
  <ifrs-full:CurrentLiabilities contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    2000000
  </ifrs-full:CurrentLiabilities>
  
  <ifrs-full:NoncurrentLiabilities contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    4000000
  </ifrs-full:NoncurrentLiabilities>
  
  <ifrs-full:TradeAndOtherCurrentPayables contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    900000
  </ifrs-full:TradeAndOtherCurrentPayables>
  
  <ifrs-full:Equity contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    4000000
  </ifrs-full:Equity>
  
</xbrl>
EOF
    else
        # OIC synthetic
        cat > "$SYNTHETIC_DIR/$filename" <<'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<xbrl xmlns="http://www.xbrl.org/2003/instance"
      xmlns:xbrli="http://www.xbrl.org/2003/instance"
      xmlns:itcc-ci="http://www.xbrl.org/int/it/ci/2023-01-01"
      xmlns:itcc-sp="http://www.xbrl.org/int/it/sp/2023-01-01"
      xmlns:iso4217="http://www.xbrl.org/2003/iso4217">
  
  <context id="ctx_2023_duration">
    <entity>
      <identifier scheme="http://www.registro-imprese.it">IT12345678901</identifier>
    </entity>
    <period>
      <startDate>2023-01-01</startDate>
      <endDate>2023-12-31</endDate>
    </period>
  </context>
  
  <context id="ctx_2023_instant">
    <entity>
      <identifier scheme="http://www.registro-imprese.it">IT12345678901</identifier>
    </entity>
    <period>
      <instant>2023-12-31</instant>
    </period>
  </context>
  
  <unit id="EUR">
    <measure>iso4217:EUR</measure>
  </unit>
  
  <!-- Conto Economico -->
  <itcc-ci:RicaviVendite contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    2500000
  </itcc-ci:RicaviVendite>
  
  <itcc-ci:CostiProduzione contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -1500000
  </itcc-ci:CostiProduzione>
  
  <itcc-ci:CostiPersonale contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -400000
  </itcc-ci:CostiPersonale>
  
  <itcc-ci:Ammortamenti contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -150000
  </itcc-ci:Ammortamenti>
  
  <itcc-ci:OneriFinanziari contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -50000
  </itcc-ci:OneriFinanziari>
  
  <itcc-ci:Imposte contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    -120000
  </itcc-ci:Imposte>
  
  <itcc-ci:UtilePerditaEsercizio contextRef="ctx_2023_duration" unitRef="EUR" decimals="0">
    280000
  </itcc-ci:UtilePerditaEsercizio>
  
  <!-- Stato Patrimoniale -->
  <itcc-sp:TotaleAttivo contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    5000000
  </itcc-sp:TotaleAttivo>
  
  <itcc-sp:AttivoCircolante contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    2000000
  </itcc-sp:AttivoCircolante>
  
  <itcc-sp:Immobilizzazioni contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    3000000
  </itcc-sp:Immobilizzazioni>
  
  <itcc-sp:DisponibilitaLiquide contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    300000
  </itcc-sp:DisponibilitaLiquide>
  
  <itcc-sp:Crediti contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    600000
  </itcc-sp:Crediti>
  
  <itcc-sp:Rimanenze contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    400000
  </itcc-sp:Rimanenze>
  
  <itcc-sp:TotalePassivo contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    3000000
  </itcc-sp:TotalePassivo>
  
  <itcc-sp:PassivitaCorrente contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    1000000
  </itcc-sp:PassivitaCorrente>
  
  <itcc-sp:DebitiEntroAnno contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    450000
  </itcc-sp:DebitiEntroAnno>
  
  <itcc-sp:PatrimonioNetto contextRef="ctx_2023_instant" unitRef="EUR" decimals="0">
    2000000
  </itcc-sp:PatrimonioNetto>
  
</xbrl>
EOF
    fi
    
    echo -e "${GREEN}✓ Created: $filename${NC}"
}

################################################################################
# Main execution
################################################################################

echo -e "${BLUE}📁 Step 1: Creating synthetic XBRL files...${NC}"
echo ""

create_synthetic_xbrl "sample-ifrs-2023.xbrl" "IFRS"
create_synthetic_xbrl "sample-oic-2023.xbrl" "OIC"

echo ""
echo -e "${GREEN}✓ Synthetic files created successfully${NC}"
echo ""

echo -e "${BLUE}📥 Step 2: ESEF iXBRL Samples (Manual Download)${NC}"
echo ""
echo -e "${YELLOW}⚠️  ESEF files require manual download from ESMA database${NC}"
echo ""
echo "Instructions:"
echo "1. Visit: https://filings.esma.europa.eu/"
echo "2. Filter by:"
echo "   - Country: Italy (or other EU countries)"
echo "   - Document Type: Annual Financial Report"
echo "   - Reporting Standard: ESEF"
echo "   - Year: 2023-2024"
echo "3. Download 5-10 ESEF packages (.zip)"
echo "4. Extract .xhtml files to: $ESEF_DIR"
echo ""
echo -e "${BLUE}Suggested Italian companies:${NC}"
echo "  - Enel S.p.A."
echo "  - UniCredit S.p.A."
echo "  - ENI S.p.A."
echo "  - Intesa Sanpaolo"
echo "  - Leonardo S.p.A."
echo ""

echo -e "${BLUE}📥 Step 3: Italian OIC XBRL Samples (Manual Download)${NC}"
echo ""
echo -e "${YELLOW}⚠️  OIC files require manual download from InfoCamere${NC}"
echo ""
echo "Instructions:"
echo "1. Visit: https://xbrl.registro-imprese.it/"
echo "2. Register/Login"
echo "3. Search for PMI bilanci (year 2022-2023)"
echo "4. Download .xbrl files"
echo "5. Save to: $OIC_DIR"
echo ""

echo -e "${BLUE}📋 Step 4: Create README${NC}"
cat > "$SAMPLES_DIR/README.md" <<'EOFREADME'
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
EOFREADME

echo -e "${GREEN}✓ README created${NC}"
echo ""

echo -e "${GREEN}╔══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                   ✓ Setup Complete!                      ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════╝${NC}"
echo ""

echo -e "${BLUE}📊 Summary:${NC}"
echo "  ✓ Synthetic files: $SYNTHETIC_DIR"
echo "  ⚠  ESEF files: Manual download required → $ESEF_DIR"
echo "  ⚠  OIC files: Manual download required → $OIC_DIR"
echo ""

echo -e "${BLUE}Next Steps:${NC}"
echo "  1. Download ESEF samples from ESMA"
echo "  2. Download OIC samples from InfoCamere"
echo "  3. Run: php artisan db:seed --class=TaxonomiesSeeder"
echo "  4. Test parsing: php artisan tinker"
echo ""

echo -e "${YELLOW}Happy coding! 🚀${NC}"

