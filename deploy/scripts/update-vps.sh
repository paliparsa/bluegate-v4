#!/usr/bin/env bash
set -Eeuo pipefail

BLUE='\033[1;34m'; GREEN='\033[1;32m'; YELLOW='\033[1;33m'; RED='\033[1;31m'; NC='\033[0m'
log()  { printf "${BLUE}[BlueGate Update]${NC} %s\n" "$*"; }
warn() { printf "${YELLOW}[WARN]${NC} %s\n" "$*"; }
die()  { printf "${RED}[ERROR]${NC} %s\n" "$*" >&2; exit 1; }

[[ ${EUID:-$(id -u)} -eq 0 ]] || die "Run with sudo/root."
[[ -f /etc/bluegate/install.env ]] || die "/etc/bluegate/install.env not found."
# shellcheck disable=SC1091
source /etc/bluegate/install.env

APP_DIR="${APP_DIR:-/var/www/bluegate}"
SOURCE_DIR="${SOURCE_DIR:-/opt/bluegate/source}"
BRANCH="${BRANCH:-main}"
REPO_URL="${REPO_URL:-}"
DOMAIN="${DOMAIN:-localhost}"
DEPLOY_ROOT="$(dirname "$APP_DIR")/.bluegate-deploy"
LOCK_FILE="/var/lock/bluegate-update.lock"
STAMP="$(date +%Y%m%d%H%M%S)"
STAGE_DIR="${DEPLOY_ROOT}/stage-${STAMP}"
BACKUP_DIR="${DEPLOY_ROOT}/backup-${STAMP}"
OLD_APP_MOVED=0
SWAPPED=0

mkdir -p "$DEPLOY_ROOT"
exec 9>"$LOCK_FILE"
flock -n 9 || die "Another BlueGate update is already running."

[[ -d "$SOURCE_DIR/.git" ]] || die "Git source not found at $SOURCE_DIR"
[[ -f "$APP_DIR/artisan" ]] || die "Laravel app not found at $APP_DIR"

cleanup_stage() {
  [[ -d "$STAGE_DIR" ]] && rm -rf "$STAGE_DIR" || true
}

rollback() {
  local code=$?
  if [[ $code -eq 0 ]]; then
    cleanup_stage
    return 0
  fi

  printf "${RED}[ROLLBACK]${NC} Update failed. Restoring previous application...\n" >&2

  if [[ $SWAPPED -eq 1 ]]; then
    rm -rf "$APP_DIR.failed" 2>/dev/null || true
    [[ -d "$APP_DIR" ]] && mv "$APP_DIR" "$APP_DIR.failed" || true
    if [[ -d "$BACKUP_DIR" ]]; then
      mv "$BACKUP_DIR" "$APP_DIR" || true
    fi
  elif [[ $OLD_APP_MOVED -eq 1 && -d "$BACKUP_DIR" && ! -d "$APP_DIR" ]]; then
    mv "$BACKUP_DIR" "$APP_DIR" || true
  fi

  if [[ -f "$APP_DIR/artisan" ]]; then
    cd "$APP_DIR"
    php artisan optimize:clear >/dev/null 2>&1 || true
    php artisan up >/dev/null 2>&1 || true
  fi

  supervisorctl restart 'bluegate-worker:*' >/dev/null 2>&1 || true
  systemctl reload nginx >/dev/null 2>&1 || true
  cleanup_stage
  exit "$code"
}
trap rollback EXIT

log "Fetching ${BRANCH}..."
[[ -n "$REPO_URL" ]] && git -C "$SOURCE_DIR" remote set-url origin "$REPO_URL"
git -C "$SOURCE_DIR" fetch --prune origin "$BRANCH"
git -C "$SOURCE_DIR" reset --hard "origin/$BRANCH"
NEW_COMMIT="$(git -C "$SOURCE_DIR" rev-parse --short HEAD)"

log "Preparing isolated staging release ${NEW_COMMIT}..."
mkdir -p "$STAGE_DIR"
rsync -a --delete \
  --exclude='.git' \
  --exclude='storage/logs/*' \
  "$APP_DIR/" "$STAGE_DIR/"

# Overlay only repository-managed files; production .env is never replaced.
rsync -a \
  --exclude='.git' \
  --exclude='.env' \
  "$SOURCE_DIR/" "$STAGE_DIR/"

# Ensure the production environment follows the release.
cp -a "$APP_DIR/.env" "$STAGE_DIR/.env"

log "Validating Composer dependencies in staging..."
if [[ -f "$STAGE_DIR/composer.lock" ]] && \
   COMPOSER_ALLOW_SUPERUSER=1 composer validate --working-dir="$STAGE_DIR" --no-check-publish --no-interaction >/tmp/bluegate-composer-validate.log 2>&1; then
  COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --working-dir="$STAGE_DIR" \
    --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
else
  warn "composer.lock is missing/stale. Resolving dependencies inside staging only."
  rm -f "$STAGE_DIR/composer.lock"
  COMPOSER_ALLOW_SUPERUSER=1 composer update \
    --working-dir="$STAGE_DIR" \
    --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress
fi

log "Running pre-deploy application checks..."
cd "$STAGE_DIR"
php artisan optimize:clear
php artisan about >/dev/null
php artisan route:list >/dev/null
php artisan view:cache >/dev/null
if grep -R -F -q "@yield('content')" storage/framework/views 2>/dev/null; then
  die "Blade validation failed: raw @yield('content') survived compilation."
fi
php artisan config:cache >/dev/null
php artisan route:cache >/dev/null

# Database migrations are run only after the new code has passed static/runtime boot checks.
# BlueGate migrations must remain backward-compatible/additive so rollback to the previous code is safe.
log "Applying database migrations..."
php artisan migrate --force
php artisan db:seed --force

chown -R www-data:www-data "$STAGE_DIR"
find "$STAGE_DIR/storage" "$STAGE_DIR/bootstrap/cache" -type d -exec chmod 775 {} \;
find "$STAGE_DIR/storage" "$STAGE_DIR/bootstrap/cache" -type f -exec chmod 664 {} \;

log "Putting the current release into maintenance mode..."
cd "$APP_DIR"
php artisan down --retry=60 || true

log "Atomically switching releases..."
mv "$APP_DIR" "$BACKUP_DIR"
OLD_APP_MOVED=1
mv "$STAGE_DIR" "$APP_DIR"
SWAPPED=1

cd "$APP_DIR"
php artisan storage:link >/dev/null 2>&1 || true
php artisan optimize:clear
php artisan optimize

log "Restarting workers and PHP runtime..."
supervisorctl reread >/dev/null
supervisorctl update >/dev/null
supervisorctl restart 'bluegate-worker:*' >/dev/null 2>&1 || true

PHP_FPM_SERVICE="$(systemctl list-unit-files --type=service | awk '/^php[0-9.]+-fpm\.service/{print $1; exit}')"
[[ -n "$PHP_FPM_SERVICE" ]] && systemctl reload "$PHP_FPM_SERVICE" || true
nginx -t >/dev/null
systemctl reload nginx
php artisan up

log "Running post-deploy health checks..."
# Host header is essential because nginx selects the BlueGate vhost by domain.
HEALTH_OK=0
for _ in {1..10}; do
  if curl -fsS --max-time 5 -H "Host: ${DOMAIN}" "http://127.0.0.1/up" >/dev/null; then
    HEALTH_OK=1
    break
  fi
  sleep 1
done
[[ $HEALTH_OK -eq 1 ]] || die "Post-deploy /up health check failed. Automatic rollback will run."

HOME_STATUS="$(curl -sS -o /tmp/bluegate-home-check.html -w '%{http_code}' --max-time 5 -H "Host: ${DOMAIN}" "http://127.0.0.1/" || true)"
[[ "$HOME_STATUS" =~ ^(200|301|302)$ ]] || die "Homepage health check returned HTTP ${HOME_STATUS}. Automatic rollback will run."

# Keep only the most recent three successful backups.
find "$DEPLOY_ROOT" -maxdepth 1 -type d -name 'backup-*' -printf '%T@ %p\n' 2>/dev/null \
  | sort -nr | awk 'NR>3 {$1=""; sub(/^ /,""); print}' \
  | xargs -r rm -rf

SWAPPED=0
OLD_APP_MOVED=0
trap - EXIT

printf "${GREEN}[OK]${NC} BlueGate updated successfully to %s\n" "$NEW_COMMIT"
printf "${GREEN}[OK]${NC} Previous release backup: %s\n" "$BACKUP_DIR"
