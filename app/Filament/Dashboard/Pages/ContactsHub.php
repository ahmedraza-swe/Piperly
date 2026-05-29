<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class ContactsHub extends Page
{
    protected string $view = 'filament.dashboard.pages.contacts';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Contacts');
    }

    public static function getNavigationLabel(): string
    {
        return __('Contacts & Companies');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
