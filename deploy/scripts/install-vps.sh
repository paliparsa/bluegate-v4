#!/usr/bin/env bash
set -Eeuo pipefail

# BlueGate V4 native VPS installer (Ubuntu/Debian)
# Can be called directly from a checked-out repository or through /install.sh.

BLUE='\033[1;34m'; GREEN='\033[1;32m'; YELLOW='\033[1;33m'; RED='\033[1;31m'; NC='\033[0m'
log()  { printf "${BLUE}[BlueGate]${NC} %s\n" "$*"; }
ok()   { printf "${GREEN}[OK]${NC} %s\n" "$*"; }
warn() { printf "${YELLOW}[WARN]${NC} %s\n" "$*"; }
die()  { printf "${RED}[ERROR]${NC} %s\n" "$*" >&2; exit 1; }

[[ ${EUID:-$(id -u)} -eq 0 ]] || die "Run as root: sudo bash deploy/scripts/install-vps.sh"
command -v apt-get >/dev/null 2>&1 || die "Ubuntu/Debian apt host required."

APP_DIR="${APP_DIR:-/var/www/bluegate}"
SOURCE_DIR="${SOURCE_DIR:-/opt/bluegate/source}"
DB_NAME="${DB_NAME:-bluegate}"
DB_USER="${DB_USER:-bluegate}"
DB_PASS="${DB_PASS:-}"
DOMAIN="${DOMAIN:-}"
INSTALL_SSL="${INSTALL_SSL:-ask}"
LE_EMAIL="${LE_EMAIL:-}"
PROJECT_SOURCE="${PROJECT_SOURCE:-$(cd "$(dirname "$0")/../.." && pwd)}"
BLUEGATE_REPO_URL="${BLUEGATE_REPO_URL:-}"
BLUEGATE_BRANCH="${BLUEGATE_BRANCH:-main}"

# Read from /dev/tty so the installer remains interactive when invoked through curl | bash.
ask() {
  local prompt="$1" default="${2:-}" value=""
  if [[ -r /dev/tty ]]; then
    if [[ -n "$default" ]]; then
      read -r -p "$prompt [$default]: " value </dev/tty || true
      printf '%s' "${value:-$default}"
    else
      read -r -p "$prompt: " value </dev/tty || true
      printf '%s' "$value"
    fi
  else
    printf '%s' "$default"
  fi
}

if [[ -z "$DOMAIN" ]]; then DOMAIN="$(ask 'Domain (example: app.bluegate.ir)')"; fi
[[ -n "$DOMAIN" ]] || die "DOMAIN is required. Example: DOMAIN=app.example.com"
[[ "$DOMAIN" =~ ^([A-Za-z0-9-]+\.)+[A-Za-z]{2,}$ ]] || die "Invalid domain: $DOMAIN"
[[ "$DB_NAME" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || die "Invalid DB_NAME"
[[ "$DB_USER" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]] || die "Invalid DB_USER"

if [[ -z "$DB_PASS" ]]; then DB_PASS="$(openssl rand -hex 24 2>/dev/null || true)"; fi
if [[ -z "$DB_PASS" ]]; then
  apt-get update -y && apt-get install -y openssl
  DB_PASS="$(openssl rand -hex 24)"
fi

if [[ "$INSTALL_SSL" == "ask" ]]; then
  ans="$(ask 'Enable HTTPS automatically with Let\x27s Encrypt? (y/N)' 'N')"
  [[ "$ans" =~ ^[Yy]$ ]] && INSTALL_SSL=1 || INSTALL_SSL=0
fi
if [[ "$INSTALL_SSL" == "1" && -z "$LE_EMAIL" ]]; then
  LE_EMAIL="$(ask 'Email for Let\x27s Encrypt notices (optional)' '')"
fi

log "1/10 Installing system packages..."
apt-get update -y
DEBIAN_FRONTEND=noninteractive apt-get install -y \
  nginx postgresql postgresql-contrib redis-server supervisor unzip git curl ca-certificates openssl rsync \
  php-fpm php-cli php-pgsql php-redis php-curl php-mbstring php-xml php-zip php-bcmath php-intl

PHP_VERSION_ID="$(php -r 'echo PHP_VERSION_ID;')"
if (( PHP_VERSION_ID < 80300 )); then
  die "PHP 8.3+ is required by BlueGate V4 / Laravel 12. Use Ubuntu 24.04 LTS or install PHP 8.3+ first."
fi

if ! command -v composer >/dev/null 2>&1; then
  log "2/10 Installing Composer with checksum verification..."
  EXPECTED_CHECKSUM="$(curl -fsSL https://composer.github.io/installer.sig)"
  curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php
  ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")"
  [[ "$EXPECTED_CHECKSUM" == "$ACTUAL_CHECKSUM" ]] || die "Composer installer checksum validation failed."
  php /tmp/composer-setup.php --quiet --install-dir=/usr/local/bin --filename=composer
  rm -f /tmp/composer-setup.php
else
  log "2/10 Composer already installed."
fi

log "3/10 Creating PostgreSQL database and role..."
runuser -u postgres -- psql -v ON_ERROR_STOP=1 --set=db_user="$DB_USER" --set=db_pass="$DB_PASS" --set=db_name="$DB_NAME" <<'SQL'
SELECT format('CREATE ROLE %I LOGIN PASSWORD %L', :'db_user', :'db_pass')
WHERE NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = :'db_user') \gexec
SELECT format('ALTER ROLE %I WITH PASSWORD %L', :'db_user', :'db_pass') \gexec
SELECT format('CREATE DATABASE %I OWNER %I', :'db_name', :'db_user')
WHERE NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = :'db_name') \gexec
SQL

log "4/10 Building Laravel application..."
TMP="$(mktemp -d)"
trap 'rm -rf "${TMP:-}"' EXIT
COMPOSER_ALLOW_SUPERUSER=1 composer create-project laravel/laravel:^12.0 "$TMP/base" --no-interaction --prefer-dist --no-scripts

# Preserve the old .env during an in-place reinstall unless explicitly overridden.
OLD_ENV=""
if [[ -f "$APP_DIR/.env" ]]; then
  OLD_ENV="$(mktemp)"
  cp "$APP_DIR/.env" "$OLD_ENV"
  warn "Existing installation detected; preserving .env."
fi

mkdir -p "$APP_DIR"
rsync -a --delete --exclude='.env' "$TMP/base/" "$APP_DIR/"
rsync -a --exclude='.git' --exclude='.env' "$PROJECT_SOURCE/" "$APP_DIR/"
cd "$APP_DIR"

# The BlueGate overlay has its own composer.json. A freshly-created Laravel
# composer.lock belongs to the base skeleton and must not be trusted for the
# overlay dependencies. If the repository ships a compatible lock file we use
# it; otherwise resolve dependencies once and create a fresh lock file.
if [[ -f composer.lock ]] && COMPOSER_ALLOW_SUPERUSER=1 composer validate --no-check-publish --no-interaction >/tmp/bluegate-composer-validate.log 2>&1; then
  log "Installing locked BlueGate dependencies..."
  COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
else
  warn "composer.lock is missing or stale; resolving BlueGate dependencies and generating a fresh lock file."
  rm -f composer.lock
  COMPOSER_ALLOW_SUPERUSER=1 composer update --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts
fi

if [[ -n "$OLD_ENV" && -s "$OLD_ENV" ]]; then
  cp "$OLD_ENV" .env
else
  cp .env.example .env
fi

# Deterministically set or append env keys.
set_env() {
  local key="$1" value="$2"
  if grep -qE "^${key}=" .env; then
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    printf '\n%s=%s\n' "$key" "$value" >> .env
  fi
}

set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "https://${DOMAIN}"
set_env DB_CONNECTION pgsql
set_env DB_HOST 127.0.0.1
set_env DB_PORT 5432
set_env DB_DATABASE "$DB_NAME"
set_env DB_USERNAME "$DB_USER"
set_env DB_PASSWORD "$DB_PASS"
set_env CACHE_STORE redis
set_env QUEUE_CONNECTION redis
set_env SESSION_DRIVER redis
set_env REDIS_HOST 127.0.0.1

if ! grep -q '^BLUEGATE_TOKEN_PEPPER=' .env; then
  printf '\nBLUEGATE_TOKEN_PEPPER=%s\n' "$(openssl rand -hex 48)" >> .env
fi

if ! grep -qE '^APP_KEY=base64:.+' .env; then php artisan key:generate --force; fi

# Run package discovery only after the production .env is ready. This prevents
# Laravel's default SQLite bootstrap scripts from running during installation.
COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize --no-dev --no-interaction
php artisan package:discover --ansi
php artisan migrate --force
php artisan storage:link >/dev/null 2>&1 || true
php artisan optimize

chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} \;

log "5/10 Configuring Nginx..."
PHP_VER="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
PHP_FPM_SERVICE="php${PHP_VER}-fpm"
[[ -S "/run/php/php${PHP_VER}-fpm.sock" || -f "/lib/systemd/system/${PHP_FPM_SERVICE}.service" ]] || die "PHP-FPM ${PHP_VER} service/socket not found."
sed -e "s/__DOMAIN__/${DOMAIN}/g" -e "s/__PHP_VERSION__/${PHP_VER}/g" "$APP_DIR/deploy/nginx/bluegate.conf" > /etc/nginx/sites-available/bluegate
ln -sfn /etc/nginx/sites-available/bluegate /etc/nginx/sites-enabled/bluegate
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable --now nginx "$PHP_FPM_SERVICE" postgresql redis-server supervisor
systemctl reload nginx

log "6/10 Configuring queue workers..."
sed "s|/var/www/bluegate|${APP_DIR}|g" "$APP_DIR/deploy/supervisor/bluegate-worker.conf" > /etc/supervisor/conf.d/bluegate-worker.conf
supervisorctl reread
supervisorctl update
supervisorctl restart 'bluegate-worker:*' >/dev/null 2>&1 || true

log "7/10 Configuring Laravel scheduler..."
cat > /etc/cron.d/bluegate <<CRON
* * * * * www-data cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1
CRON
chmod 644 /etc/cron.d/bluegate

log "8/10 Writing installation metadata..."
mkdir -p /etc/bluegate
cat > /etc/bluegate/install.env <<META
APP_DIR=${APP_DIR}
SOURCE_DIR=${SOURCE_DIR}
DOMAIN=${DOMAIN}
REPO_URL=${BLUEGATE_REPO_URL}
BRANCH=${BLUEGATE_BRANCH}
META
chmod 600 /etc/bluegate/install.env

log "9/10 HTTPS setup..."
if [[ "$INSTALL_SSL" == "1" ]]; then
  DEBIAN_FRONTEND=noninteractive apt-get install -y certbot python3-certbot-nginx
  CERTBOT_ARGS=(--nginx -d "$DOMAIN" --non-interactive --agree-tos --redirect)
  if [[ -n "$LE_EMAIL" ]]; then CERTBOT_ARGS+=(--email "$LE_EMAIL"); else CERTBOT_ARGS+=(--register-unsafely-without-email); fi
  if certbot "${CERTBOT_ARGS[@]}"; then
    ok "HTTPS enabled for ${DOMAIN}."
  else
    warn "Certbot could not issue a certificate. Verify DNS points ${DOMAIN} to this VPS, then run: certbot --nginx -d ${DOMAIN}"
  fi
else
  warn "HTTPS skipped. After DNS is ready run: certbot --nginx -d ${DOMAIN}"
fi

log "10/10 Running health check..."
LOCAL_HEALTH="$(curl -fsS -H "Host: ${DOMAIN}" http://127.0.0.1/api/v1/health 2>/dev/null || true)"
if [[ -n "$LOCAL_HEALTH" ]]; then
  ok "Local health endpoint responded: ${LOCAL_HEALTH}"
else
  warn "Local health endpoint did not respond yet. Check: journalctl/nginx and ${APP_DIR}/storage/logs/laravel.log"
fi

printf '\n'
ok "BlueGate V4 installation completed."
printf '  Domain:        %s\n' "$DOMAIN"
printf '  App directory: %s\n' "$APP_DIR"
printf '  Database:      %s\n' "$DB_NAME"
printf '  DB credentials: stored in %s/.env\n' "$APP_DIR"
printf '  Update command: sudo bash %s/deploy/scripts/update-vps.sh\n' "$SOURCE_DIR"
printf '\nHealth URL: https://%s/api/v1/health\n' "$DOMAIN"
