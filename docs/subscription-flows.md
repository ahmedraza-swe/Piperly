# Subscription flows (trial + paid)

Piperly exposes two checkout paths from the home page pricing cards.

## Flow 1 — 7-day trial (no card)

1. Visitor clicks **Start 7-day trial** on a plan.
2. Route: `GET /checkout/plan/{planSlug}/trial`
3. Guest enters **name + email** only (password auto-generated; set later via forgot-password).
4. System creates user, tenant workspace, CRM pipeline defaults, and a **locally managed** subscription (active for 7 days).
5. Redirect to **tenant dashboard** (`/dashboard`).

### Env

```env
TRIAL_WITHOUT_PAYMENT_ENABLED=true
TRIAL_WITHOUT_PAYMENT_MINIMAL_SIGNUP=true
TRIAL_WITHOUT_PAYMENT_SMS_VERIFICATION_ENABLED=false
```

Re-seed plans after changing trial length:

```bash
php artisan db:seed --class=Database\\Seeders\\CrmPublicPlansSeeder
```

## Flow 2 — Subscribe now (Stripe test)

1. Visitor clicks **Subscribe now** on a plan.
2. Route: `GET /checkout/plan/{planSlug}`
3. Guest registers (name, email, password) and pays via **Stripe Checkout** (no Stripe-side trial on this path).
4. Stripe webhook activates the subscription; user returns to success URL → **tenant dashboard**.

### Stripe test setup

1. Create/test keys at [Stripe Dashboard → Developers → API keys](https://dashboard.stripe.com/test/apikeys).
2. Add to `.env`:

```env
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SIGNING_SECRET=whsec_...
```

3. In `/admin`, confirm **Stripe** is the only active payment provider (seeded by default).
4. Forward webhooks locally:

```bash
stripe listen --forward-to http://127.0.0.1:8000/payments-providers/stripe/webhook
```

Copy the `whsec_...` signing secret into `STRIPE_WEBHOOK_SIGNING_SECRET`.

### Test card

- Number: `4242 4242 4242 4242`
- Expiry: any future date
- CVC: any 3 digits

## Manual test checklist

- [ ] Home → **Start 7-day trial** → name + email → lands in CRM dashboard with empty pipeline
- [ ] Home → **Subscribe now** → Stripe test payment → webhook fires → dashboard accessible
- [ ] Existing user can start trial with email + password
- [ ] Platform owner still reaches `/admin`

## Next phases (after GitHub push)

- Trial expiry / upgrade prompts
- AI Copilot + Automation
