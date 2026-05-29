<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class LeadsHub extends Page
{
    protected string $view = 'filament.dashboard.pages.leads';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Leads');
    }

    public static function getNavigationLabel(): string
    {
        return __('Lead Management');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
