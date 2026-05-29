<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Deal;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class ReportsTopOwnersWidget extends Widget
{
    public static function canView(): bool
    {
        return ! request()->routeIs('filament.dashboard.pages.tenant-dashboard');
    }

    protected string $view = 'filament.dashboard.widgets.reports-top-owners-widget';

    protected int|string|array $columnSpan = 1;

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $tenant = Filament::getTenant();
        $tenantId = $tenant->id;

        $owners = $tenant->users()
            ->select('users.id', 'users.name')
            ->pluck('users.name', 'users.id');

        $rows = Deal::query()
            ->where('tenant_id', $tenantId)
            ->selectRaw('owner_user_id, COUNT(*) as total_deals, SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won_deals, COALESCE(SUM(value), 0) as total_value')
            ->groupBy('owner_user_id')
            ->orderByDesc('won_deals')
            ->orderByDesc('total_value')
            ->limit(6)
            ->get();

        return [
            'rows' => $rows->map(function (Deal $row) use ($owners): array {
                return [
                    'owner' => $owners[$row->owner_user_id] ?? __('Unassigned'),
                    'total_deals' => (int) ($row->total_deals ?? 0),
                    'won_deals' => (int) ($row->won_deals ?? 0),
                    'total_value' => '$'.number_format((float) ($row->total_value ?? 0), 0, '.', ','),
                ];
            })->all(),
        ];
    }
}
