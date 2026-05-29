<?php

namespace App\Observers;

use App\Models\CrmPipelineStage;
use App\Models\Deal;
use App\Models\User;
use App\Services\CrmInAppNotifier;
use App\Support\CrmBell;
use App\Support\TenantResourceUrls;

class DealObserver
{
    public function __construct(
        private CrmInAppNotifier $notifier,
    ) {}

    public function created(Deal $deal): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (! $deal->owner_user_id) {
            return;
        }

        $owner = User::query()->find($deal->owner_user_id);
        if (! $owner) {
            return;
        }

        $actorId = auth()->id();
        $url = TenantResourceUrls::dealView($deal);

        $secondary = collect([
            $deal->company_name ? __('Company: :name', ['name' => $deal->company_name]) : null,
            $deal->value !== null ? __('Value: :v', ['v' => '$'.number_format((float) $deal->value, 0, '.', ',')]) : null,
        ])->filter()->implode(' · ');

        $this->notifier->notify(
            $owner,
            CrmBell::for(
                __('New deal assigned to you'),
                CrmBell::twoLineBody(
                    $deal->title,
                    $secondary !== '' ? $secondary : __('Open the record to update stage and next steps.'),
                ),
                'info',
                'heroicon-o-briefcase',
                [CrmBell::openAction($url, __('View deal'))],
            ),
            $actorId
        );
    }

    public function updated(Deal $deal): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $actorId = auth()->id();

        if ($deal->wasChanged('owner_user_id') && $deal->owner_user_id) {
            $owner = User::query()->find($deal->owner_user_id);
            if ($owner) {
                $url = TenantResourceUrls::dealView($deal);
                $this->notifier->notify(
                    $owner,
                    CrmBell::for(
                        __('Deal reassigned to you'),
                        CrmBell::twoLineBody(
                            $deal->title,
                            __('You are now responsible for this opportunity.'),
                        ),
                        'warning',
                        'heroicon-o-user-group',
                        [CrmBell::openAction($url, __('View deal'))],
                    ),
                    $actorId
                );
            }
        }

        if ($deal->wasChanged('status') && in_array($deal->status, ['won', 'lost'], true)) {
            $owner = $deal->owner_user_id ? User::query()->find($deal->owner_user_id) : null;
            if ($owner) {
                $status = $deal->status;
                $url = TenantResourceUrls::dealView($deal);
                $title = $status === 'won' ? __('Deal won') : __('Deal lost');
                $tone = $status === 'won' ? 'success' : 'danger';
                $icon = $status === 'won' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle';

                $this->notifier->notify(
                    $owner,
                    CrmBell::for(
                        $title,
                        CrmBell::twoLineBody(
                            $deal->title,
                            $deal->company_name ? __('Company: :name', ['name' => $deal->company_name]) : null,
                        ),
                        $tone,
                        $icon,
                        [CrmBell::openAction($url, __('View deal'))],
                    ),
                    $actorId
                );
            }
        }

        if ($deal->wasChanged('pipeline_stage_id') && $deal->status === 'open' && ! $deal->wasChanged('status')) {
            $owner = $deal->owner_user_id ? User::query()->find($deal->owner_user_id) : null;
            if ($owner) {
                $stageName = $deal->pipelineStage?->name ?? CrmPipelineStage::query()
                    ->whereKey($deal->pipeline_stage_id)
                    ->value('name');

                $url = TenantResourceUrls::dealView($deal);

                $this->notifier->notify(
                    $owner,
                    CrmBell::for(
                        __('Pipeline stage updated'),
                        CrmBell::twoLineBody(
                            $deal->title,
                            __('Now in stage: :stage', ['stage' => $stageName ?? __('Unknown stage')]),
                        ),
                        'info',
                        'heroicon-o-arrows-right-left',
                        [CrmBell::openAction($url, __('View deal'))],
                    ),
                    $actorId
                );
            }
        }
    }
}
