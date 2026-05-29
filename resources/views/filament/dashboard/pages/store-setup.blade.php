<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2">
        <x-filament::section>
            <x-slot name="heading">{{ __('Company Workspace') }}</x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-300">
                {{ __('Manage your workspace settings, subscription status, and payment history from one place.') }}
            </p>
            <div class="mt-3 text-sm">
                <a href="{{ route('filament.dashboard.pages.tenant-settings', ['tenant' => filament()->getTenant()]) }}" class="text-primary-600">
                    {{ __('Open Workspace Settings') }}
                </a>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('What You Can Do Now') }}</x-slot>
            <ul class="list-disc pl-5 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                <li>{{ __('Manage subscriptions and billing.') }}</li>
                <li>{{ __('Track incoming orders and payments.') }}</li>
                <li>{{ __('Invite team members and assign roles.') }}</li>
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
