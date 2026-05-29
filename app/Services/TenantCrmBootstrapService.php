<?php

namespace App\Services;

use App\Constants\CrmPipelineConstants;
use App\Models\CrmPipelineStage;
use App\Models\Tenant;

class TenantCrmBootstrapService
{
    /**
     * Idempotent CRM defaults for a new tenant (safe if TenantCreated fires more than once).
     */
    public function bootstrapDefaultPipelineStages(Tenant $tenant): void
    {
        if ($tenant->crmPipelineStages()->exists()) {
            return;
        }

        foreach (CrmPipelineConstants::DEFAULT_STAGES as $stage) {
            CrmPipelineStage::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $stage['name'],
                'sort_order' => $stage['sort_order'],
            ]);
        }
    }
}
