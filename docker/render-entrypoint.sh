#!/bin/sh
set -e

cd /var/www/html

echo "==> Piperly Render boot"

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

php artisan storage:link 2>/dev/null || true

php artisan config:clear
php artisan route:clear
php artisan view:clear

if php artisan migrate --force; then
    echo "==> Migrations OK"

    # No Render Shell needed — seed + branding run automatically on each deploy
    php artisan db:seed --force || echo "==> Seed skipped or partial"
    php artisan platform:apply-branding || true
else
    echo "==> WARNING: migrations failed — set DB_* env vars in Render Environment"
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting web server on port ${PORT:-10000}"
exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
