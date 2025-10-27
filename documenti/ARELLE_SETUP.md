# Arelle — Setup & Utilizzo (Step 3B)

## Modalità Supportate
- CLI (default): invoca `python3 arelleCmdLine.py` su host
- Docker: invoca `docker exec arelle python /arelle/arelleCmdLine.py`

## Variabili .env
```
ARELLE_PATH=/opt/Arelle/arelleCmdLine.py
XBRL_PARSE_TIMEOUT=300
XBRL_MAX_FILE_SIZE=52428800
XBRL_TAXONOMY_CACHE_TTL=86400
# XBRL_MODE=cli | docker
XBRL_MODE=cli
```

## Installazione — CLI
```
sudo apt-get update && sudo apt-get install -y git python3 python3-pip
sudo mkdir -p /opt && cd /opt
sudo git clone https://github.com/Arelle/Arelle.git
cd Arelle
sudo pip3 install -r requirements.txt
python3 arelleCmdLine.py --help
```

## Installazione — Docker
```
docker build -t corpvitals-arelle -f docker/arelle/Dockerfile .
docker run -d --name arelle -p 8080:8080 \
  -v $(pwd)/storage:/storage corpvitals-arelle
```
Impostare `XBRL_MODE=docker` in `.env` per usare `docker exec`.

## Verifica Integrazione
```
php artisan xbrl:inspect storage/xbrl-samples/synthetic/sample-ifrs-2023.xbrl
```

## Troubleshooting
- "Arelle failed": verificare `ARELLE_PATH` o container in esecuzione
- "output not found": permessi di scrittura in `/tmp`
- "docker exec" error: verificare nome container `arelle`
