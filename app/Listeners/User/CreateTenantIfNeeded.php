<?php

namespace App\Listeners\User;

use App\Services\SessionService;
use App\Services\TenantCreationService;
use App\Services\TrialProvisioningService;
use Illuminate\Auth\Events\Registered;

class CreateTenantIfNeeded
{
    public function __construct(
        private SessionService $sessionService,
        private TenantCreationService $tenantCreationService,
        private TrialProvisioningService $trialProvisioningService,
    ) {}

    public function handle(Registered $event): void
    {
        if (! $this->sessionService->shouldCreateTenantForFreePlanUser()) {
            return;
        }

        $user = $event->user;
        $tenant = $user->tenants()->orderByPivot('is_default', 'desc')->first();

        if ($tenant === null) {
            $this->tenantCreationService->createTenantForFreePlanUser($user);
            $tenant = $user->tenants()->orderByPivot('is_default', 'desc')->first();
        }

        if ($tenant !== null) {
            $this->trialProvisioningService->provisionForNewWorkspace($user, $tenant);
        }

        $this->sessionService->resetCreateTenantForFreePlanUser();
    }
}
