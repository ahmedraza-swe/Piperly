<?php

namespace Tests\Feature\Tenant;

use App\Constants\CrmPipelineConstants;
use App\Constants\SessionConstants;
use App\Services\TenantCreationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Session;
use Tests\Feature\FeatureTest;

class TenantOnboardingPhaseOneTest extends FeatureTest
{
    public function test_create_tenant_seeds_default_crm_pipeline_stages(): void
    {
        $user = $this->createUser();

        /** @var TenantCreationService $tenantCreationService */
        $tenantCreationService = app(TenantCreationService::class);

        $tenant = $tenantCreationService->createTenant($user);

        $this->assertCount(count(CrmPipelineConstants::DEFAULT_STAGES), $tenant->crmPipelineStages);
        $this->assertSame(
            array_column(CrmPipelineConstants::DEFAULT_STAGES, 'name'),
            $tenant->crmPipelineStages()->orderBy('sort_order')->pluck('name')->all()
        );
    }

    public function test_create_tenant_for_free_plan_user_is_idempotent(): void
    {
        $user = $this->createUser();

        /** @var TenantCreationService $tenantCreationService */
        $tenantCreationService = app(TenantCreationService::class);

        $tenantCreationService->createTenantForFreePlanUser($user);
        $tenantCreationService->createTenantForFreePlanUser($user);

        $this->assertSame(1, $user->fresh()->tenants()->count());
    }

    public function test_registered_listener_creates_tenant_when_session_flag_set(): void
    {
        $user = $this->createUser();

        Session::put(SessionConstants::SHOULD_CREATE_TENANT_FOR_FREE_PLAN_USER, true);

        event(new Registered($user));

        $this->assertSame(1, $user->fresh()->tenants()->count());
        $this->assertFalse(Session::get(SessionConstants::SHOULD_CREATE_TENANT_FOR_FREE_PLAN_USER, false));
    }
}
