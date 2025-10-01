#!/usr/bin/env bash
set -e

echo "[entrypoint] Starting container…"

# 1) Ensure APP_KEY exists (won’t overwrite)
if [ -z "$APP_KEY" ]; then
  echo "[entrypoint] APP_KEY missing → generating…"
  php artisan key:generate --force || true
fi

# 2) Run migrations (safe to rerun)
echo "[entrypoint] Running migrations…"
php artisan migrate --force || true

# 3) Conditionally run seeders (only when you want)
if [ "${RUN_SEED}" = "1" ]; then
  echo "[entrypoint] RUN_SEED=1 → running db:seed…"
  php artisan db:seed --force || true
else
  echo "[entrypoint] RUN_SEED not set → skipping seeding."
fi

# 4) Cache for performance
echo "[entrypoint] Caching config/routes…"
php artisan config:cache || true
php artisan route:cache || true

# 5) Start Apache in foreground
echo "[entrypoint] Starting Apache…"
apache2-foreground
