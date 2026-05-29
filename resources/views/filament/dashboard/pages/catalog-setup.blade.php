<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Catalog Module') }}</x-slot>
        <p class="text-sm text-gray-600 dark:text-gray-300">
            {{ __('This area will power product, category, variant, and inventory management for tenant stores.') }}
        </p>
        <ul class="mt-3 list-disc pl-5 space-y-1 text-sm text-gray-600 dark:text-gray-300">
            <li>{{ __('Products CRUD') }}</li>
            <li>{{ __('Categories & collections') }}</li>
            <li>{{ __('Variants and SKU-level stock') }}</li>
            <li>{{ __('Search and filters') }}</li>
        </ul>
    </x-filament::section>
</x-filament-panels::page>
