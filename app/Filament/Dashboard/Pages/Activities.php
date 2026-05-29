<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class Activities extends Page
{
    protected string $view = 'filament.dashboard.pages.activities';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Activities');
    }

    public static function getNavigationLabel(): string
    {
        return __('Calls, Meetings & Tasks');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
