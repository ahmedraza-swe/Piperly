<?php

namespace App\Observers;

use App\Models\Contact;
use App\Models\Lead;
use App\Services\CrmInAppNotifier;
use App\Support\CrmBell;
use App\Support\TenantResourceUrls;

class ContactObserver
{
    public function __construct(
        private CrmInAppNotifier $notifier,
    ) {}

    public function created(Contact $contact): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (! $contact->lead_id) {
            return;
        }

        $lead = Lead::query()
            ->where('tenant_id', $contact->tenant_id)
            ->find($contact->lead_id);

        if (! $lead || ! $lead->owner_user_id) {
            return;
        }

        $owner = $lead->owner;
        if (! $owner) {
            return;
        }

        $actorId = auth()->id();

        $nameLine = $contact->full_name;
        if ($contact->email) {
            $nameLine .= ' · '.$contact->email;
        }

        $this->notifier->notify(
            $owner,
            CrmBell::for(
                __('New contact on your lead'),
                CrmBell::twoLineBody(
                    $nameLine,
                    __('Linked to lead: :title', ['title' => $lead->title]),
                ),
                'success',
                'heroicon-o-user-plus',
                [
                    CrmBell::openAction(TenantResourceUrls::contactView($contact), __('View contact')),
                    CrmBell::secondaryButton('lead', TenantResourceUrls::leadView($lead), __('View lead')),
                ],
            ),
            $actorId
        );
    }
}
