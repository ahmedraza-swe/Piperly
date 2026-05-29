<?php

namespace App\Console\Commands;

use App\Jobs\VerifyQueueJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class VerifyStackCommand extends Command
{
    protected $signature = 'stack:verify {--queue : Dispatch a test job and wait for the worker}';

    protected $description = 'Verify Redis connectivity and optional queue processing';

    public function handle(): int
    {
        $this->components->info('Checking Redis…');

        if (config('database.redis.client') === 'phpredis' && ! extension_loaded('redis')) {
            $this->components->error('PHP ext-redis is not loaded on this PHP binary.');
            $this->line('  Laragon: enable Redis extension in PHP, or use Docker Sail (see docs/local-development.md).');

            return self::FAILURE;
        }

        try {
            $pong = Redis::connection()->ping();
            $this->components->twoColumnDetail('Redis ping', is_string($pong) ? $pong : 'PONG');
        } catch (\Throwable $exception) {
            $this->components->error('Redis failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        if (! $this->option('queue')) {
            $this->components->info('Redis OK. Run with --queue to test job processing (requires Horizon or queue:work).');

            return self::SUCCESS;
        }

        if (config('queue.default') === 'sync') {
            $this->components->warn('QUEUE_CONNECTION=sync — jobs run inline. Set QUEUE_CONNECTION=redis for a real queue test.');

            return self::SUCCESS;
        }

        $token = Str::uuid()->toString();
        VerifyQueueJob::dispatch($token);

        $this->components->info('Dispatched VerifyQueueJob. Waiting up to 15s for a worker…');

        $deadline = now()->addSeconds(15);

        while (now()->lt($deadline)) {
            if (Cache::get('stack:verify:queue:'.$token)) {
                Cache::forget('stack:verify:queue:'.$token);
                $this->components->info('Queue worker processed the test job successfully.');

                return self::SUCCESS;
            }

            usleep(500_000);
        }

        $this->components->error('Timed out waiting for queue worker. Start Horizon or `php artisan queue:work`.');

        return self::FAILURE;
    }
}
