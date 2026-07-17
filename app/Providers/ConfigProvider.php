<?php

namespace App\Providers;

use App\Services\ConfigService;
use Illuminate\Support\ServiceProvider;

class ConfigProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(ConfigService $configService): void
    {
        try {
            $configService->loadConfigs();
        } catch (\Throwable $e) {
            // We don't want to throw if the database is not yet migrated
        }
    }
}
