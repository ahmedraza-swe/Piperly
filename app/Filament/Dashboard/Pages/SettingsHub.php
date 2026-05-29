<?php

namespace App\Filament\Dashboard\Pages;

use App\Filament\Dashboard\Support\WorkspaceSettingsLinks;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class SettingsHub extends Page
{
    protected string $view = 'filament.dashboard.pages.settings-hub';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Workspace Settings');
    }

    public function getHeading(): string|Htmlable
    {
        return __('Workspace Settings');
    }

    public function getSubheading(): ?string
    {
        return __('Manage your workspace profile, team, pipeline, and billing.');
    }

    /**
     * @return array<int, array{group: string, items: array<int, array{title: string, description: string, url: string, icon: string}>}>
     */
    public function getSettingGroups(): array
    {
        return WorkspaceSettingsLinks::grouped(
            WorkspaceSettingsLinks::tenant(),
            WorkspaceSettingsLinks::user(),
        );
    }
}
