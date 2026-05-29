<?php

namespace App\Observers;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\User;
use App\Services\CrmInAppNotifier;
use App\Support\CrmBell;
use App\Support\TenantResourceUrls;

class LeadObserver
{
    public function __construct(
        private CrmInAppNotifier $notifier,
    ) {}

    public function created(Lead $lead): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (! $lead->owner_user_id) {
            return;
        }

        $owner = User::query()->find($lead->owner_user_id);
        if (! $owner) {
            return;
        }

        $actorId = auth()->id();
        $url = TenantResourceUrls::leadView($lead);

        $secondary = collect([
            $lead->company_name ? __('Company: :name', ['name' => $lead->company_name]) : null,
            $lead->source ? __('Source: :src', ['src' => $lead->source]) : null,
        ])->filter()->implode(' · ');

        $this->notifier->notify(
            $owner,
            CrmBell::for(
                __('New lead assigned to you'),
                CrmBell::twoLineBody(
                    $lead->title,
                    $secondary !== '' ? $secondary : __('You are the owner — review details and plan next steps.'),
                ),
                'info',
                'heroicon-o-user-plus',
                [CrmBell::openAction($url, __('View lead'))],
            ),
            $actorId
        );
    }

    public function updated(Lead $lead): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $actorId = auth()->id();

        if ($lead->wasChanged('owner_user_id') && $lead->owner_user_id) {
            $owner = User::query()->find($lead->owner_user_id);
            if ($owner) {
                $url = TenantResourceUrls::leadView($lead);
                $this->notifier->notify(
                    $owner,
                    CrmBell::for(
                        __('Lead reassigned to you'),
                        CrmBell::twoLineBody(
                            $lead->title,
                            $lead->company_name ? __('Company: :name', ['name' => $lead->company_name]) : null,
                        ),
                        'warning',
                        'heroicon-o-arrow-path',
                        [CrmBell::openAction($url, __('View lead'))],
                    ),
                    $actorId
                );
            }
        }

        if ($lead->wasChanged('converted_at') && $lead->converted_at) {
            $owner = $lead->owner_user_id ? User::query()->find($lead->owner_user_id) : null;
            if ($owner) {
                $deal = Deal::query()
                    ->where('tenant_id', $lead->tenant_id)
                    ->where('lead_id', $lead->id)
                    ->first();

                $actions = [CrmBell::openAction(TenantResourceUrls::leadView($lead), __('View lead'), 'gray')];
                if ($deal) {
                    array_unshift($actions, CrmBell::openAction(TenantResourceUrls::dealView($deal), __('View deal')));
                }

                $this->notifier->notify(
                    $owner,
                    CrmBell::for(
                        __('Lead converted'),
                        CrmBell::twoLineBody(
                            $lead->title,
                            __('This lead is marked converted. Continue in the deal record if one was created.'),
                        ),
                        'success',
                        'heroicon-o-check-badge',
                        $actions,
                    ),
                    $actorId
                );
            }
        }

        if ($lead->wasChanged('ai_score') && $lead->ai_score !== null && $lead->ai_score >= 70) {
            $prev = (int) $lead->getOriginal('ai_score');
            if ($prev < 70) {
                $owner = $lead->owner_user_id ? User::query()->find($lead->owner_user_id) : null;
                if ($owner) {
                    $this->notifier->notify(
                        $owner,
                        CrmBell::for(
                            __('Hot lead'),
                            CrmBell::twoLineBody(
                                $lead->title,
                                __('AI score reached :score — prioritize follow-up.', ['score' => (string) $lead->ai_score]),
                            ),
                            'danger',
                            'heroicon-o-fire',
                            [CrmBell::openAction(TenantResourceUrls::leadView($lead), __('View lead'))],
                        ),
                        $actorId
                    );
                }
            }
        }
    }
}
