<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class MarketingHub extends Page
{
    protected string $view = 'filament.dashboard.pages.marketing-hub';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Marketing');
    }

    public static function getNavigationLabel(): string
    {
        return __('Promotions & Campaigns');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
