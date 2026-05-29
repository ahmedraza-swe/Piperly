<?php

namespace App\Filament\Dashboard\Resources\Leads\Widgets;

use App\Models\Lead;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadOverview extends StatsOverviewWidget
{
    protected function getColumns(): int|array
    {
        return 4;
    }

    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()->id;

        $allLeads = Lead::query()
            ->where('tenant_id', $tenantId);

        $total = (clone $allLeads)->count();
        $hot = (clone $allLeads)->where('ai_score', '>=', 70)->count();
        $unassigned = (clone $allLeads)->whereNull('owner_user_id')->count();
        $noActivity = (clone $allLeads)
            ->where(function ($query) {
                $query->whereNull('last_contacted_at')
                    ->orWhere('last_contacted_at', '<=', now()->subDays(7));
            })
            ->count();

        return [
            Stat::make(__('Total Leads'), (string) $total)
                ->description(__('All captured leads'))
                ->color('primary'),
            Stat::make(__('Hot Leads'), (string) $hot)
                ->description(__('AI score >= 70'))
                ->color('success'),
            Stat::make(__('Unassigned'), (string) $unassigned)
                ->description(__('Owner not assigned yet'))
                ->color('warning'),
            Stat::make(__('No Activity'), (string) $noActivity)
                ->description(__('No contact in 7 days'))
                ->color('danger'),
        ];
    }
}
