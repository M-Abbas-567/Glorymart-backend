#!/usr/bin/env bash
set -e

export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"

mkdir -p \
  storage/framework/sessions \
  storage/framework/views \
  storage/framework/cache \
  storage/framework/cache/data \
  storage/framework/testing \
  storage/logs \
  bootstrap/cache

chmod -R ug+rw storage bootstrap/cache || true

php artisan config:clear
php artisan route:clear
php artisan view:clear

if [ -n "${APP_KEY:-}" ]; then
  php artisan config:cache
  php artisan view:cache
else
  echo "APP_KEY is missing. Set APP_KEY in Railway variables before production traffic."
fi

if [ "${RUN_MIGRATIONS:-true}" = "false" ]; then
  echo "Skipping migrations because RUN_MIGRATIONS=false."
elif [ -n "${DATABASE_URL:-}" ] || [ -n "${MYSQLHOST:-}" ] || [ "${DB_HOST:-127.0.0.1}" != "127.0.0.1" ]; then
  php artisan migrate --force
else
  echo "Skipping migrations because no Railway database variables were found."
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
