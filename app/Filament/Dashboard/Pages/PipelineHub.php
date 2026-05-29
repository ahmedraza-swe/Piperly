<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class PipelineHub extends Page
{
    protected string $view = 'filament.dashboard.pages.pipeline';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Pipeline');
    }

    public static function getNavigationLabel(): string
    {
        return __('Deal Pipeline');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
