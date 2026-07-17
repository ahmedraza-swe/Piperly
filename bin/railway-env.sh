#!/bin/sh
# Map Railway/MySQL plugin env vars → Laravel DB_*
# Also fix common Render mistakes (localhost DB, redis without Redis service)

is_local_host() {
    case "${1:-}" in
        ''|127.0.0.1|localhost|::1) return 0 ;;
        *) return 1 ;;
    esac
}

# Prefer MYSQL* when DB_HOST is missing or still pointing at local machine
if [ -n "${MYSQLHOST:-}" ] && ! is_local_host "$MYSQLHOST"; then
    if is_local_host "${DB_HOST:-}" ; then
        export DB_CONNECTION=mysql
        export DB_HOST="$MYSQLHOST"
        export DB_PORT="${MYSQLPORT:-3306}"
        export DB_DATABASE="${MYSQLDATABASE:-railway}"
        export DB_USERNAME="${MYSQLUSER:-root}"
        export DB_PASSWORD="${MYSQLPASSWORD:-}"
        echo "==> Mapped MYSQL* → DB_* (host=$DB_HOST)"
    fi
fi

if [ -n "${MYSQL_URL:-}" ] && [ -z "${DATABASE_URL:-}" ]; then
    export DATABASE_URL="$MYSQL_URL"
fi

# Railway public domain → APP_URL if not set
if [ -n "${RAILWAY_PUBLIC_DOMAIN:-}" ] && [ -z "${APP_URL:-}" ]; then
    export APP_URL="https://${RAILWAY_PUBLIC_DOMAIN}"
fi

# Render / Railway: never emit http:// asset URLs (mixed content = no CSS)
if [ -n "${APP_URL:-}" ]; then
    export APP_URL="$(printf '%s' "$APP_URL" | sed 's|^http://|https://|')"
elif [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$(printf '%s' "$RENDER_EXTERNAL_URL" | sed 's|^http://|https://|')"
fi

# Render free tier has no Redis — redis + 127.0.0.1 causes 500s
if is_local_host "${REDIS_HOST:-127.0.0.1}"; then
    case "${CACHE_DRIVER:-}" in
        redis) export CACHE_DRIVER=file; echo "==> CACHE_DRIVER forced to file (no Redis)" ;;
    esac
    case "${SESSION_DRIVER:-}" in
        redis) export SESSION_DRIVER=file; echo "==> SESSION_DRIVER forced to file (no Redis)" ;;
    esac
    case "${QUEUE_CONNECTION:-}" in
        redis) export QUEUE_CONNECTION=database; echo "==> QUEUE_CONNECTION forced to database (no Redis)" ;;
    esac
fi

# SESSION_DOMAIN=.piperly.com breaks cookies on *.onrender.com
if [ -n "${SESSION_DOMAIN:-}" ] && [ -n "${APP_URL:-}" ]; then
    case "$APP_URL" in
        *"${SESSION_DOMAIN}"*|*"${SESSION_DOMAIN#.}"*) ;;
        *)
            echo "==> Clearing SESSION_DOMAIN=$SESSION_DOMAIN (does not match APP_URL)"
            unset SESSION_DOMAIN
            export SESSION_DOMAIN=
            ;;
    esac
fi

# Strip accidental newlines from Render dashboard pastes (e.g. false\n)
if [ -n "${VITE_USE_DEV_SERVER:-}" ]; then
    export VITE_USE_DEV_SERVER="$(printf '%s' "$VITE_USE_DEV_SERVER" | tr -d '\r\n')"
fi
