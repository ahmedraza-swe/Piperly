<?php

namespace App\Services;

use App\Constants\SubscriptionConstants;
use App\Constants\TenancyPermissionConstants;
use App\Constants\TenantConstants;
use App\Events\Tenant\TenantCreated;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantCreationService
{
    public function __construct(
        private TenantPermissionService $tenantPermissionService,
    ) {}

    public function findUserTenantsForNewOrder(?User $user)
    {
        if ($user === null) {
            return collect();
        }

        return $this->tenantPermissionService->filterTenantsWhereUserHasPermission(
            $user->tenants()->get(),
            TenancyPermissionConstants::PERMISSION_CREATE_ORDERS
        );
    }

    public function findUserTenantForNewOrderByUuid(User $user, ?string $tenantUuid): ?Tenant
    {
        if ($tenantUuid === null) {
            return null;
        }

        return $this->tenantPermissionService->filterTenantsWhereUserHasPermission(
            $user->tenants()->where('uuid', $tenantUuid)->get(),
            TenancyPermissionConstants::PERMISSION_CREATE_ORDERS
        )->first();
    }

    public function findUserTenantsForNewSubscription(?User $user)
    {
        if ($user === null) {
            return collect();
        }

        if (config('app.tenant_multiple_subscriptions_enabled')) {
            return $this->tenantPermissionService->filterTenantsWhereUserHasPermission(
                $user->tenants()->get(),
                TenancyPermissionConstants::PERMISSION_CREATE_SUBSCRIPTIONS
            );
        }

        return $this->tenantPermissionService->filterTenantsWhereUserHasPermission(
            $user->tenants()->whereDoesntHave('subscriptions', function ($query) {
                $query->whereIn('status', SubscriptionConstants::SUBSCRIPTION_STATUS_THAT_ARE_NOT_DEAD);
            })->get(),
            TenancyPermissionConstants::PERMISSION_CREATE_SUBSCRIPTIONS
        );
    }

    public function findUserTenantForNewSubscriptionByUuid(User $user, ?string $tenantUuid): ?Tenant
    {
        if ($tenantUuid === null) {
            return null;
        }

        if (config('app.tenant_multiple_subscriptions_enabled')) {
            return $this->tenantPermissionService->filterTenantsWhereUserHasPermission(
                $user->tenants()
                    ->where('uuid', $tenantUuid)->get(),
                TenancyPermissionConstants::PERMISSION_CREATE_SUBSCRIPTIONS
            )->first();
        }

        return $this->tenantPermissionService->filterTenantsWhereUserHasPermission(
            $user->tenants()->whereDoesntHave('subscriptions', function ($query) {
                $query->whereIn('status', SubscriptionConstants::SUBSCRIPTION_STATUS_THAT_ARE_NOT_DEAD);
            })->where('uuid', $tenantUuid)->get(),
            TenancyPermissionConstants::PERMISSION_CREATE_SUBSCRIPTIONS
        )->first();
    }

    public function createTenant(User $user): Tenant
    {
        return DB::transaction(fn () => $this->createTenantWithinTransaction($user));
    }

    public function createTenantWithName(User $user, string $tenantName): Tenant
    {
        $normalizedName = trim($tenantName);
        if ($normalizedName === '') {
            return $this->createTenant($user);
        }

        return DB::transaction(function () use ($user, $normalizedName) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            $existingTenant = $lockedUser->tenants()->orderByPivot('is_default', 'desc')->first();
            if ($existingTenant !== null) {
                if ($existingTenant->is_name_auto_generated) {
                    $existingTenant->update([
                        'name' => $normalizedName,
                        'is_name_auto_generated' => false,
                    ]);
                }

                return $existingTenant;
            }

            $tenant = Tenant::create([
                'name' => $normalizedName,
                'uuid' => (string) Str::uuid(),
                'is_name_auto_generated' => false,
                'created_by' => $lockedUser->id,
            ]);

            $tenant->users()->attach($lockedUser);

            $this->tenantPermissionService->assignTenantUserRole($tenant, $lockedUser, TenancyPermissionConstants::TENANT_CREATOR_ROLE);

            TenantCreated::dispatch($tenant, $lockedUser);

            return $tenant;
        });
    }

    public function createTenantForFreePlanUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($lockedUser->tenants()->count() === 0) {
                $this->createTenantWithinTransaction($lockedUser);
            }
        });
    }

    /**
     * Create tenant, pivot membership, default role, and CRM bootstrap (via TenantCreated listeners).
     * Must run inside a DB transaction when callers require atomicity.
     */
    private function createTenantWithinTransaction(User $user): Tenant
    {
        $number = $this->nextWorkspaceSuffixNumber($user);

        $name = $user->name.' '.TenantConstants::getAlias();
        $name .= ' #'.$number;

        $tenant = Tenant::create([
            'name' => $name,
            'uuid' => (string) Str::uuid(),
            'is_name_auto_generated' => true,
            'created_by' => $user->id,
        ]);

        $tenant->users()->attach($user);

        $this->tenantPermissionService->assignTenantUserRole($tenant, $user, TenancyPermissionConstants::TENANT_CREATOR_ROLE);

        TenantCreated::dispatch($tenant, $user);

        return $tenant;
    }

    /**
     * Next numeric suffix for auto-generated workspace names (#1, #2, ...), including legacy names without a suffix.
     */
    private function nextWorkspaceSuffixNumber(User $user): int
    {
        $max = 0;

        foreach ($user->tenants()->pluck('name') as $existingName) {
            if (preg_match('/#(\d+)\s*$/', (string) $existingName, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        if ($max > 0) {
            return $max + 1;
        }

        return $user->tenants()->count() + 1;
    }
}
