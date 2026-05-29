<?php

namespace App\Constants;

class CrmPipelineConstants
{
    /**
     * Default sales pipeline stages seeded for every new tenant (Phase 2 Kanban will use these).
     *
     * @var list<array{name: string, sort_order: int}>
     */
    public const DEFAULT_STAGES = [
        ['name' => 'New Lead', 'sort_order' => 0],
        ['name' => 'Qualified', 'sort_order' => 1],
        ['name' => 'Proposal', 'sort_order' => 2],
        ['name' => 'Negotiation', 'sort_order' => 3],
        ['name' => 'Won', 'sort_order' => 4],
        ['name' => 'Lost', 'sort_order' => 5],
    ];
}
