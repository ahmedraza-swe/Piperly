<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\UserJoinedTenant;
use App\Filament\Dashboard\Pages\TenantDashboard;
use App\Models\User;
use App\Services\CrmInAppNotifier;
use App\Support\CrmBell;

class NotifyInviterWhenUserJoinedTenant
{
    public function __construct(
        private CrmInAppNotifier $notifier,
    ) {}

    public function handle(UserJoinedTenant $event): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if (! $event->inviterUserId) {
            return;
        }

        $inviter = User::query()->find($event->inviterUserId);
        if (! $inviter || $inviter->is($event->user)) {
            return;
        }

        $tenantName = $event->tenant->name;
        $joinerName = $event->user->name;

        $dashboardUrl = TenantDashboard::getUrl(['tenant' => $event->tenant], panel: 'dashboard');

        $this->notifier->notify(
            $inviter,
            CrmBell::for(
                __('Invitation accepted'),
                CrmBell::twoLineBody(
                    __(':name is now part of your workspace.', ['name' => $joinerName]),
                    __('Workspace: :tenant', ['tenant' => $tenantName]),
                ),
                'success',
                'heroicon-o-user-plus',
                [CrmBell::openAction($dashboardUrl, __('Open workspace'))],
            ),
            $event->user->id
        );
    }
}
