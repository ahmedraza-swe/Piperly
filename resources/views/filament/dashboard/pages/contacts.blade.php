<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Contacts & Companies') }}</x-slot>
        <x-slot name="description">{{ __('Manage contact profiles, linked companies, and communication timeline.') }}</x-slot>
        <div class="grid gap-4 md:grid-cols-3 text-sm">
            <div class="rounded-lg border p-4">Total Contacts: <strong>1,248</strong></div>
            <div class="rounded-lg border p-4">Companies: <strong>329</strong></div>
            <div class="rounded-lg border p-4">Needs Follow-up: <strong>41</strong></div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
