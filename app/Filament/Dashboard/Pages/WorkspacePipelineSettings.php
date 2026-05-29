<?php

namespace App\Filament\Dashboard\Pages;

use App\Constants\TenancyPermissionConstants;
use App\Services\TenantPermissionService;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class WorkspacePipelineSettings extends Page
{
    protected string $view = 'filament.dashboard.pages.workspace-pipeline-settings';

    protected static ?string $slug = 'workspace-pipeline';

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Pipeline stages');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        $tenantPermissionService = app(TenantPermissionService::class);

        return $tenantPermissionService->tenantUserHasPermissionTo(
            Filament::getTenant(),
            auth()->user(),
            TenancyPermissionConstants::PERMISSION_UPDATE_TENANT_SETTINGS,
        );
    }

    public function getHeading(): string|Htmlable
    {
        return __('Pipeline stages');
    }

    public function getTitle(): string|Htmlable
    {
        return __('Pipeline stages');
    }

    public function getSubheading(): ?string
    {
        return __('Stages appear on the deal board and when qualifying leads. You need at least one stage.');
    }
}
