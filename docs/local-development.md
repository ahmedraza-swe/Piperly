# Local development

Two supported setups: **Laragon** (what you use today) and **Docker Sail** (production-like, Redis + Horizon).

## Quick start — Laragon

1. Copy environment file:
   ```bash
   copy .env.laragon.example .env
   ```
2. Create MySQL database (e.g. `piperly`) in Laragon.
3. Install dependencies:
   ```bash
   composer install
   npm ci
   npm run build
   ```
4. Migrate and seed:
   ```bash
   php artisan migrate
   php artisan db:seed
   php artisan platform:ensure-owner
   ```
5. Open **http://127.0.0.1:8000** (use this host consistently — not `localhost`).

### Laragon notes

- **`VITE_USE_DEV_SERVER=false`** — uses compiled assets from `public/build`. Run `npm run build` after CSS/JS changes.
- **`ext-intl`** — optional on Laragon; the app has fallbacks. Enable intl in Laragon PHP for full money formatting (see `docs/php-runtime-notes.md`).
- **Queues** — default `.env.laragon.example` uses `QUEUE_CONNECTION=database`. Run `php artisan queue:work` in a second terminal when testing async jobs.

## Docker Sail (Redis + Horizon + scheduler)

Matches production more closely. Requires Docker Desktop.

1. Copy Sail env:
   ```bash
   copy .env.example .env
   ```
2. Start stack:
   ```bash
   ./vendor/bin/sail up -d
   ```
   Services: **app**, **mysql**, **redis**, **mailpit**, **horizon**, **scheduler**.
3. First-time setup:
   ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate
   ./vendor/bin/sail artisan db:seed
   ./vendor/bin/sail artisan platform:ensure-owner
   ./vendor/bin/sail npm ci
   ./vendor/bin/sail npm run build
   ```
4. App URL: **http://localhost:8080** (or `APP_PORT` from `.env`).

### Verify Redis & queues

```bash
./vendor/bin/sail artisan stack:verify
./vendor/bin/sail artisan stack:verify --queue
```

Horizon dashboard (local): **http://localhost:8080/horizon** (platform admin login required in non-local).

## Default logins

| Role | URL | Credentials |
|------|-----|-------------|
| Platform owner | `/admin` | `PLATFORM_OWNER_EMAIL` / `PLATFORM_OWNER_PASSWORD` from `.env` |
| Customer | `/login` | Register via home → trial or paid checkout |

**Subscription flows (trial + Stripe):** [subscription-flows.md](subscription-flows.md)

## GitHub & CI

After pushing to GitHub, the **CI** workflow (`.github/workflows/ci.yml`) runs on every push/PR:

- PHP 8.3 tests with MySQL
- `npm run build` + manifest check

No deploy step yet — add staging/production workflows in a later phase.

## Next infra phases

1. Push repo to GitHub (private recommended).
2. Staging server + env secrets.
3. CD workflow (deploy on `main` tag or merge).
4. Managed Redis + Horizon in production.

See also: `docs/crm-guide.md`, `docs/php-runtime-notes.md`.
