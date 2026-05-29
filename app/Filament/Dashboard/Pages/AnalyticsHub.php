<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class AnalyticsHub extends Page
{
    protected string $view = 'filament.dashboard.pages.analytics-hub';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Analytics');
    }

    public static function getNavigationLabel(): string
    {
        return __('Reports & Insights');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
