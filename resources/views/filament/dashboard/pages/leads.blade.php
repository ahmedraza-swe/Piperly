<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Lead Management') }}</x-slot>
        <x-slot name="description">{{ __('Capture, qualify, and assign leads with source tracking and tags.') }}</x-slot>
        <div class="grid gap-4 md:grid-cols-3 text-sm">
            <div class="rounded-lg border p-4">New Leads Today: <strong>24</strong></div>
            <div class="rounded-lg border p-4">Qualified Leads: <strong>13</strong></div>
            <div class="rounded-lg border p-4">Unassigned Leads: <strong>5</strong></div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
