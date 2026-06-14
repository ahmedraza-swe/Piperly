<?php

namespace App\Services;

use App\Constants\SubscriptionConstants;
use App\Constants\SubscriptionStatus;
use App\Constants\SubscriptionType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WorkspaceBillingService
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private PlanService $planService,
    ) {}

    /**
     * @return array{
     *     state: string,
     *     subscription: ?Subscription,
     *     days_remaining: ?int,
     *     trial_ends_at: ?Carbon,
     *     show_trial_offers: bool,
     *     show_paid_plans: bool,
     *     plan: ?Plan,
     *     marketing_plans: Collection,
     * }
     */
    public function getContext(Tenant $tenant, User $user): array
    {
        $subscription = $this->resolvePrimarySubscription($tenant);
        $state = $this->resolveState($subscription);
        $showTrialOffers = $this->shouldShowTrialOffers($user, $subscription, $state);
        $showPaidPlans = in_array($state, ['trial_expired', 'inactive', 'none', 'trial_active'], true);

        $trialEndsAt = $subscription?->trial_ends_at ?? $subscription?->ends_at;
        $daysRemaining = null;
        if ($state === 'trial_active' && $trialEndsAt !== null) {
            $daysRemaining = max(0, (int) now()->diffInDays(Carbon::parse($trialEndsAt), false));
        }

        return [
            'state' => $state,
            'subscription' => $subscription,
            'days_remaining' => $daysRemaining,
            'trial_ends_at' => $trialEndsAt ? Carbon::parse($trialEndsAt) : null,
            'show_trial_offers' => $showTrialOffers,
            'show_paid_plans' => $showPaidPlans,
            'plan' => $subscription?->plan,
            'marketing_plans' => $this->marketingPlans(),
        ];
    }

    private function resolvePrimarySubscription(Tenant $tenant): ?Subscription
    {
        $active = $tenant->subscriptions()
            ->whereIn('status', SubscriptionConstants::SUBSCRIPTION_STATUS_THAT_ARE_NOT_DEAD)
            ->with(['plan.product', 'plan.interval', 'currency'])
            ->orderByDesc('updated_at')
            ->first();

        if ($active !== null) {
            return $active;
        }

        return $tenant->subscriptions()
            ->with(['plan.product', 'plan.interval', 'currency'])
            ->orderByDesc('updated_at')
            ->first();
    }

    private function resolveState(?Subscription $subscription): string
    {
        if ($subscription === null) {
            return 'none';
        }

        if ($subscription->status === SubscriptionStatus::ACTIVE->value) {
            if ($subscription->type === SubscriptionType::LOCALLY_MANAGED) {
                $endsAt = $subscription->ends_at ?? $subscription->trial_ends_at;
                if ($endsAt !== null && Carbon::parse($endsAt)->isPast()) {
                    return 'trial_expired';
                }

                return 'trial_active';
            }

            return 'paid_active';
        }

        if (
            $subscription->type === SubscriptionType::LOCALLY_MANAGED
            && in_array($subscription->status, [SubscriptionStatus::INACTIVE->value, SubscriptionStatus::CANCELED->value], true)
        ) {
            return 'trial_expired';
        }

        if (in_array($subscription->status, [SubscriptionStatus::PENDING->value, SubscriptionStatus::PAST_DUE->value], true)) {
            return 'paid_active';
        }

        return 'inactive';
    }

    private function shouldShowTrialOffers(User $user, ?Subscription $subscription, string $state): bool
    {
        if (! config('app.trial_without_payment.enabled')) {
            return false;
        }

        if (! $this->subscriptionService->canUserHaveSubscriptionTrial($user)) {
            return false;
        }

        if (in_array($state, ['trial_active', 'paid_active'], true)) {
            return false;
        }

        if ($state === 'trial_expired') {
            return false;
        }

        if ($subscription !== null && $this->subscriptionService->isLocalSubscription($subscription)) {
            return false;
        }

        return $state === 'none' || $state === 'inactive';
    }

    private function marketingPlans(): Collection
    {
        $slugs = config('platform.marketing_product_slugs', ['starter', 'growth']);

        return $this->planService->getAllPlansWithPrices(
            $slugs,
            null,
            onlyVisible: true,
        );
    }
}
