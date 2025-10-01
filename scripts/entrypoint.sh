#!/usr/bin/env bash
set -e

# If APP_KEY is missing, try to generate one (won’t overwrite existing)
if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force || true
fi

# Run migrations (safe to rerun)
php artisan migrate --force || true

# Cache config/routes for speed (ignore errors if any)
php artisan config:cache || true
php artisan route:cache || true

# Start Apache in foreground (Render expects foreground process)
apache2-foreground
