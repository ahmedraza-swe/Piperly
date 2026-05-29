## CRMercy - AI-Powered Multi-Tenant SaaS CRM

Complete Laravel build guide for a modern CRM SaaS product.

## 1) Product Definition

CRMercy is a **B2B multi-tenant CRM SaaS** where each customer company gets an isolated workspace to manage leads, deals, customers, communication, and automation.

### Core outcomes
- Capture and manage leads from multiple sources
- Move leads through configurable sales pipelines
- Track tasks, activities, emails, calls, and meetings
- Use AI for scoring, summaries, drafting, and follow-up suggestions
- Bill each tenant on subscription plans

## 2) Architecture and Tenancy

### Tenancy model
- Tenancy strategy: **database per tenant** (stancl/tenancy)
- App access pattern:
  - Platform (super admin): `app.crmercy.com/admin`
  - Tenant app: `tenant-slug.crmercy.com/dashboard`

### Roles
- **Super Admin (Platform Owner):** manage tenants, plans, billing, abuse, platform analytics
- **Tenant Admin:** configure workspace, team, pipeline, automations, integrations
- **Sales User / Agent:** manage leads, deals, activities, follow-ups
- **Read-only / Analyst (optional):** view reports without edit rights

## 3) Recommended Stack

- Laravel 11
- Filament v3 (admin + tenant panels)
- Livewire 3
- MySQL 8
- Redis + Horizon
- Laravel Sanctum
- Spatie Permission
- OpenAI PHP (AI features)
- Laravel Cashier (Stripe billing)
- Docker + GitHub Actions CI/CD

## 4) Dashboard Tabs (Tenant Side - Final)

Only these tabs should appear in tenant dashboard navigation:

1. **Dashboard**  
   KPI cards, pipeline value, today's tasks, conversion trend, overdue follow-ups

2. **Leads**  
   Lead CRUD, source tracking, lead owner, status, tags, filters

3. **Pipeline**  
   Kanban stages, drag and drop deals, deal value, forecast

4. **Contacts**  
   Contact/company records, communication timeline, related deals  
   *(Implemented: tenant **Contacts** resource — CRUD + view, optional link to a lead, one primary contact per lead, duplicate-email check, log activities from the contact page, linked names on the lead view, demo seeder contacts.)*

5. **Activities**  
   Calls, meetings, notes, tasks, reminders, due and overdue tracking  
   *(Implemented: tenant **Activities** resource — list/create/view/edit, link lead and/or contact, bulk “mark complete”, overdue / due-today filters, mark complete or reopen from view; new activities bump the linked lead’s `last_contacted_at`; demo seeder rows with `[Demo]` subject prefix.)*

6. **Automation**  
   Rule builder (if/then), stage triggers, assignment rules, reminder workflows

7. **AI Copilot**  
   Email draft, call summary, lead score, next best action, objection handling

8. **Reports**  
   Conversion funnel, pipeline health, rep performance, revenue forecast

9. **Settings**  
   Team/roles, custom fields, pipeline config, integrations, billing profile

## 5) Platform Admin Modules (Super Admin)

- Tenants management
- Plan and pricing management
- Subscriptions and invoices
- Usage metering (AI credits, contacts, leads, seats)
- System health and queue monitoring
- Support tools (impersonation, audit logs)

## 6) Core Data Model (Tenant DB)

### Essential tables
- `users`
- `roles`, `permissions`, `model_has_roles`
- `leads`
- `contacts`
- `companies`
- `pipeline_stages`
- `deals`
- `activities`
- `tasks`
- `notes`
- `tags`, `taggables`
- `custom_fields`, `custom_field_values`
- `automations`, `automation_runs`
- `integrations`

### AI-related tables
- `ai_prompts`
- `ai_usages` (token/cost tracking)
- `lead_scores`

## 7) AI Features (Modern SaaS Standard)

### v1 AI must-have
- AI lead scoring (fit + intent + engagement)
- Smart email/call follow-up draft
- Meeting/call summary to structured notes
- Suggested next action and due date

### v2 AI upgrades
- Predictive churn/risk alerts
- Auto stage recommendation
- AI data enrichment suggestions
- Agent performance coaching insights

## 8) Billing and Plans

### Plan structure
- **Starter:** small team, basic CRM, limited AI credits
- **Growth:** full pipeline, automations, moderate AI credits
- **Scale:** advanced reports, higher limits, priority support

### Billing logic
- Stripe subscriptions via Cashier
- Seat-based pricing + usage add-ons (AI credits)
- Trial period with onboarding checklist
- Grace period, dunning, and failed payment recovery

## 9) Security and Compliance

- Tenant data isolation enforced at DB and app layers
- RBAC with least privilege
- Password reset + email verification
- Audit logs for critical actions
- Rate limiting on login/API/AI endpoints
- Encrypt sensitive integration tokens
- Backups and restore process

## 10) API Surface

Base endpoints:
- `POST /api/auth/login`
- `GET /api/leads`
- `POST /api/leads`
- `GET /api/deals`
- `POST /api/activities`
- `POST /api/ai/email-draft`
- `POST /api/ai/lead-score`

Auth:
- Laravel Sanctum tokens

## 11) Execution Roadmap (Phased)

### Phase 0 - Foundation
- Docker + Laravel setup
- Environment templates
- Health checks

### Phase 1 - Tenancy and Auth
- Tenant registration flow
- Tenant DB provisioning + migration
- Workspace owner seeding
- Basic role system

### Phase 2 - CRM Core
- Leads, contacts, deals, pipeline stages
- Activities and tasks
- Search, filters, tags

### Phase 3 - Tenant Dashboard UI
- Build only approved CRM tabs
- Static placeholders replaced with real data
- Responsive and clean UX

### Phase 4 - AI Copilot
- OpenAI integration
- Scoring job + draft generation
- Usage and cost logging

### Phase 5 - Billing and Plans
- Stripe + Cashier plans
- Tenant subscription guardrails
- Usage limits

### Phase 6 - Reports and Automation
- Funnel reports, rep reports, forecast
- Automation rules and scheduled jobs

### Phase 7 - Platform Admin and Support
- Tenant control center
- Impersonation and audit trail
- Revenue and growth dashboards

### Phase 8 - API, QA, and Go Live
- Public API polish
- Test coverage and CI/CD
- Production hardening

## 12) Test Strategy

- Unit tests: scoring logic, tenancy services, billing guards
- Feature tests: lead lifecycle, pipeline transitions, role permissions
- Integration tests: Stripe webhook handling, OpenAI job queues
- Smoke tests: tenant onboarding to first deal closure

Run:
- `php artisan test`

## 13) Go-Live Checklist

- `APP_ENV=production`
- debug and dev tools disabled
- HTTPS enforced
- queues and Horizon running
- scheduler enabled
- config/routes/views cached
- monitoring and alerts configured
- daily backups verified

## 14) Immediate Implementation Priority

Right now we should implement in this exact order:
1. Finalize tenant dashboard navigation to the 9 CRM tabs above
2. Build Leads + Pipeline + Activities with real data
3. Wire KPI dashboard cards from real tables
4. Add AI Copilot basic actions (email draft + lead score)

This ensures CRMercy launches as a real modern CRM SaaS, not a template.

## 15) Demo CRM data (local)

Seed repeatable sample leads and deals for **every** tenant (rows are tagged with `source = crm_demo_seed` and removed on re-run):

```bash
php artisan db:seed --class=Database\Seeders\CrmDemoDataSeeder
```

The tenant dashboard KPIs, 7-day chart, and “Recent deals / leads” widget read from the database after this runs.

## 16) Tenant dashboard table actions (icons)

List tables in the **tenant** Filament panel use **icon-only** row actions (view / edit / delete) with **tooltips** for labels. New resources should use:

`App\Filament\Dashboard\Support\TableRecordActions` — `viewEditDelete()`, `viewOnly()`, `editOnly()`, or `deleteOnly()` instead of `->button()` on those actions.

For extra row actions (e.g. overflow menu), use `ActionGroup` with `->icon('heroicon-m-ellipsis-vertical')` and `->tooltip(...)`, not a text label on the trigger.
