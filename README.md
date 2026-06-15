<p align="center">
  <img src="public/images/logo-dark.svg" alt="Piperly" width="220" />
</p>

<h1 align="center">Piperly</h1>

<p align="center">
  <strong>Multi-tenant CRM SaaS for sales teams</strong><br>
  Leads, pipeline, contacts, activities, trials, and subscriptions — each company gets its own workspace.
</p>

<p align="center">
  <a href="https://github.com/ahmedraza-swe/Piperly/actions/workflows/ci.yml"><img src="https://github.com/ahmedraza-swe/Piperly/actions/workflows/ci.yml/badge.svg" alt="CI"></a>
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Filament-4-F59E0B" alt="Filament 4">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="MIT License">
</p>

---

## What is Piperly?

**Piperly** is a B2B **multi-tenant CRM SaaS** I am building on top of a Laravel SaaS foundation. Each customer company signs up, gets an isolated **workspace (tenant)**, and manages its sales process in one place — from first lead to closed deal.

The product is designed for **growing sales teams** that need a simple CRM with **self-serve onboarding**, **7-day free trials**, and **paid plans via Stripe** — without juggling spreadsheets or bloated enterprise tools.

| Audience | What they get |
|----------|----------------|
| **Sales teams** | Leads, deals, contacts, activities, pipeline board, dashboard KPIs |
| **Workspace admins** | Team invites, roles, pipeline stages, billing & plans |
| **Platform owner** | Plans, tenants, subscriptions, payments, platform analytics (`/admin`) |

---

## What I built (highlights)

This repository is an active product build, not a static tutorial. Recent work includes:

- **CRM core** — Leads, deals (Kanban pipeline), contacts, activities with due/overdue tracking
- **Multi-tenancy** — Per-company workspaces with Filament dashboard (`/dashboard`)
- **Marketing site** — Landing page with pricing, trial CTAs, and purple glass navigation
- **7-day trial flow** — No-card trial checkout + **auto-trial on registration**
- **Paid subscriptions** — Stripe Checkout, webhooks, plan management
- **Billing & Plans** — Tenant settings page for trial status, upgrade, and paid plans after trial ends
- **Platform admin** — Super-admin panel for plans, tenants, subscriptions, and metrics
- **Automated tests** — Feature tests for checkout, billing, registration, CRM foundation, and Filament resources
- **CI** — GitHub Actions (PHPUnit + Vite build on every push)

**Foundation:** Built on [SaaSykit Tenancy](https://saasykit.com/) (Laravel + Filament SaaS boilerplate), extended with CRM-specific modules and Piperly branding.

---

## Feature status

| Area | Status |
|------|--------|
| Leads, deals, pipeline board | ✅ Implemented |
| Contacts & activities | ✅ Implemented |
| Tenant dashboard & KPI widgets | ✅ Implemented |
| Team, roles, invitations | ✅ Implemented |
| 7-day trial (checkout + register) | ✅ Implemented |
| Stripe paid checkout | ✅ Implemented |
| Billing & Plans (tenant profile) | ✅ Implemented |
| Platform admin (plans, billing, tenants) | ✅ Implemented |
| AI Copilot | 🚧 UI scaffold — next phase |
| Automation rules | 🚧 UI scaffold — next phase |
| E-commerce / catalog | 📋 Planned (see product blueprint docs) |

---

## Tech stack

| Layer | Technologies |
|-------|----------------|
| **Backend** | PHP 8.3, Laravel 12, Livewire 3 |
| **Admin / CRM UI** | Filament 4, Tailwind CSS, Alpine.js |
| **Database** | MySQL 8 |
| **Cache / queues** | Redis, Laravel Horizon (Docker/Sail) |
| **Payments** | Stripe (primary), Paddle/Lemon Squeezy (available in foundation) |
| **Auth** | Session auth, social login hooks, optional OTP |
| **Permissions** | Spatie Laravel Permission (per-tenant roles) |
| **Testing** | PHPUnit, feature tests across services and HTTP layer |
| **CI/CD** | GitHub Actions |
| **Local dev** | Laragon (Windows) or Laravel Sail (Docker) |

---

## Architecture (simplified)

```
┌─────────────────────────────────────────────────────────────┐
│  Marketing site (/)     →  Register / Trial / Stripe checkout │
└──────────────────────────────┬──────────────────────────────┘
                               │
         ┌─────────────────────┴─────────────────────┐
         ▼                                           ▼
┌─────────────────┐                       ┌─────────────────────┐
│ Platform Admin  │                       │ Tenant Dashboard    │
│ /admin          │                       │ /dashboard          │
│ Plans, tenants, │                       │ Leads, deals, CRM,  │
│ subscriptions   │                       │ billing, team       │
└─────────────────┘                       └─────────────────────┘
```

- **Tenancy:** Workspace-scoped data; each user can belong to one or more tenants
- **Subscriptions:** Locally managed trials + Stripe-managed paid plans
- **Services layer:** `TrialProvisioningService`, `WorkspaceBillingService`, `SubscriptionService`, etc.

---

## Quick start (local)

**Requirements:** PHP 8.2+, Composer, Node 20+, MySQL 8

```bash
git clone https://github.com/ahmedraza-swe/Piperly.git
cd Piperly

copy .env.laragon.example .env   # Windows — or cp on Mac/Linux
composer install
npm ci && npm run build

php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan platform:ensure-owner

php artisan serve
# → http://127.0.0.1:8000
```

**Default platform owner** (from `.env`):

| | |
|---|---|
| URL | `/admin` |
| Email | `owner@piperly.test` |
| Password | Value of `PLATFORM_OWNER_PASSWORD` in `.env` |

Full setup (Docker Sail, Redis, Horizon, Stripe webhooks): **[docs/local-development.md](docs/local-development.md)**

---

## Subscription flows

| Flow | Entry | Result |
|------|--------|--------|
| **7-day trial** | Home → Start trial, or **Create account** | Workspace + local trial, no card |
| **Subscribe now** | Home → Subscribe | Stripe Checkout → paid subscription |
| **After trial** | Settings → Billing & Plans | Paid plans only; upgrade path |

Details: **[docs/subscription-flows.md](docs/subscription-flows.md)**

---

## Project structure

```
app/
  Filament/Admin/          # Platform owner panel
  Filament/Dashboard/      # Tenant CRM + settings
  Services/                # Billing, trials, subscriptions, tenants
  Livewire/                # Checkout & auth flows
database/seeders/          # Plans, roles, demo CRM data
resources/views/           # Landing page, auth, checkout
tests/Feature/             # Automated feature tests
docs/                      # Guides (CRM, subscriptions, local dev)
```

---

## Testing

```bash
php artisan test
```

CI runs the full test suite and `npm run build` on pushes to `main` and `develop`.

---

## Documentation

| Doc | Description |
|-----|-------------|
| [docs/crm-guide.md](docs/crm-guide.md) | Product vision, modules, roadmap |
| [docs/subscription-flows.md](docs/subscription-flows.md) | Trial + Stripe setup |
| [docs/local-development.md](docs/local-development.md) | Laragon & Docker Sail |
| [PHASE_1_FOUNDATION.md](PHASE_1_FOUNDATION.md) | Health checks & foundation notes |

---

## Roadmap

1. ~~CRM foundation, trials, billing~~ ✅
2. **AI Copilot** — email drafts, lead scoring, call summaries
3. **Automation** — assignment rules, reminders, stage triggers
4. Production deploy + custom domain
5. E-commerce module (separate product blueprint)

---

## Author

**Ahmed Raza** — [GitHub @ahmedraza-swe](https://github.com/ahmedraza-swe)

Built as a portfolio-grade SaaS product demonstrating full-stack Laravel development: multi-tenancy, payments, admin panels, CRM domain logic, tests, and CI.

---

## License

This project is open-sourced under the [MIT License](LICENSE).

The underlying SaaSykit Tenancy boilerplate has its own license terms — see vendor documentation if you fork for commercial use.
