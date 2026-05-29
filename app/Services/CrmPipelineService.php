<?php

namespace App\Services;

use App\Models\CrmPipelineStage;
use App\Models\Deal;
use App\Models\Tenant;
use Illuminate\Support\Collection;

class CrmPipelineService
{
    public function ensureDefaultStages(Tenant $tenant): Collection
    {
        $existing = $tenant->crmPipelineStages()->orderBy('sort_order')->get();

        if ($existing->isNotEmpty()) {
            return $existing;
        }

        $defaults = [
            'Prospecting',
            'Qualified',
            'Proposal',
            'Negotiation',
            'Won',
            'Lost',
        ];

        foreach ($defaults as $index => $name) {
            CrmPipelineStage::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $name,
                'sort_order' => $index + 1,
            ]);
        }

        return $tenant->crmPipelineStages()->orderBy('sort_order')->get();
    }

    public function createStage(Tenant $tenant, string $name): CrmPipelineStage
    {
        $maxOrder = (int) $tenant->crmPipelineStages()->max('sort_order');

        return CrmPipelineStage::query()->create([
            'tenant_id' => $tenant->id,
            'name' => trim($name),
            'sort_order' => $maxOrder + 1,
        ]);
    }

    public function updateStageName(CrmPipelineStage $stage, string $name): void
    {
        $stage->update(['name' => trim($name)]);
    }

    public function canDeleteStage(CrmPipelineStage $stage): bool
    {
        if ($stage->leads()->exists()) {
            return false;
        }

        return ! Deal::query()->where('pipeline_stage_id', $stage->id)->exists();
    }

    public function deleteStage(CrmPipelineStage $stage): bool
    {
        if (! $this->canDeleteStage($stage)) {
            return false;
        }

        $stage->delete();

        return true;
    }

    /**
     * @param  array<int, int>  $orderedStageIds
     */
    public function reorderStages(Tenant $tenant, array $orderedStageIds): void
    {
        foreach (array_values($orderedStageIds) as $index => $stageId) {
            CrmPipelineStage::query()
                ->where('tenant_id', $tenant->id)
                ->whereKey($stageId)
                ->update(['sort_order' => $index + 1]);
        }
    }
}
