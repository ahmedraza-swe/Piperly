<?php

namespace App\Filament\Dashboard\Widgets;

use App\Models\Lead;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class TenantLeadStatusDoughnutWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = [
        'default' => 12,
        'xl' => 6,
    ];

    protected ?string $maxHeight = '18rem';

    /**
     * @var array<string, string>
     */
    protected array $statusPalette = [
        'new' => 'rgb(59, 130, 246)',
        'contacted' => 'rgb(99, 102, 241)',
        'qualified' => 'rgb(16, 185, 129)',
        'nurturing' => 'rgb(234, 179, 8)',
        'disqualified' => 'rgb(148, 163, 184)',
    ];

    protected function getData(): array
    {
        $tenantId = Filament::getTenant()->id;

        $statusOrder = ['new', 'contacted', 'qualified', 'nurturing', 'disqualified'];
        $labelMap = [
            'new' => __('New'),
            'contacted' => __('Contacted'),
            'qualified' => __('Qualified'),
            'nurturing' => __('Nurturing'),
            'disqualified' => __('Disqualified'),
        ];

        $raw = Lead::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('converted_at')
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statusOrder as $status) {
            $count = (int) ($raw[$status] ?? 0);
            if ($count === 0) {
                continue;
            }
            $labels[] = $labelMap[$status] ?? $status;
            $data[] = $count;
            $colors[] = $this->statusPalette[$status] ?? 'rgb(148, 163, 184)';
        }

        if ($labels === []) {
            return [
                'labels' => [__('No active leads')],
                'datasets' => [
                    [
                        'label' => __('Leads'),
                        'data' => [1],
                        'backgroundColor' => ['rgba(226, 232, 240, 0.9)'],
                        'borderColor' => ['rgba(148, 163, 184, 0.6)'],
                        'borderWidth' => 1,
                    ],
                ],
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Active leads'),
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'hoverOffset' => 6,
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
            'cutout' => '62%',
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 12,
                        'boxWidth' => 8,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public function getHeading(): string
    {
        return __('Leads by status');
    }

    public function getDescription(): ?string
    {
        return __('Active leads only (not converted).');
    }
}
