<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">{{ __('Insights') }}</x-slot>
            <ul class="list-disc pl-5 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                <li>{{ __('Sales trend and revenue snapshots') }}</li>
                <li>{{ __('Top products and order health') }}</li>
                <li>{{ __('Payment and subscription performance') }}</li>
            </ul>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('Quick Access') }}</x-slot>
            <div class="space-y-2 text-sm">
                <a href="{{ route('filament.dashboard.resources.orders.index', ['tenant' => filament()->getTenant()]) }}" class="block text-primary-600">
                    {{ __('View Orders') }}
                </a>
                <a href="{{ route('filament.dashboard.resources.transactions.index', ['tenant' => filament()->getTenant()]) }}" class="block text-primary-600">
                    {{ __('View Payments') }}
                </a>
                <a href="{{ route('filament.dashboard.resources.subscriptions.index', ['tenant' => filament()->getTenant()]) }}" class="block text-primary-600">
                    {{ __('View Subscriptions') }}
                </a>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
