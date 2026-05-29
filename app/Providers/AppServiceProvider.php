<?php

namespace App\Providers;

use App\Events\Tenant\TenantCreated;
use App\Events\Tenant\UserJoinedTenant;
use App\Events\Tenant\UserRemovedFromTenant;
use App\Listeners\Tenant\BootstrapTenantCrmDefaults;
use App\Listeners\Tenant\NotifyInviterWhenUserJoinedTenant;
use App\Listeners\Tenant\NotifyUserWhenRemovedFromTenant;
use App\Models\Activity;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Observers\ActivityObserver;
use App\Observers\ContactObserver;
use App\Observers\DealObserver;
use App\Observers\LeadObserver;
use App\Listeners\User\CreateTenantIfNeeded;
use App\Services\PaymentProviders\LemonSqueezy\LemonSqueezyProvider;
use App\Services\PaymentProviders\Offline\OfflineProvider;
use App\Services\PaymentProviders\Paddle\PaddleProvider;
use App\Services\PaymentProviders\PaymentService;
use App\Services\PaymentProviders\Stripe\StripeProvider;
use App\Services\UserVerificationService;
use App\Services\VerificationProviders\TwilioProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && (bool) config('telescope.enabled')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        // payment providers
        $this->app->tag([
            StripeProvider::class,
            PaddleProvider::class,
            LemonSqueezyProvider::class,
            OfflineProvider::class,
        ], 'payment-providers');

        $this->app->bind(PaymentService::class, function () {
            return new PaymentService(...$this->app->tagged('payment-providers'));
        });

        // verification providers
        $this->app->tag([
            TwilioProvider::class,
        ], 'verification-providers');

        $this->app->afterResolving(UserVerificationService::class, function (UserVerificationService $service) {
            $service->setVerificationProviders(...$this->app->tagged('verification-providers'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureViteUsesCompiledAssets();

        Event::listen(Registered::class, CreateTenantIfNeeded::class);
        Event::listen(TenantCreated::class, BootstrapTenantCrmDefaults::class);
        Event::listen(UserJoinedTenant::class, NotifyInviterWhenUserJoinedTenant::class);
        Event::listen(UserRemovedFromTenant::class, NotifyUserWhenRemovedFromTenant::class);

        Lead::observe(LeadObserver::class);
        Deal::observe(DealObserver::class);
        Activity::observe(ActivityObserver::class);
        Contact::observe(ContactObserver::class);

        static $intlMissingLogged = false;
        if (! extension_loaded('intl') && ! $intlMissingLogged) {
            $intlMissingLogged = true;
            Log::warning('PHP intl extension is not loaded. Number/currency formatting uses project fallbacks (see docs/php-runtime-notes.md). Enable ext-intl only in this project\'s PHP binary if you need full locale rules.', [
                'php_version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
            ]);
        }

        FilamentAsset::register([
            Js::make('components-script', __DIR__.'/../../resources/js/components.js'),
        ]);
    }

    /**
     * Stale public/hot makes Laravel load CSS/JS from the Vite dev server. If npm run dev
     * is not running, pages look unstyled (often noticed right after login). Use compiled
     * assets unless VITE_USE_DEV_SERVER=true in .env.
     */
    private function ensureViteUsesCompiledAssets(): void
    {
        $hotPath = public_path('hot');

        if (file_exists($hotPath) && ! filter_var(env('VITE_USE_DEV_SERVER', false), FILTER_VALIDATE_BOOL)) {
            @unlink($hotPath);
        }

        if (! file_exists(public_path('build/manifest.json'))) {
            Log::warning('Frontend assets missing. Run: npm install && npm run build', [
                'manifest' => public_path('build/manifest.json'),
            ]);
        }
    }
}
