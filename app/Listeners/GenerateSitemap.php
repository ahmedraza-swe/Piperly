<?php

namespace App\Listeners;

use App\Events\SitemapChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Artisan;

class GenerateSitemap implements ShouldQueue
{
    public function handle(SitemapChanged $event): void
    {
        if (! config('app.sitemap_auto_generation_enabled', false)) {
            return;
        }

        Artisan::call('app:generate-sitemap');
    }
}
