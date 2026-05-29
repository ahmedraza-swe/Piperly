<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Marketing & Promotions') }}</x-slot>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            {{ __('Run growth workflows from here once enabled in phases.') }}
        </p>
        <ul class="mt-3 list-disc pl-5 space-y-1 text-sm text-gray-600 dark:text-gray-300">
            <li>{{ __('Discount coupons and rules') }}</li>
            <li>{{ __('Flash sales and campaign windows') }}</li>
            <li>{{ __('Email/SMS campaign hooks') }}</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
