<?php

namespace App\Filament\Dashboard\Support;

use App\Constants\TenancyPermissionConstants;
use App\Filament\Dashboard\Pages\BillingAndPlans;
use App\Filament\Dashboard\Pages\Team;
use App\Filament\Dashboard\Pages\TenantSettings;
use App\Filament\Dashboard\Pages\WorkspacePipelineSettings;
use App\Filament\Dashboard\Resources\Invitations\InvitationResource;
use App\Filament\Dashboard\Resources\Orders\OrderResource;
use App\Filament\Dashboard\Resources\Roles\RoleResource;
use App\Filament\Dashboard\Resources\Transactions\TransactionResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ConfigService;
use App\Services\TenantPermissionService;
use Filament\Facades\Filament;
use Jeffgreco13\FilamentBreezy\Pages\MyProfilePage;

/**
 * Builds permission-aware links for the workspace settings hub.
 */
class WorkspaceSettingsLinks
{
    /**
     * @return array<int, array{group: string, items: array<int, array{title: string, description: string, url: string, icon: string}>}>
     */
    public static function grouped(Tenant $tenant, User $user): array
    {
        $permissions = app(TenantPermissionService::class);
        $groups = [];

        $workspace = [];
        if (TenantSettings::canAccess()) {
            $workspace[] = self::item(
                __('Workspace profile'),
                __('Name, billing address, and organization details for invoices.'),
                TenantSettings::getUrl(panel: 'dashboard'),
                'heroicon-o-building-office-2',
            );
        }
        if (WorkspacePipelineSettings::canAccess()) {
            $workspace[] = self::item(
                __('Pipeline stages'),
                __('Customize lead and deal stages used on the board and in reports.'),
                WorkspacePipelineSettings::getUrl(panel: 'dashboard'),
                'heroicon-o-view-columns',
            );
        }
        if ($workspace !== []) {
            $groups[] = ['group' => __('Workspace'), 'items' => $workspace];
        }

        $team = [];
        if (Team::canAccess()) {
            $team[] = self::item(
                __('Team members'),
                __('View members, change roles, and remove users from this workspace.'),
                Team::getUrl(panel: 'dashboard'),
                'heroicon-o-user-group',
            );
        }
        if (InvitationResource::canAccess()) {
            $team[] = self::item(
                __('Invitations'),
                __('Invite colleagues by email and manage pending invites.'),
                InvitationResource::getUrl('index', panel: 'dashboard'),
                'heroicon-o-envelope',
            );
        }
        if (RoleResource::canAccess()) {
            $team[] = self::item(
                __('Roles & permissions'),
                __('Create workspace roles and control what each role can do.'),
                RoleResource::getUrl('index', panel: 'dashboard'),
                'heroicon-o-shield-check',
            );
        }
        if ($team !== []) {
            $groups[] = ['group' => __('Team & access'), 'items' => $team];
        }

        $billing = [];
        if ($permissions->tenantUserHasPermissionTo($tenant, $user, TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS)) {
            $billing[] = self::item(
                __('Billing & Plans'),
                __('View your trial or subscription, upgrade, or choose a paid plan.'),
                BillingAndPlans::getUrl(panel: 'dashboard'),
                'heroicon-o-credit-card',
            );
        }
        if ($permissions->tenantUserHasPermissionTo($tenant, $user, TenancyPermissionConstants::PERMISSION_VIEW_ORDERS)) {
            $billing[] = self::item(
                __('Orders'),
                __('One-time purchases and order history.'),
                OrderResource::getUrl('index', panel: 'dashboard'),
                'heroicon-o-shopping-bag',
            );
        }
        if (
            app(ConfigService::class)->get('app.customer_dashboard.show_transactions', true)
            && $permissions->tenantUserHasPermissionTo($tenant, $user, TenancyPermissionConstants::PERMISSION_VIEW_TRANSACTIONS)
        ) {
            $billing[] = self::item(
                __('Payments'),
                __('Payment and transaction records.'),
                TransactionResource::getUrl('index', panel: 'dashboard'),
                'heroicon-o-banknotes',
            );
        }
        if ($billing !== []) {
            $groups[] = ['group' => __('Billing'), 'items' => $billing];
        }

        $account = [
            self::item(
                __('My profile'),
                __('Your account, password, and personal address.'),
                MyProfilePage::getUrl(panel: 'dashboard'),
                'heroicon-o-user-circle',
            ),
        ];
        $groups[] = ['group' => __('Your account'), 'items' => $account];

        return $groups;
    }

    /**
     * @return array{title: string, description: string, url: string, icon: string}
     */
    private static function item(string $title, string $description, string $url, string $icon): array
    {
        return compact('title', 'description', 'url', 'icon');
    }

    public static function tenant(): Tenant
    {
        return Filament::getTenant();
    }

    public static function user(): User
    {
        return auth()->user();
    }
}
