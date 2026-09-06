#!/usr/bin/env bash
set -u
echo "===== BLUEGATE LAST DEPLOY ====="
tail -160 /var/log/bluegate-deploy.log 2>/dev/null || echo "No deploy log yet."
echo
echo "===== MIGRATIONS ====="
cd /var/www/bluegate 2>/dev/null && php artisan migrate:status || true
echo
echo "===== LOCAL HEALTH ====="
source /etc/bluegate/install.env 2>/dev/null || true
DOMAIN="${DOMAIN:-localhost}"
curl -i -H "Host: ${DOMAIN}" http://127.0.0.1/up 2>&1 | head -40
