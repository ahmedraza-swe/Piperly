<?php

namespace App\Services;

use App\Exceptions\SubscriptionCreationNotAllowedException;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;

class TrialProvisioningService
{
    public function __construct(
        private SubscriptionService $subscriptionService,
    ) {}

    /**
     * Start the default 7-day local trial for a newly created workspace.
     */
    public function provisionForNewWorkspace(User $user, Tenant $tenant): ?Subscription
    {
        if (! config('app.trial_without_payment.enabled')) {
            return null;
        }

        if (! $this->subscriptionService->canUserHaveSubscriptionTrial($user)) {
            return null;
        }

        if (! $this->subscriptionService->canCreateSubscription($tenant->id)) {
            return null;
        }

        $planSlug = (string) config('platform.default_trial_plan_slug', 'starter-monthly');

        try {
            $subscription = $this->subscriptionService->findNewByPlanSlugAndTenant($planSlug, $tenant);

            if ($subscription === null) {
                $subscription = $this->subscriptionService->create(
                    planSlug: $planSlug,
                    userId: $user->id,
                    quantity: 1,
                    tenant: $tenant,
                    localSubscription: true,
                );
            }

            return $subscription;
        } catch (SubscriptionCreationNotAllowedException) {
            return null;
        }
    }
}
