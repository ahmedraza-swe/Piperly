<?php

namespace App\Filament\Dashboard\Pages;

use App\Constants\TenancyPermissionConstants;
use App\Filament\Dashboard\Resources\Subscriptions\SubscriptionResource;
use App\Services\TenantPermissionService;
use App\Services\WorkspaceBillingService;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class BillingAndPlans extends Page
{
    protected string $view = 'filament.dashboard.pages.billing-and-plans';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Billing & Plans');
    }

    public function getHeading(): string|Htmlable
    {
        return __('Billing & Plans');
    }

    public function getSubheading(): ?string
    {
        return __('View your trial or subscription, upgrade, or choose a paid plan.');
    }

    public static function canAccess(): bool
    {
        return app(TenantPermissionService::class)->tenantUserHasPermissionTo(
            Filament::getTenant(),
            auth()->user(),
            TenancyPermissionConstants::PERMISSION_VIEW_SUBSCRIPTIONS,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getBillingContext(): array
    {
        return app(WorkspaceBillingService::class)->getContext(
            Filament::getTenant(),
            auth()->user(),
        );
    }

    public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?\Illuminate\Database\Eloquent\Model $tenant = null): string
    {
        return parent::getUrl($parameters, $isAbsolute, $panel, $tenant);
    }

    public function subscriptionDetailsUrl(): ?string
    {
        $subscription = $this->getBillingContext()['subscription'] ?? null;

        if ($subscription === null) {
            return null;
        }

        return SubscriptionResource::getUrl('view', ['record' => $subscription->uuid]);
    }

    public function upgradeUrl(): ?string
    {
        $subscription = $this->getBillingContext()['subscription'] ?? null;

        if ($subscription === null || ! app(\App\Services\SubscriptionService::class)->isIncompleteSubscription($subscription)) {
            return null;
        }

        return route('checkout.convert-local-subscription', ['subscriptionUuid' => $subscription->uuid]);
    }
}
