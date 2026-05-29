<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Support\PlatformAdminLinks;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class PlatformHub extends Page
{
    protected static ?string $slug = 'platform';

    protected string $view = 'filament.admin.pages.platform-hub';

    protected static ?int $navigationSort = -2;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    public static function getNavigationLabel(): string
    {
        return __('Platform console');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function getHeading(): string|Htmlable
    {
        return __('Platform console');
    }

    public function getSubheading(): ?string
    {
        return __('You are the system owner. Manage plans, tenants, subscriptions, and payments here—not inside a customer CRM workspace.');
    }

    /**
     * @return array<int, array{group: string, items: array<int, array{title: string, description: string, url: string, icon: string}>}>
     */
    public function getLinkGroups(): array
    {
        return PlatformAdminLinks::grouped();
    }
}
