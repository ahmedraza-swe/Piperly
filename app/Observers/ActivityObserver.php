<?php

namespace App\Observers;

use App\Filament\Dashboard\Resources\Activities\ActivityResource;
use App\Models\Activity;
use App\Models\Lead;
use App\Services\CrmInAppNotifier;
use App\Support\CrmBell;
use App\Support\TenantResourceUrls;

class ActivityObserver
{
    public function __construct(
        private CrmInAppNotifier $notifier,
    ) {}

    public function created(Activity $activity): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (! $activity->lead_id) {
            return;
        }

        $lead = Lead::query()
            ->where('tenant_id', $activity->tenant_id)
            ->find($activity->lead_id);

        if (! $lead || ! $lead->owner_user_id) {
            return;
        }

        $owner = $lead->owner;
        if (! $owner) {
            return;
        }

        $actorId = $activity->user_id;
        if ($owner->id === $actorId) {
            return;
        }

        $url = TenantResourceUrls::activityView($activity);
        $typeLabel = ActivityResource::typeOptions()[$activity->type] ?? $activity->type;

        $detailLines = array_filter([
            __('Lead: :title', ['title' => $lead->title]),
            __('Type: :type', ['type' => $typeLabel]),
        ]);
        $detail = implode("\n", $detailLines);

        $this->notifier->notify(
            $owner,
            CrmBell::for(
                __('New activity on your lead'),
                CrmBell::twoLineBody($activity->subject, $detail),
                'info',
                'heroicon-o-calendar-days',
                [CrmBell::openAction($url, __('View activity'))],
            ),
            $actorId
        );
    }
}
