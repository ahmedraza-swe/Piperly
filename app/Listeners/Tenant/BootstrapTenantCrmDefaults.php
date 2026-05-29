<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\TenantCreated;
use App\Services\TenantCrmBootstrapService;

class BootstrapTenantCrmDefaults
{
    public function __construct(
        private TenantCrmBootstrapService $tenantCrmBootstrapService,
    ) {}

    public function handle(TenantCreated $event): void
    {
        $this->tenantCrmBootstrapService->bootstrapDefaultPipelineStages($event->tenant);
    }
}
