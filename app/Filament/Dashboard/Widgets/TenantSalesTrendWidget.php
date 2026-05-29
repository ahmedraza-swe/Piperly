<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class TenantSalesTrendWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '18rem';

    protected function getData(): array
    {
        $tenantId = Filament::getTenant()->id;
        $labels = [];
        $newLeads = [];
        $newDeals = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->translatedFormat('D');
            $day = $date->toDateString();
            $newLeads[] = Lead::query()
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', $day)
                ->count();
            $newDeals[] = Deal::query()
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', $day)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => __('New leads'),
                    'data' => $newLeads,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.18)',
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 2,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 1,
                ],
                [
                    'label' => __('New deals'),
                    'data' => $newDeals,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.16)',
                    'fill' => true,
                    'tension' => 0.35,
                    'borderWidth' => 2,
                    'pointRadius' => 3,
                    'pointHoverRadius' => 5,
                    'pointBackgroundColor' => 'rgb(16, 185, 129)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 16,
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                    'grid' => [
                        'color' => 'rgba(148, 163, 184, 0.18)',
                    ],
                    'border' => [
                        'display' => false,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'border' => [
                        'display' => false,
                    ],
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
        return __('CRM activity (last 7 days)');
    }

    public function getDescription(): ?string
    {
        return __('Leads and deals created per day for this workspace.');
    }
}
