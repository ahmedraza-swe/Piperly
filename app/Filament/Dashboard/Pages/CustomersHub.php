<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class CustomersHub extends Page
{
    protected string $view = 'filament.dashboard.pages.customers-hub';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('Customer Management');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
