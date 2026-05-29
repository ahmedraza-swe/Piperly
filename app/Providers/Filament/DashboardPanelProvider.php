<?php

namespace App\Providers\Filament;

use App\Constants\TenancyPermissionConstants;
use App\Filament\Dashboard\Pages\SettingsHub;
use App\Filament\Dashboard\Pages\TenantSettings;
use App\Http\Middleware\UpdateUserLastSeenAt;
use App\Livewire\AddressForm;
use App\Models\Tenant;
use App\Services\TenantPermissionService;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use App\Filament\Dashboard\Pages\TenantDashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Dashboard\Widgets\TenantActivityFollowUpsWidget;
use App\Filament\Dashboard\Widgets\TenantDashboardWorkspaceWidget;
use App\Filament\Dashboard\Widgets\TenantDealPipelineBarWidget;
use App\Filament\Dashboard\Widgets\TenantKpiOverviewWidget;
use App\Filament\Dashboard\Widgets\TenantLeadStatusDoughnutWidget;
use App\Filament\Dashboard\Widgets\TenantSalesTrendWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('dashboard')
            ->path('dashboard')
            ->brandName(fn () => config('app.name').' · '.__('CRM'))
            ->colors([
                'primary' => Color::Teal,
            ])
            ->userMenuItems([
                Action::make('platform-console')
                    ->label(__('Platform console'))
                    ->visible(
                        fn () => auth()->user()->isAdmin()
                    )
                    ->url(fn () => route('filament.admin.pages.platform'))
                    ->icon('heroicon-s-server-stack'),
                Action::make('workspace-settings')
                    ->label(__('Workspace Settings'))
                    ->visible(
                        function () {
                            $tenantPermissionService = app(TenantPermissionService::class);

                            return $tenantPermissionService->tenantUserHasPermissionTo(
                                Filament::getTenant(),
                                auth()->user(),
                                TenancyPermissionConstants::PERMISSION_UPDATE_TENANT_SETTINGS
                            );
                        }
                    )
                    ->icon('heroicon-s-cog-8-tooth')
                    ->url(fn () => SettingsHub::getUrl()),
            ])
            ->discoverResources(in: app_path('Filament/Dashboard/Resources'), for: 'App\\Filament\\Dashboard\\Resources')
            ->discoverPages(in: app_path('Filament/Dashboard/Pages'), for: 'App\\Filament\\Dashboard\\Pages')
            ->pages([
                TenantDashboard::class,
            ])
            ->viteTheme('resources/css/filament/dashboard/theme.css')
            ->discoverWidgets(in: app_path('Filament/Dashboard/Widgets'), for: 'App\\Filament\\Dashboard\\Widgets')
            ->widgets([
                TenantKpiOverviewWidget::class,
                TenantSalesTrendWidget::class,
                TenantDealPipelineBarWidget::class,
                TenantLeadStatusDoughnutWidget::class,
                TenantActivityFollowUpsWidget::class,
                TenantDashboardWorkspaceWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                UpdateUserLastSeenAt::class,
            ])
            ->renderHook('panels::head.start', function () {
                return view('components.layouts.partials.analytics');
            })
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(__('Dashboard'))
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(__('Leads'))
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(__('Pipeline'))
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(__('Contacts'))
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(__('Activities'))
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(__('Automation'))
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(__('AI Copilot'))
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(__('Reports'))
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(__('Settings'))
                    ->collapsed(),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugins([
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
                        shouldRegisterNavigation: false, // Adds a main navigation item for the My Profile page (default = false)
                        hasAvatars: false, // Enables the avatar upload form component (default = false)
                        slug: 'my-profile' // Sets the slug for the profile page (default = 'my-profile')
                    )
                    ->myProfileComponents([
                        AddressForm::class,
                    ]),
            ])
            ->tenantMenu()
            ->tenant(Tenant::class, 'uuid')
            ->databaseNotifications();
    }
}
