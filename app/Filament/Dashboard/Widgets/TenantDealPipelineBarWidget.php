<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Deal;
use App\Services\CrmPipelineService;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class TenantDealPipelineBarWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = [
        'default' => 12,
        'xl' => 6,
    ];

    protected ?string $maxHeight = '18rem';

    protected function getData(): array
    {
        $tenant = Filament::getTenant();
        $tenantId = $tenant->id;

        $stages = app(CrmPipelineService::class)->ensureDefaultStages($tenant);

        $labels = [];
        $counts = [];
        $colors = [
            'rgba(99, 102, 241, 0.85)',
            'rgba(59, 130, 246, 0.85)',
            'rgba(14, 165, 233, 0.85)',
            'rgba(20, 184, 166, 0.85)',
            'rgba(34, 197, 94, 0.85)',
            'rgba(234, 179, 8, 0.85)',
            'rgba(249, 115, 22, 0.85)',
            'rgba(244, 63, 94, 0.85)',
        ];

        foreach ($stages as $index => $stage) {
            $labels[] = $stage->name;
            $counts[] = (int) Deal::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'open')
                ->where('pipeline_stage_id', $stage->id)
                ->count();
        }

        $unassigned = (int) Deal::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->whereNull('pipeline_stage_id')
            ->count();

        if ($unassigned > 0) {
            $labels[] = __('Unassigned');
            $counts[] = $unassigned;
        }

        $barColors = [];
        foreach (array_keys($labels) as $i) {
            $barColors[] = $colors[$i % count($colors)];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Open deals'),
                    'data' => $counts,
                    'backgroundColor' => $barColors,
                    'borderColor' => array_map(fn (string $c): string => str_replace('0.85', '1', $c), $barColors),
                    'borderWidth' => 1,
                    'borderRadius' => 6,
                    'maxBarThickness' => 28,
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
            'indexAxis' => 'y',
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
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
                'y' => [
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
        return 'bar';
    }

    public function getHeading(): string
    {
        return __('Open deals by stage');
    }

    public function getDescription(): ?string
    {
        return __('Horizontal view of your open pipeline.');
    }
}
