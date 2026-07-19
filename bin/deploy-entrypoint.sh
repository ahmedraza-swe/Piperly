#!/bin/sh

cd /var/www/html

# Railway MySQL + APP_URL auto-mapping
. /var/www/html/bin/railway-env.sh

# Safe defaults (no Redis required)
export CACHE_DRIVER="${CACHE_DRIVER:-file}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-database}"

echo "==> Piperly deploy boot"
echo "==> PORT=${PORT:-8080} DB_HOST=${DB_HOST:-not-set} DB_DATABASE=${DB_DATABASE:-not-set}"
echo "==> SESSION_DRIVER=$SESSION_DRIVER CACHE_DRIVER=$CACHE_DRIVER APP_URL=${APP_URL:-not-set}"

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

php artisan storage:link 2>/dev/null || true

php artisan config:clear
php artisan route:clear
php artisan view:clear

# Migrate/seed before serve so Railway tracks php as the main process (avoids 502)
if php artisan migrate --force; then
    echo "==> Migrations OK"
    php artisan db:seed --force || echo "==> Seed skipped or partial"
    php artisan platform:apply-branding || true
    php artisan platform:ensure-owner || true
else
    echo "==> WARNING: migrations failed — check MYSQL* / DB_* variables"
    export SESSION_DRIVER=file
    export CACHE_DRIVER=file
    export QUEUE_CONNECTION=sync
fi

php artisan config:cache || php artisan config:clear
php artisan route:cache || php artisan route:clear
php artisan view:cache || true

echo "==> Starting web server on port ${PORT:-8080}"
# exec = PID 1 so Railway keeps the service healthy
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
