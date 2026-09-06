#!/usr/bin/env bash
set -Eeuo pipefail
[[ ${EUID:-$(id -u)} -eq 0 ]] || { echo "[ERROR] Run with sudo/root."; exit 1; }

# Never inherit a deleted/stale working directory from the interactive shell.
# Atomic releases may move/remove /var/www/bluegate while an SSH session is still cd'ed into it.
cd / || { echo "[ERROR] Cannot switch to a safe working directory."; exit 1; }
[[ -f /etc/bluegate/install.env ]] || { echo "[ERROR] /etc/bluegate/install.env missing."; exit 1; }
# shellcheck disable=SC1091
source /etc/bluegate/install.env
SOURCE_DIR="${SOURCE_DIR:-/opt/bluegate/source}"
BRANCH="${BRANCH:-main}"
REPO_URL="${REPO_URL:-}"
[[ -d "$SOURCE_DIR/.git" ]] || { echo "[ERROR] Git source missing: $SOURCE_DIR"; exit 1; }

echo "[BlueGate] Refreshing updater from GitHub before deployment..."
[[ -n "$REPO_URL" ]] && git -C "$SOURCE_DIR" remote set-url origin "$REPO_URL"
git -C "$SOURCE_DIR" fetch --prune origin "$BRANCH"
git -C "$SOURCE_DIR" reset --hard "origin/$BRANCH"

ENGINE="$SOURCE_DIR/deploy/scripts/deploy-release.sh"
[[ -f "$ENGINE" ]] || { echo "[ERROR] New deployment engine not found: $ENGINE"; exit 1; }

TMP="$(mktemp /tmp/bluegate-deploy.XXXXXX.sh)"
cp "$ENGINE" "$TMP"
chmod 700 "$TMP"
trap 'rm -f "$TMP"' EXIT
exec bash "$TMP"
