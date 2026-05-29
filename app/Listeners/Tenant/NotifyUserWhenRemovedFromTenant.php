<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\UserRemovedFromTenant;
use App\Services\CrmInAppNotifier;
use App\Support\CrmBell;

class NotifyUserWhenRemovedFromTenant
{
    public function __construct(
        private CrmInAppNotifier $notifier,
    ) {}

    public function handle(UserRemovedFromTenant $event): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $this->notifier->sendIgnoringActor(
            $event->user,
            CrmBell::for(
                __('Removed from workspace'),
                CrmBell::twoLineBody(
                    __('You no longer have access to :tenant.', ['tenant' => $event->tenant->name]),
                    __('If this was unexpected, contact a workspace administrator.'),
                ),
                'danger',
                'heroicon-o-x-circle',
                [],
            ),
        );
    }
}
