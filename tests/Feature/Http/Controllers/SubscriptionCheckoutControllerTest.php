<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Currency;
use App\Models\Interval;
use App\Models\Plan;
use App\Models\PlanPrice;
use Tests\Feature\FeatureTest;

class SubscriptionCheckoutControllerTest extends FeatureTest
{
    public function test_checkout_loads()
    {
        $planSlug = 'plan-slug-'.rand(1, 1000000);

        $plan = Plan::factory()->create([
            'slug' => $planSlug,
            'is_active' => true,
        ]);

        PlanPrice::create([
            'plan_id' => $plan->id,
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => 100,
        ]);

        $response = $this->followingRedirects()->get(route('checkout.subscription', [
            'planSlug' => $plan->slug,
        ]));

        $response->assertStatus(200);

        $response->assertSee('Subscribe now');
    }

    public function test_checkout_loads_for_plan_with_trial()
    {
        $planSlug = 'plan-slug-'.rand(1, 1000000);

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

        $response = $this->followingRedirects()->get(route('checkout.subscription', [
            'planSlug' => $plan->slug,
        ]));

        $response->assertStatus(200);

        $response->assertSee('Subscribe now');
    }

    public function test_trial_checkout_loads_when_trial_without_payment_enabled()
    {
        config(['app.trial_without_payment.enabled' => true]);

        $planSlug = 'plan-slug-'.rand(1, 1000000);

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

        $response = $this->get(route('checkout.trial', [
            'planSlug' => $plan->slug,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Start your trial');
    }

    public function test_paid_checkout_always_loads_stripe_form_even_when_trial_enabled()
    {
        config(['app.trial_without_payment.enabled' => true]);

        $planSlug = 'plan-slug-'.rand(1, 1000000);

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

        $response = $this->get(route('checkout.subscription', [
            'planSlug' => $plan->slug,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Subscribe now');
    }
}
