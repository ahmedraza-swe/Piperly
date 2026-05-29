<?php

namespace App\Filament\Dashboard\Widgets;

use App\Filament\Dashboard\Resources\Activities\ActivityResource;
use App\Models\Activity;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class TenantActivityFollowUpsWidget extends Widget
{
    protected string $view = 'filament.dashboard.widgets.tenant-activity-follow-ups-widget';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $tenant = Filament::getTenant();

        $items = Activity::query()
            ->where('tenant_id', $tenant->id)
            ->openDueWindow()
            ->orderBy('due_at')
            ->limit(10)
            ->get(['id', 'subject', 'type', 'due_at']);

        return [
            'typeLabels' => ActivityResource::typeOptions(),
            'items' => $items->map(function (Activity $activity) use ($tenant): array {
                $isOverdue = $activity->due_at && $activity->due_at->lt(today()->startOfDay());

                return [
                    'id' => $activity->id,
                    'subject' => $activity->subject,
                    'type' => $activity->type,
                    'due_at' => $activity->due_at,
                    'is_overdue' => $isOverdue,
                    'url' => ActivityResource::getUrl('view', ['record' => $activity], tenant: $tenant),
                ];
            })->all(),
        ];
    }
}
