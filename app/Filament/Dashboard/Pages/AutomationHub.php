<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class AutomationHub extends Page
{
    protected string $view = 'filament.dashboard.pages.automation-hub';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Automation');
    }

    public static function getNavigationLabel(): string
    {
        return __('Rules & Workflows');
    }
}
