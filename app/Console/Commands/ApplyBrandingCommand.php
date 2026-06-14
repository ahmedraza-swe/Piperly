<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Services\ConfigService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ApplyBrandingCommand extends Command
{
    protected $signature = 'platform:apply-branding';

    protected $description = 'Apply Piperly app name and description to admin settings and cache';

    public function handle(ConfigService $configService): int
    {
        $name = (string) config('app.name', 'Piperly');
        $description = (string) config('app.description', '');

        if (config('app.admin_settings.enabled')) {
            Config::set('app.name', $name);

            if ($description !== '') {
                Config::set('app.description', $description);
            }

            Cache::forever('app.name', $name);

            if ($description !== '') {
                Cache::forever('app.description', $description);
            }

            $this->info("Updated admin settings: app.name = {$name}");
        } else {
            $this->warn('ADMIN_SETTINGS_ENABLED is false — using .env values only.');
        }

        $this->line('');
        $this->line('  App name:        '.$name);
        $this->line('  App description: '.$description);

        return self::SUCCESS;
    }
}
