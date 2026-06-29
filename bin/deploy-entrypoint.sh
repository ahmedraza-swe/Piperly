#!/bin/sh

cd /var/www/html

# Railway MySQL + APP_URL auto-mapping
. /var/www/html/bin/railway-env.sh

echo "==> Piperly deploy boot"
echo "==> PORT=${PORT:-8080} DB_HOST=${DB_HOST:-not-set} DB_DATABASE=${DB_DATABASE:-not-set}"

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

php artisan storage:link 2>/dev/null || true

php artisan config:clear
php artisan route:clear
php artisan view:clear

# Start web server immediately so Railway does not wait on migrations/seed
echo "==> Starting web server on port ${PORT:-8080}"
php artisan serve --host=0.0.0.0 --port="${PORT:-8080}" &
SERVER_PID=$!
sleep 2

if php artisan migrate --force; then
    echo "==> Migrations OK"
    php artisan db:seed --force || echo "==> Seed skipped or partial"
    php artisan platform:apply-branding || true
else
    echo "==> WARNING: migrations failed — link MySQL MYSQL* refs on this service"
fi

php artisan config:cache || php artisan config:clear
php artisan route:cache || php artisan route:clear
php artisan view:cache || true

echo "==> Deploy boot complete (pid $SERVER_PID)"
wait $SERVER_PID
