<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Deal;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReportsOverviewWidget extends StatsOverviewWidget
{
    /**
     * Hide from the tenant home dashboard; still used on the Reports page header.
     */
    public static function canView(): bool
    {
        return ! request()->routeIs('filament.dashboard.pages.tenant-dashboard');
    }

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()->id;

        $wonDeals = (int) Deal::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'won')
            ->count();

        $lostDeals = (int) Deal::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'lost')
            ->count();

        $closedDeals = $wonDeals + $lostDeals;
        $winRate = $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100, 1) : 0.0;

        $avgCycle = (int) round(
            Deal::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'won')
                ->whereNotNull('closed_at')
                ->get(['created_at', 'closed_at'])
                ->avg(function (Deal $deal): float {
                    return max(0, Carbon::parse($deal->created_at)->diffInDays(Carbon::parse($deal->closed_at)));
                }) ?? 0
        );

        $forecast30d = (float) Deal::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->sum('value');

        return [
            Stat::make(__('Win rate'), $winRate.'%')
                ->description(__('Won deals over all closed deals'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),
            Stat::make(__('Avg sales cycle'), $avgCycle.' '.__('days'))
                ->description(__('Average days from created to won'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make(__('Forecast (30d)'), '$'.number_format($forecast30d, 0, '.', ','))
                ->description(__('Based on current open pipeline value'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
        ];
    }
}
