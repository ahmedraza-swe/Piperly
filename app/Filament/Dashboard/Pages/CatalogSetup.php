<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class CatalogSetup extends Page
{
    protected string $view = 'filament.dashboard.pages.catalog-setup';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('Products & Categories');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
