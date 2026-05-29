<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class TenantDashboardWorkspaceWidget extends Widget
{
    protected string $view = 'filament.dashboard.widgets.tenant-dashboard-workspace-widget';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $tenant = Filament::getTenant();
        $tenantParam = ['tenant' => $tenant];

        $recentDeals = Deal::query()
            ->where('tenant_id', $tenant->id)
            ->latest('updated_at')
            ->limit(6)
            ->get(['id', 'title', 'company_name', 'status', 'value', 'updated_at']);

        $recentLeads = Lead::query()
            ->where('tenant_id', $tenant->id)
            ->latest('updated_at')
            ->limit(6)
            ->get(['id', 'title', 'company_name', 'status', 'value', 'updated_at', 'converted_at']);

        return [
            'recentDeals' => $recentDeals,
            'recentLeads' => $recentLeads,
            'tenantRouteParams' => $tenantParam,
        ];
    }
}
