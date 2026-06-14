@php
    $ctx = $this->getBillingContext();
    $subscription = $ctx['subscription'];
    $state = $ctx['state'];
    $plans = $ctx['marketing_plans'];
    $tenant = filament()->getTenant();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Current status --}}
        <x-filament::section>
            <x-slot name="heading">{{ __('Current plan') }}</x-slot>

            @if ($state === 'trial_active')
                <div class="rounded-xl border border-primary-200 bg-primary-50/60 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-primary-700">{{ __('Free trial') }}</p>
                            <h3 class="mt-1 text-xl font-bold text-primary-950">{{ $ctx['plan']?->product?->name ?? __('Starter') }}</h3>
                            <p class="mt-2 text-sm text-primary-800">
                                @if ($ctx['days_remaining'] !== null)
                                    {{ __(':days days left in your trial.', ['days' => $ctx['days_remaining']]) }}
                                @endif
                                @if ($ctx['trial_ends_at'])
                                    {{ __('Ends :date.', ['date' => $ctx['trial_ends_at']->timezone(config('app.timezone'))->format(config('app.datetime_format', 'M j, Y g:i A'))]) }}
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @if ($this->upgradeUrl())
                                <a href="{{ $this->upgradeUrl() }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                                    {{ __('Upgrade to paid plan') }}
                                </a>
                            @endif
                            @if ($this->subscriptionDetailsUrl())
                                <a href="{{ $this->subscriptionDetailsUrl() }}" class="inline-flex items-center rounded-lg border border-primary-300 px-4 py-2 text-sm font-semibold text-primary-800 hover:bg-white">
                                    {{ __('Subscription details') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif ($state === 'paid_active')
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">{{ __('Active subscription') }}</p>
                    <h3 class="mt-1 text-xl font-bold text-gray-900">{{ $ctx['plan']?->product?->name }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ __('Your workspace is on a paid plan.') }}</p>
                    @if ($this->subscriptionDetailsUrl())
                        <a href="{{ $this->subscriptionDetailsUrl() }}" class="mt-4 inline-flex text-sm font-semibold text-primary-600 hover:text-primary-800">
                            {{ __('Manage subscription') }} →
                        </a>
                    @endif
                </div>
            @elseif ($state === 'trial_expired')
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">{{ __('Trial ended') }}</p>
                    <h3 class="mt-1 text-lg font-bold text-gray-900">{{ __('Choose a paid plan to keep your workspace') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ __('Your free trial has ended. Subscribe below to continue using your CRM.') }}</p>
                </div>
            @else
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <p class="text-sm text-gray-600">{{ __('No active subscription on this workspace yet.') }}</p>
                </div>
            @endif
        </x-filament::section>

        {{-- Plans --}}
        @if ($ctx['show_paid_plans'] || $ctx['show_trial_offers'])
            <x-filament::section>
                <x-slot name="heading">
                    @if ($state === 'trial_expired' || ! $ctx['show_trial_offers'])
                        {{ __('Choose a paid plan') }}
                    @else
                        {{ __('Available plans') }}
                    @endif
                </x-slot>
                <x-slot name="description">
                    @if ($state === 'trial_expired' || ! $ctx['show_trial_offers'])
                        {{ __('Select a plan and pay to continue after your trial.') }}
                    @else
                        {{ __('Start a free trial or subscribe now.') }}
                    @endif
                </x-slot>

                <div class="grid gap-6 md:grid-cols-2">
                    @foreach ($plans as $plan)
                        @php
                            $price = app(\App\Services\PlanService::class)->getPlanPrice($plan);
                            $isCurrent = $subscription && $subscription->plan_id === $plan->id && $state === 'trial_active';
                        @endphp
                        <div class="flex flex-col rounded-2xl border-2 border-primary-200 bg-white p-6 shadow-sm">
                            @if ($plan->product->is_popular)
                                <span class="mb-3 inline-flex w-fit rounded-full bg-primary-500 px-3 py-0.5 text-xs font-semibold text-white">{{ __('Most popular') }}</span>
                            @endif
                            <h3 class="text-lg font-bold text-gray-900">{{ $plan->product->name }}</h3>
                            @if ($price)
                                <p class="mt-2 text-3xl font-extrabold text-primary-900">
                                    @money($price->price, $price->currency->code)
                                    <span class="text-sm font-normal text-gray-500">/ {{ __($plan->interval->name) }}</span>
                                </p>
                            @endif
                            <ul class="mt-4 flex-1 space-y-2 text-sm text-gray-600">
                                @if ($plan->product->features)
                                    @foreach ($plan->product->features as $feature)
                                        <li class="flex gap-2"><span class="text-primary-500">✓</span> {{ $feature['feature'] }}</li>
                                    @endforeach
                                @endif
                            </ul>
                            <div class="mt-6 flex flex-col gap-2">
                                @if ($ctx['show_trial_offers'] && $plan->has_trial && ! $isCurrent)
                                    <a href="{{ route('checkout.trial', $plan->slug) }}" class="inline-flex justify-center rounded-full bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">
                                        {{ __('Start 7-day trial') }}
                                    </a>
                                @endif
                                @if (! $isCurrent)
                                    <a href="{{ route('checkout.subscription', $plan->slug) }}" @class([
                                        'inline-flex justify-center rounded-full px-4 py-2.5 text-sm font-semibold',
                                        'border-2 border-primary-500 text-primary-700 hover:bg-primary-50' => $ctx['show_trial_offers'],
                                        'bg-primary-600 text-white hover:bg-primary-700' => ! $ctx['show_trial_offers'],
                                    ])>
                                        {{ __('Subscribe now') }}
                                    </a>
                                @else
                                    <span class="inline-flex justify-center rounded-full bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-500">
                                        {{ __('Current plan') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
