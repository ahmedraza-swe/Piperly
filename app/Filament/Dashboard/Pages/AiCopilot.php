<?php

namespace App\Filament\Dashboard\Pages;

use Filament\Pages\Page;

class AiCopilot extends Page
{
    protected string $view = 'filament.dashboard.pages.ai-copilot';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('AI Copilot');
    }

    public static function getNavigationLabel(): string
    {
        return __('AI Assistant');
    }
}
