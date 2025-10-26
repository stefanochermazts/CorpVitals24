# Deployment Guide - CorpVitals24

Guida completa al deployment in produzione di CorpVitals24.

---

## 📋 Indice

- [Prerequisiti](#prerequisiti)
- [Server Setup](#server-setup)
- [Database Setup](#database-setup)
- [Application Setup](#application-setup)
- [Web Server Configuration](#web-server-configuration)
- [SSL/TLS Setup](#ssltls-setup)
- [Monitoring & Logging](#monitoring--logging)
- [Backup Strategy](#backup-strategy)
- [Maintenance](#maintenance)
- [Troubleshooting](#troubleshooting)

---

## 🔧 Prerequisiti

### Server Requirements

**Minimo (Production Light)**:
- **CPU**: 2 cores
- **RAM**: 4 GB
- **Disk**: 50 GB SSD
- **OS**: Ubuntu 22.04 LTS / Debian 12

**Raccomandato (Production)**:
- **CPU**: 4 cores
- **RAM**: 8 GB
- **Disk**: 100 GB SSD
- **OS**: Ubuntu 22.04 LTS

### Software Requirements

- PHP 8.3 + Extensions
- PostgreSQL 16.x
- Redis 7.x
- Nginx 1.24+
- Node.js 20.x (per build)
- Supervisor (per queues)
- Certbot (per SSL/TLS)

---

## 🖥️ Server Setup

### 1. Aggiorna Sistema

```bash
sudo apt update
sudo apt upgrade -y
```

### 2. Installa PHP 8.3

```bash
# Add PPA
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 + extensions
sudo apt install -y \
    php8.3-fpm \
    php8.3-cli \
    php8.3-pgsql \
    php8.3-redis \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-gd

# Verify
php -v
```

### 3. Installa PostgreSQL 16

```bash
# Add PostgreSQL APT repository
sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
sudo apt update

# Install PostgreSQL 16
sudo apt install -y postgresql-16 postgresql-contrib-16

# Verify
psql --version
```

### 4. Installa Redis 7

```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf
# Uncomment e imposta: requirepass your_strong_password
# Cambia: bind 127.0.0.1 ::1

# Restart Redis
sudo systemctl restart redis-server
sudo systemctl enable redis-server

# Verify
redis-cli ping
```

### 5. Installa Nginx

```bash
sudo apt install -y nginx

# Verify
nginx -v
```

### 6. Installa Composer

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
rm composer-setup.php

# Verify
composer --version
```

### 7. Installa Node.js 20

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Verify
node -v
npm -v
```

### 8. Installa Supervisor

```bash
sudo apt install -y supervisor

sudo systemctl enable supervisor
sudo systemctl start supervisor
```

---

## 🗄️ Database Setup

### 1. Crea Database User

```bash
sudo -u postgres psql

# In PostgreSQL shell:
CREATE USER corpvitals_prod WITH PASSWORD 'your_strong_password';
CREATE DATABASE corpvitals24_prod OWNER corpvitals_prod;
GRANT ALL PRIVILEGES ON DATABASE corpvitals24_prod TO corpvitals_prod;
\q
```

### 2. Configura PostgreSQL per Connessioni Remote (se necessario)

```bash
sudo nano /etc/postgresql/16/main/postgresql.conf
# Modifica: listen_addresses = 'localhost,<your_app_server_ip>'

sudo nano /etc/postgresql/16/main/pg_hba.conf
# Aggiungi: host corpvitals24_prod corpvitals_prod <app_server_ip>/32 scram-sha-256

sudo systemctl restart postgresql
```

### 3. Abilita SSL per PostgreSQL (Raccomandato)

```bash
# Generate self-signed certificate (o usa Let's Encrypt)
sudo openssl req -new -x509 -days 365 -nodes -text \
    -out /etc/ssl/certs/postgres-selfsigned.crt \
    -keyout /etc/ssl/private/postgres-selfsigned.key

sudo chown postgres:postgres /etc/ssl/certs/postgres-selfsigned.crt
sudo chown postgres:postgres /etc/ssl/private/postgres-selfsigned.key
sudo chmod 600 /etc/ssl/private/postgres-selfsigned.key

# Configure PostgreSQL
sudo nano /etc/postgresql/16/main/postgresql.conf
# Uncomment e configura:
# ssl = on
# ssl_cert_file = '/etc/ssl/certs/postgres-selfsigned.crt'
# ssl_key_file = '/etc/ssl/private/postgres-selfsigned.key'

sudo systemctl restart postgresql
```

---

## 📦 Application Setup

### 1. Crea User Applicazione

```bash
sudo adduser --disabled-password --gecos "" corpvitals
sudo usermod -aG www-data corpvitals
```

### 2. Setup Directory

```bash
sudo mkdir -p /var/www/corpvitals24
sudo chown corpvitals:www-data /var/www/corpvitals24
cd /var/www/corpvitals24
```

### 3. Clone Repository

```bash
sudo -u corpvitals git clone https://github.com/youruser/CorpVitals24.git .

# O upload via FTP/SCP
```

### 4. Installa Dipendenze

```bash
# Backend (come user corpvitals)
sudo -u corpvitals composer install --optimize-autoloader --no-dev

# Frontend (build assets)
sudo -u corpvitals npm ci
sudo -u corpvitals npm run build
```

### 5. Configura Environment

```bash
sudo -u corpvitals cp .env.example .env
sudo -u corpvitals nano .env
```

**Configurazione `.env` Produzione**:

```env
APP_NAME="CorpVitals24"
APP_ENV=production
APP_KEY=                          # Generato dopo
APP_DEBUG=false
APP_TIMEZONE=Europe/Rome
APP_URL=https://yourdomain.com
APP_LOCALE=it

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=corpvitals24_prod
DB_USERNAME=corpvitals_prod
DB_PASSWORD=your_strong_password

# Redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=your_redis_password
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

# Session
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true         # IMPORTANT: true in production
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Cache
CACHE_STORE=redis
CACHE_PREFIX=corpvitals24_cache

# Queue
QUEUE_CONNECTION=redis
QUEUE_PREFIX=corpvitals24_queue

# Security
CORS_ALLOWED_ORIGINS="https://yourdomain.com"
SANCTUM_STATEFUL_DOMAINS="yourdomain.com"

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

# Mail (configure based on your provider)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 6. Generate Keys & Migrate

```bash
# Generate APP_KEY
sudo -u corpvitals php artisan key:generate

# Run migrations
sudo -u corpvitals php artisan migrate --force

# (Optional) Seed database
sudo -u corpvitals php artisan db:seed --force

# Cache config/routes/views
sudo -u corpvitals php artisan config:cache
sudo -u corpvitals php artisan route:cache
sudo -u corpvitals php artisan view:cache
```

### 7. Set Permissions

```bash
sudo chown -R corpvitals:www-data /var/www/corpvitals24
sudo find /var/www/corpvitals24 -type f -exec chmod 644 {} \;
sudo find /var/www/corpvitals24 -type d -exec chmod 755 {} \;

# Writable directories
sudo chmod -R 775 /var/www/corpvitals24/storage
sudo chmod -R 775 /var/www/corpvitals24/bootstrap/cache
```

---

## 🌐 Web Server Configuration

### Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/corpvitals24
```

**Content (`/etc/nginx/sites-available/corpvitals24`)**:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    root /var/www/corpvitals24/public;
    index index.php;

    # SSL Certificates (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers (additional to Laravel middleware)
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Logs
    access_log /var/log/nginx/corpvitals24_access.log;
    error_log /var/log/nginx/corpvitals24_error.log;

    # Character set
    charset utf-8;

    # Disable favicon 404 log
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Serve static files directly
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Laravel front controller
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        # Increase timeouts for large requests
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Enable Site**:

```bash
sudo ln -s /etc/nginx/sites-available/corpvitals24 /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### PHP-FPM Tuning

```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

Modifica:

```ini
memory_limit = 256M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
date.timezone = Europe/Rome
```

```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Modifica:

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

Restart:

```bash
sudo systemctl restart php8.3-fpm
```

---

## 🔒 SSL/TLS Setup

### Using Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test automatic renewal
sudo certbot renew --dry-run
```

Le certificati si rinnoveranno automaticamente.

---

## 📡 Monitoring & Logging

### Setup Log Rotation

```bash
sudo nano /etc/logrotate.d/corpvitals24
```

Content:

```
/var/www/corpvitals24/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 corpvitals www-data
    sharedscripts
    postrotate
        php /var/www/corpvitals24/artisan cache:clear > /dev/null 2>&1 || true
    endscript
}
```

### Setup Queue Workers (Supervisor)

```bash
sudo nano /etc/supervisor/conf.d/corpvitals24-worker.conf
```

Content:

```ini
[program:corpvitals24-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/corpvitals24/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=corpvitals
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/corpvitals24/storage/logs/worker.log
stopwaitsecs=3600
```

Reload Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start corpvitals24-worker:*
```

---

## 💾 Backup Strategy

### 1. Database Backups

Script di backup automatico:

```bash
sudo nano /usr/local/bin/backup-corpvitals-db.sh
```

Content:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/corpvitals24"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="corpvitals24_prod"
DB_USER="corpvitals_prod"

mkdir -p $BACKUP_DIR

# Dump database
pg_dump -U $DB_USER $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Cleanup old backups (keep last 30 days)
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +30 -delete

echo "Backup completed: db_backup_$DATE.sql.gz"
```

```bash
sudo chmod +x /usr/local/bin/backup-corpvitals-db.sh
```

**Cron Job** (daily at 2 AM):

```bash
sudo crontab -e
```

Add:

```
0 2 * * * /usr/local/bin/backup-corpvitals-db.sh >> /var/log/corpvitals_backup.log 2>&1
```

### 2. Application Files Backup

```bash
sudo nano /usr/local/bin/backup-corpvitals-files.sh
```

Content:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/corpvitals24"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/corpvitals24"

mkdir -p $BACKUP_DIR

# Backup storage directory
tar -czf $BACKUP_DIR/storage_backup_$DATE.tar.gz -C $APP_DIR storage

# Cleanup old backups (keep last 7 days)
find $BACKUP_DIR -name "storage_backup_*.tar.gz" -mtime +7 -delete

echo "Files backup completed: storage_backup_$DATE.tar.gz"
```

```bash
sudo chmod +x /usr/local/bin/backup-corpvitals-files.sh
```

Cron (weekly):

```
0 3 * * 0 /usr/local/bin/backup-corpvitals-files.sh >> /var/log/corpvitals_backup.log 2>&1
```

---

## 🔧 Maintenance

### Zero-Downtime Deployment

```bash
#!/bin/bash
# deploy.sh

cd /var/www/corpvitals24

# Enable maintenance mode
php artisan down

# Pull latest code
git pull origin main

# Install dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Run migrations
php artisan migrate --force

# Clear & cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear

# Restart queue workers
sudo supervisorctl restart corpvitals24-worker:*

# Disable maintenance mode
php artisan up

echo "Deployment completed!"
```

### Clear Caches

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🐛 Troubleshooting

### Issue: 500 Internal Server Error

**Check Logs**:
```bash
tail -f /var/www/corpvitals24/storage/logs/laravel.log
tail -f /var/log/nginx/corpvitals24_error.log
```

**Common Fixes**:
- Verifica permissions su `storage/` e `bootstrap/cache/`
- Clear caches
- Check `.env` configuration
- Verify database connection

### Issue: Queue Workers Not Processing Jobs

**Check Status**:
```bash
sudo supervisorctl status corpvitals24-worker:*
```

**Restart Workers**:
```bash
sudo supervisorctl restart corpvitals24-worker:*
```

**Check Logs**:
```bash
tail -f /var/www/corpvitals24/storage/logs/worker.log
```

### Issue: Redis Connection Failed

**Check Redis**:
```bash
redis-cli ping
```

**Restart Redis**:
```bash
sudo systemctl restart redis-server
```

### Issue: Database Connection Failed

**Check PostgreSQL**:
```bash
sudo systemctl status postgresql
sudo -u postgres psql -c "SELECT version();"
```

**Test Connection**:
```bash
psql -h 127.0.0.1 -U corpvitals_prod -d corpvitals24_prod
```

---

## 📞 Support

Per ulteriore supporto:
- **Email**: support@corpvitals24.test
- **Docs**: [https://docs.corpvitals24.test](https://docs.corpvitals24.test)

---

**Last Updated**: 2025-10-26

