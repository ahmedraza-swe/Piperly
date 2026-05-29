<?php

namespace App\Console\Commands;

use Database\Seeders\PlatformOwnerSeeder;
use Illuminate\Console\Command;

class EnsurePlatformOwnerCommand extends Command
{
    protected $signature = 'platform:ensure-owner';

    protected $description = 'Create or update the platform owner admin account from .env (PLATFORM_OWNER_*)';

    public function handle(): int
    {
        $this->call('db:seed', ['--class' => PlatformOwnerSeeder::class, '--force' => true]);

        $this->line('');
        $this->line('  Admin panel: '.url('/admin'));
        $this->line('  Email:       '.config('platform.owner_email'));
        $this->line('  Password:    (value of PLATFORM_OWNER_PASSWORD in .env)');

        return self::SUCCESS;
    }
}
