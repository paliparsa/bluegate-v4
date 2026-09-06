#!/usr/bin/env bash
set -Eeuo pipefail

# BlueGate V4 GitHub bootstrap installer.
# Intended usage:
#   curl -fsSL https://raw.githubusercontent.com/paliparsa/bluegate-v4/main/install.sh | sudo bash
# or non-interactive:
#   curl -fsSL .../install.sh | sudo env DOMAIN=app.example.com INSTALL_SSL=1 bash

REPO_URL="${REPO_URL:-https://github.com/paliparsa/bluegate-v4.git}"
BRANCH="${BRANCH:-main}"
SOURCE_DIR="${SOURCE_DIR:-/opt/bluegate/source}"

log() { printf '\033[1;34m[BlueGate]\033[0m %s\n' "$*"; }
die() { printf '\033[1;31m[BlueGate ERROR]\033[0m %s\n' "$*" >&2; exit 1; }

[[ ${EUID:-$(id -u)} -eq 0 ]] || die "Run this installer as root (use sudo)."
command -v apt-get >/dev/null 2>&1 || die "Ubuntu/Debian with apt is required."

log "Installing bootstrap dependencies..."
apt-get update -y
DEBIAN_FRONTEND=noninteractive apt-get install -y git ca-certificates curl

if [[ -d "$SOURCE_DIR/.git" ]]; then
  log "Existing BlueGate source found; refreshing ${BRANCH}..."
  git -C "$SOURCE_DIR" remote set-url origin "$REPO_URL"
  git -C "$SOURCE_DIR" fetch --depth=1 origin "$BRANCH"
  git -C "$SOURCE_DIR" reset --hard "origin/$BRANCH"
else
  rm -rf "$SOURCE_DIR"
  mkdir -p "$(dirname "$SOURCE_DIR")"
  log "Cloning ${REPO_URL} (${BRANCH})..."
  git clone --depth=1 --branch "$BRANCH" "$REPO_URL" "$SOURCE_DIR"
fi

[[ -f "$SOURCE_DIR/deploy/scripts/install-vps.sh" ]] || die "deploy/scripts/install-vps.sh not found in repository."
chmod +x "$SOURCE_DIR/deploy/scripts/"*.sh 2>/dev/null || true

export PROJECT_SOURCE="$SOURCE_DIR"
export BLUEGATE_REPO_URL="$REPO_URL"
export BLUEGATE_BRANCH="$BRANCH"
exec bash "$SOURCE_DIR/deploy/scripts/install-vps.sh"
