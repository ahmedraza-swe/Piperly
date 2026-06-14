<?php

namespace Tests\Feature\Services;

use App\Constants\SubscriptionStatus;
use App\Constants\SubscriptionType;
use App\Models\Currency;
use App\Models\Interval;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TrialProvisioningService;
use App\Services\WorkspaceBillingService;
use Illuminate\Support\Carbon;
use Tests\Feature\FeatureTest;

class WorkspaceBillingAndTrialTest extends FeatureTest
{
    public function test_registration_provisions_seven_day_trial_for_new_workspace(): void
    {
        config([
            'app.trial_without_payment.enabled' => true,
        ]);

        $planSlug = 'trial-plan-'.rand(1, 1000000);
        config(['platform.default_trial_plan_slug' => $planSlug]);

        $plan = Plan::factory()->create([
            'slug' => $planSlug,
            'is_active' => true,
            'has_trial' => true,
            'trial_interval_count' => 7,
            'trial_interval_id' => Interval::where('slug', 'day')->first()->id,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $user->tenants()->attach($tenant, ['is_default' => true]);

        $subscription = app(TrialProvisioningService::class)->provisionForNewWorkspace($user, $tenant);

        $this->assertNotNull($subscription);
        $this->assertSame(SubscriptionType::LOCALLY_MANAGED, $subscription->type);
        $this->assertNotNull($subscription->ends_at);
    }

    public function test_billing_context_shows_paid_plans_only_after_trial_expires(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $user->tenants()->attach($tenant, ['is_default' => true]);

        $subscription = Subscription::factory()->for($tenant)->for($user)->create([
            'type' => SubscriptionType::LOCALLY_MANAGED,
            'status' => SubscriptionStatus::INACTIVE->value,
            'ends_at' => now()->subDay(),
        ]);

        $ctx = app(WorkspaceBillingService::class)->getContext($tenant, $user);

        $this->assertSame('trial_expired', $ctx['state']);
        $this->assertFalse($ctx['show_trial_offers']);
        $this->assertTrue($ctx['show_paid_plans']);
        $this->assertSame($subscription->id, $ctx['subscription']->id);
    }

    public function test_billing_context_hides_trial_offers_during_active_trial(): void
    {
        Carbon::setTestNow('2026-06-09 12:00:00');

        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $user->tenants()->attach($tenant, ['is_default' => true]);

        Subscription::factory()->for($tenant)->for($user)->create([
            'type' => SubscriptionType::LOCALLY_MANAGED,
            'status' => SubscriptionStatus::ACTIVE->value,
            'ends_at' => now()->addDays(5),
        ]);

        $ctx = app(WorkspaceBillingService::class)->getContext($tenant, $user);

        $this->assertSame('trial_active', $ctx['state']);
        $this->assertFalse($ctx['show_trial_offers']);
        $this->assertTrue($ctx['show_paid_plans']);
        $this->assertSame(5, $ctx['days_remaining']);

        Carbon::setTestNow();
    }
}
