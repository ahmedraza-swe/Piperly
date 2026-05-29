<?php

namespace App\Filament\Admin\Support;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Resources\Discounts\DiscountResource;
use App\Filament\Admin\Resources\Orders\OrderResource;
use App\Filament\Admin\Resources\Plans\PlanResource;
use App\Filament\Admin\Resources\Products\ProductResource;
use App\Filament\Admin\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Admin\Resources\Tenants\TenantResource;
use App\Filament\Admin\Resources\Transactions\TransactionResource;
use App\Filament\Admin\Resources\Users\UserResource;

/**
 * Quick links for the platform owner console (/admin).
 */
class PlatformAdminLinks
{
    /**
     * @return array<int, array{group: string, items: array<int, array{title: string, description: string, url: string, icon: string}>}>
     */
    public static function grouped(): array
    {
        return [
            [
                'group' => __('Revenue & billing'),
                'items' => [
                    self::item(
                        __('Payments received'),
                        __('All successful charges and payment records across tenants.'),
                        TransactionResource::getUrl('index'),
                        'heroicon-o-banknotes',
                    ),
                    self::item(
                        __('Subscriptions'),
                        __('Active, trialing, and canceled tenant subscriptions.'),
                        SubscriptionResource::getUrl('index'),
                        'heroicon-o-fire',
                    ),
                    self::item(
                        __('Orders'),
                        __('One-time purchases and checkout orders.'),
                        OrderResource::getUrl('index'),
                        'heroicon-o-shopping-bag',
                    ),
                    self::item(
                        __('Revenue analytics'),
                        __('MRR, churn, ARPU charts and date filters.'),
                        Dashboard::getUrl(),
                        'heroicon-o-chart-bar',
                    ),
                ],
            ],
            [
                'group' => __('Plans & catalog'),
                'items' => [
                    self::item(
                        __('Products'),
                        __('What you sell on the home page (Starter, Growth, etc.).'),
                        ProductResource::getUrl('index'),
                        'heroicon-o-cube',
                    ),
                    self::item(
                        __('Plans & pricing'),
                        __('Monthly/yearly prices, trials, and plan visibility.'),
                        PlanResource::getUrl('index'),
                        'heroicon-o-currency-dollar',
                    ),
                    self::item(
                        __('Discounts'),
                        __('Coupon codes and promotional pricing.'),
                        DiscountResource::getUrl('index'),
                        'heroicon-o-ticket',
                    ),
                ],
            ],
            [
                'group' => __('Customers'),
                'items' => [
                    self::item(
                        __('Tenants (workspaces)'),
                        __('Every customer workspace you sell to.'),
                        TenantResource::getUrl('index'),
                        'heroicon-o-building-office-2',
                    ),
                    self::item(
                        __('Users'),
                        __('All registered accounts on the platform.'),
                        UserResource::getUrl('index'),
                        'heroicon-o-users',
                    ),
                ],
            ],
        ];
    }

    /**
     * @return array{title: string, description: string, url: string, icon: string}
     */
    private static function item(string $title, string $description, string $url, string $icon): array
    {
        return compact('title', 'description', 'url', 'icon');
    }
}
