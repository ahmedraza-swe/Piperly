<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Deal Pipeline') }}</x-slot>
        <x-slot name="description">{{ __('Track opportunities across stages and monitor weighted forecast.') }}</x-slot>
        <div class="grid gap-4 md:grid-cols-4 text-sm">
            <div class="rounded-lg border p-4">Prospecting: <strong>17</strong></div>
            <div class="rounded-lg border p-4">Qualified: <strong>11</strong></div>
            <div class="rounded-lg border p-4">Proposal: <strong>8</strong></div>
            <div class="rounded-lg border p-4">Negotiation: <strong>4</strong></div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
