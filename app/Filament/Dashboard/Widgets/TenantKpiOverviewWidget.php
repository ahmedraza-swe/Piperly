<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TenantKpiOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    /**
     * @var int | array<string, ?int> | null
     */
    protected int|array|null $columns = [
        'default' => 1,
        '@sm' => 2,
        '@xl' => 4,
    ];

    protected function getStats(): array
    {
        $tenantId = Filament::getTenant()->id;

        $activeLeads = Lead::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('converted_at')
            ->count();

        $openDeals = Deal::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->count();

        $pipelineValue = (float) Deal::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->sum('value');

        $hotLeads = Lead::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('ai_score')
            ->where('ai_score', '>=', 70)
            ->count();

        $pipelineLabel = '$'.number_format($pipelineValue, 0, '.', ',');

        return [
            Stat::make(__('Active leads'), (string) $activeLeads)
                ->description(__('Not yet converted to a deal'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->chart($this->sparkLast7Days(Lead::class, $tenantId)),
            Stat::make(__('Open deals'), (string) $openDeals)
                ->description(__('Pipeline opportunities'))
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success')
                ->chart($this->sparkLast7Days(Deal::class, $tenantId)),
            Stat::make(__('Open pipeline'), $pipelineLabel)
                ->description(__('Sum of open deal values'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning')
                ->chart($this->openValueSpark($tenantId)),
            Stat::make(__('Hot leads'), (string) $hotLeads)
                ->description(__('AI score >= 70'))
                ->descriptionIcon('heroicon-m-fire')
                ->color('danger')
                ->chart($this->hotLeadSpark($tenantId)),
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function sparkLast7Days(string $modelClass, int $tenantId): array
    {
        $counts = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $counts[] = (int) $modelClass::query()
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', $day)
                ->count();
        }

        return $counts;
    }

    /**
     * @return array<int, float>
     */
    protected function openValueSpark(int $tenantId): array
    {
        $values = [];
        for ($i = 6; $i >= 0; $i--) {
            $end = now()->subDays($i)->endOfDay();
            $values[] = (float) Deal::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'open')
                ->where('created_at', '<=', $end)
                ->sum('value');
        }

        return array_map(fn (float $v): int => (int) round($v / 1000), $values);
    }

    /**
     * @return array<int, int>
     */
    protected function hotLeadSpark(int $tenantId): array
    {
        $counts = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $counts[] = (int) Lead::query()
                ->where('tenant_id', $tenantId)
                ->whereNotNull('ai_score')
                ->where('ai_score', '>=', 70)
                ->whereDate('created_at', $day)
                ->count();
        }

        return $counts;
    }
}
