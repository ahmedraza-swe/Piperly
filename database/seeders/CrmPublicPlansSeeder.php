<?php

namespace Database\Seeders;

use App\Constants\PlanType;
use App\Models\Currency;
use App\Models\Interval;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Two subscription products for the public home page (trial + subscribe).
 */
class CrmPublicPlansSeeder extends Seeder
{
    public function run(): void
    {
        $this->callOnce([
            CurrenciesSeeder::class,
            IntervalsSeeder::class,
        ]);

        $currencyId = Currency::query()->where('code', config('app.default_currency', 'USD'))->first()?->id
            ?? Currency::query()->value('id');

        if (! $currencyId) {
            $this->command?->warn('No currency found. Run CurrenciesSeeder first.');

            return;
        }

        $monthIntervalId = Interval::query()->where('slug', 'month')->value('id');
        $dayIntervalId = Interval::query()->where('slug', 'day')->value('id');

        $catalog = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'For small teams getting started with CRM.',
                'is_popular' => false,
                'monthly_cents' => 2900,
                'trial_days' => 7,
                'features' => [
                    ['feature' => 'Leads, contacts & deals'],
                    ['feature' => 'Deal board & activities'],
                    ['feature' => 'Team workspace (up to 3 users)'],
                    ['feature' => '7-day free trial'],
                ],
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For growing sales teams that need automation and reports.',
                'is_popular' => true,
                'monthly_cents' => 7900,
                'trial_days' => 7,
                'features' => [
                    ['feature' => 'Everything in Starter'],
                    ['feature' => 'Advanced reports & exports'],
                    ['feature' => 'Automation rules'],
                    ['feature' => 'AI assistant (when enabled)'],
                    ['feature' => '7-day free trial'],
                ],
            ],
        ];

        foreach ($catalog as $item) {
            $product = Product::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'is_popular' => $item['is_popular'],
                    'features' => $item['features'],
                ],
            );

            $plan = Plan::query()->updateOrCreate(
                ['slug' => $item['slug'].'-monthly'],
                [
                    'name' => $item['name'].' Monthly',
                    'product_id' => $product->id,
                    'interval_id' => $monthIntervalId,
                    'interval_count' => 1,
                    'type' => PlanType::FLAT_RATE->value,
                    'is_active' => true,
                    'is_visible' => true,
                    'has_trial' => true,
                    'trial_interval_id' => $dayIntervalId,
                    'trial_interval_count' => $item['trial_days'],
                ],
            );

            $plan->prices()->updateOrCreate(
                ['currency_id' => $currencyId],
                ['price' => $item['monthly_cents']],
            );
        }

        $this->command?->info('Public CRM plans seeded: starter, growth (monthly + 7-day trial).');
    }
}
