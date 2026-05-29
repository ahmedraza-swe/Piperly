<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Deal;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class ReportsDealOutcomeTrendWidget extends ChartWidget
{
    public static function canView(): bool
    {
        return ! request()->routeIs('filament.dashboard.pages.tenant-dashboard');
    }

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '18rem';

    protected function getData(): array
    {
        $tenantId = Filament::getTenant()->id;
        $labels = [];
        $won = [];
        $lost = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M');
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $won[] = (int) Deal::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'won')
                ->whereBetween('closed_at', [$start, $end])
                ->count();

            $lost[] = (int) Deal::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'lost')
                ->whereBetween('closed_at', [$start, $end])
                ->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Won deals'),
                    'data' => $won,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 2,
                ],
                [
                    'label' => __('Lost deals'),
                    'data' => $lost,
                    'backgroundColor' => 'rgba(244, 63, 94, 0.15)',
                    'borderColor' => 'rgb(244, 63, 94)',
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string
    {
        return __('Deal outcomes (last 6 months)');
    }

    public function getDescription(): ?string
    {
        return __('Monthly won vs lost closed deals.');
    }
}
