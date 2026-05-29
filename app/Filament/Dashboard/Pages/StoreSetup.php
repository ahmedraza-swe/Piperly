<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class StoreSetup extends Page
{
    protected string $view = 'filament.dashboard.pages.store-setup';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Store Setup');
    }

    public static function getNavigationLabel(): string
    {
        return __('Workspace Overview');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
