#!/usr/bin/env bash
set -Eeuo pipefail

BLUE='\033[1;34m'; GREEN='\033[1;32m'; RED='\033[1;31m'; NC='\033[0m'
log() { printf "${BLUE}[BlueGate Update]${NC} %s\n" "$*"; }
die() { printf "${RED}[ERROR]${NC} %s\n" "$*" >&2; exit 1; }

[[ ${EUID:-$(id -u)} -eq 0 ]] || die "Run with sudo/root."
[[ -f /etc/bluegate/install.env ]] || die "/etc/bluegate/install.env not found. This server was not installed with the GitHub installer."
# shellcheck disable=SC1091
source /etc/bluegate/install.env

APP_DIR="${APP_DIR:-/var/www/bluegate}"
SOURCE_DIR="${SOURCE_DIR:-/opt/bluegate/source}"
BRANCH="${BRANCH:-main}"
REPO_URL="${REPO_URL:-}"

[[ -d "$SOURCE_DIR/.git" ]] || die "Git source not found at $SOURCE_DIR"
[[ -f "$APP_DIR/artisan" ]] || die "Laravel app not found at $APP_DIR"

log "Enabling maintenance mode..."
cd "$APP_DIR"
php artisan down --retry=60 || true
trap 'cd "$APP_DIR" && php artisan up >/dev/null 2>&1 || true' EXIT

log "Fetching ${BRANCH}..."
[[ -n "$REPO_URL" ]] && git -C "$SOURCE_DIR" remote set-url origin "$REPO_URL"
git -C "$SOURCE_DIR" fetch origin "$BRANCH"
git -C "$SOURCE_DIR" reset --hard "origin/$BRANCH"

log "Syncing application overlay..."
rsync -a --exclude='.git' --exclude='.env' "$SOURCE_DIR/" "$APP_DIR/"

log "Installing production dependencies..."
if [[ -f "$APP_DIR/composer.lock" ]] && COMPOSER_ALLOW_SUPERUSER=1 composer validate --working-dir="$APP_DIR" --no-check-publish --no-interaction >/tmp/bluegate-composer-validate.log 2>&1; then
  COMPOSER_ALLOW_SUPERUSER=1 composer install --working-dir="$APP_DIR" --no-dev --prefer-dist --optimize-autoloader --no-interaction
else
  printf '\033[1;33m[WARN]\033[0m composer.lock is missing/stale; refreshing it before update.\n'
  rm -f "$APP_DIR/composer.lock"
  COMPOSER_ALLOW_SUPERUSER=1 composer update --working-dir="$APP_DIR" --no-dev --prefer-dist --optimize-autoloader --no-interaction
fi

log "Applying database migrations..."
cd "$APP_DIR"
php artisan migrate --force
php artisan db:seed --force
php artisan optimize:clear
php artisan optimize
php artisan storage:link >/dev/null 2>&1 || true

chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} \;

log "Restarting workers..."
supervisorctl reread >/dev/null
supervisorctl update >/dev/null
supervisorctl restart 'bluegate-worker:*' >/dev/null 2>&1 || true
php artisan up
trap - EXIT

printf "${GREEN}[OK]${NC} BlueGate updated successfully to: %s\n" "$(git -C "$SOURCE_DIR" rev-parse --short HEAD)"
