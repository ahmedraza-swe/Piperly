#!/bin/sh
# Map Railway MySQL plugin env vars → Laravel DB_* (when DB_HOST not set manually)
if [ -n "${MYSQLHOST:-}" ] && [ -z "${DB_HOST:-}" ]; then
    export DB_CONNECTION=mysql
    export DB_HOST="$MYSQLHOST"
    export DB_PORT="${MYSQLPORT:-3306}"
    export DB_DATABASE="${MYSQLDATABASE:-railway}"
    export DB_USERNAME="${MYSQLUSER:-root}"
    export DB_PASSWORD="${MYSQLPASSWORD:-}"
fi

if [ -n "${MYSQL_URL:-}" ] && [ -z "${DATABASE_URL:-}" ]; then
    export DATABASE_URL="$MYSQL_URL"
fi

# Railway public domain → APP_URL if not set
if [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ] && [ -z "${APP_URL:-}" ]; then
    export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
fi
