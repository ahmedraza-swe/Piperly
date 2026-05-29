<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Customer Management') }}</x-slot>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            {{ __('Customer profiles and support workflows will be managed from this section.') }}
        </p>
        <ul class="mt-3 list-disc pl-5 space-y-1 text-sm text-gray-600 dark:text-gray-300">
            <li>{{ __('Customer list with order history') }}</li>
            <li>{{ __('Address and contact details') }}</li>
            <li>{{ __('Tags, notes, and support context') }}</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
