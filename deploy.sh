#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/home/digi/srv/penomoran-surat"
BRANCH="${2:-main}"

msg() {
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] [deploy] $*"
}

compose_cmd() {
  if docker compose version >/dev/null 2>&1; then
    echo "docker compose"; return
  fi
  if command -v docker-compose >/dev/null 2>&1; then
    echo "docker-compose"; return
  fi
  msg "Docker Compose not found"; exit 1
}

check_load() {
  LOAD=$(awk '{print $1}' /proc/loadavg)
  msg "Current load: $LOAD"

  # kalau load terlalu tinggi, skip deploy
  if (( $(echo "$LOAD > 3.0" | bc -l) )); then
    msg "⚠️ Load terlalu tinggi, deploy dibatalkan"
    exit 1
  fi
}

ensure_env_file() {
  if [[ ! -f "${APP_DIR}/.env" ]]; then
    if [[ -f "${APP_DIR}/.env.example" ]]; then
      msg "Copying .env.example → .env"
      cp "${APP_DIR}/.env.example" "${APP_DIR}/.env"
    else
      msg ".env not found, skip"
    fi
  fi
}

git_update() {
  msg "Updating code..."

  git config --global --add safe.directory ${APP_DIR}
  git fetch origin

  msg "Checkout branch: $BRANCH"
  git checkout $BRANCH
  git reset --hard origin/$BRANCH

  msg "Git updated"
}

compose_up() {
  local compose
  compose=$(compose_cmd)

  msg "Starting containers (safe mode)..."
  ${compose} up -d --build --remove-orphans
}

post_deploy() {
  local compose
  compose=$(compose_cmd)

  msg "Waiting for container to be ready..."
  sleep 3

  msg "Fixing permissions for uploads and writable folders..."
  # Pastikan foldernya ada dulu
  ${compose} exec -T web mkdir -p /var/www/html/public/uploads/nota_dinas
  ${compose} exec -T web mkdir -p /var/www/html/writable
  
  # Eksekusi sebagai root di dalam container untuk mengubah owner ke www-data (UID 33)
  ${compose} exec -T web chown -R www-data:www-data /var/www/html/writable /var/www/html/public/uploads || true
  ${compose} exec -T web chmod -R 775 /var/www/html/writable /var/www/html/public/uploads || true

  msg "Running migration..."
  ${compose} exec -T web php spark migrate --all || true

  msg "Clearing cache..."
  ${compose} exec -T web php spark cache:clear || true
}

deploy_all() {
  msg "START DEPLOY"

  cd "${APP_DIR}"

  check_load
  git_update
  ensure_env_file

  compose_up
  post_deploy

  msg "DEPLOY SUCCESS"
}

case "${1:-}" in
  deploy)
    deploy_all
    ;;
  *)
    echo "Usage: ./deploy.sh deploy [branch]"
    exit 1
    ;;
esac
