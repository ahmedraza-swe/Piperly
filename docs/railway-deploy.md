# Deploy Piperly on Railway (app + MySQL)

One Railway project: **Web service** (Docker) + **MySQL** database. Migrations and seed run automatically on deploy — no Shell needed.

## 1. Create Railway project

1. Go to [railway.app](https://railway.app) → **New Project**
2. **Deploy from GitHub repo** → select `ahmedraza-swe/Piperly`
3. Railway detects `railway.toml` + `Dockerfile` automatically

## 2. Add MySQL

1. In the same project → **+ New** → **Database** → **MySQL**
2. Wait until MySQL shows **Active**
3. On the **Piperly web service** → **Variables** → add references from MySQL (service name may be `MySQL`):

```
MYSQLHOST=${{MySQL.MYSQLHOST}}
MYSQLPORT=${{MySQL.MYSQLPORT}}
MYSQLUSER=${{MySQL.MYSQLUSER}}
MYSQLPASSWORD=${{MySQL.MYSQLPASSWORD}}
MYSQLDATABASE=${{MySQL.MYSQLDATABASE}}
```

The deploy entrypoint maps `MYSQL*` → Laravel `DB_*` automatically.

## 3. Set environment variables (Web service)

Open your **Piperly web service** → **Variables** → **RAW Editor** and paste from `.env.railway.example`, or add manually:

| Variable | Value |
|----------|--------|
| `APP_KEY` | `base64:EcjAm1p7YpnFjru2lEGYxXoisoiBAQiWnw6csnOULxE=` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://${{RAILWAY_PUBLIC_DOMAIN}}` |
| `PLATFORM_OWNER_EMAIL` | `owner@piperly.com` |
| `PLATFORM_OWNER_PASSWORD` | your strong password |
| `CACHE_DRIVER` | `file` |
| `SESSION_DRIVER` | `file` |
| `QUEUE_CONNECTION` | `database` |
| `LOG_CHANNEL` | `stderr` |
| `VITE_USE_DEV_SERVER` | `false` |

## 4. Generate public URL

1. Web service → **Settings** → **Networking** → **Generate Domain**
2. Copy URL (e.g. `piperly-production.up.railway.app`)
3. Ensure `APP_URL` uses that domain (Railway variable above does this automatically)

## 5. Deploy

Push to `main` on GitHub — Railway redeploys automatically.

Or: Railway dashboard → **Deploy** → **Redeploy**

Watch **Deploy Logs** for:

```
==> Migrations OK
==> Starting web server on port ...
```

## 6. Verify

| URL | Expected |
|-----|----------|
| `https://your-app.up.railway.app/healthz` | `"database": "ok"` |
| `https://your-app.up.railway.app/` | Home page |
| `https://your-app.up.railway.app/admin` | Platform login |

**Admin login:** `PLATFORM_OWNER_EMAIL` / `PLATFORM_OWNER_PASSWORD`

## 7. Render + Railway together (optional)

If app stays on **Render** but DB is on **Railway**:

1. MySQL service → **Connect** → copy **Public** connection details
2. Paste into Render Environment as `DB_HOST`, `DB_PORT`, etc.
3. Enable **Public Networking** on Railway MySQL if connecting from Render

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Healthcheck failure (build OK) | Add MySQL + `MYSQL*` variable references on web service; healthcheck uses `/up` |
| Build failed (`chmod: deploy-entrypoint.sh`) | Redeploy latest `main` — scripts live in `bin/` |
| Migrations failed in logs | Add MySQL service to same project or set `DB_*` |
| Assets broken | `VITE_USE_DEV_SERVER=false` (assets built in Docker) |
| Wrong app name | Runs `platform:apply-branding` on each deploy |

## What runs on each deploy (automatic)

- `php artisan migrate --force`
- `php artisan db:seed --force`
- `php artisan platform:apply-branding`
- `php artisan config:cache` / `route:cache` / `view:cache`

No manual commands required after env vars are set.
