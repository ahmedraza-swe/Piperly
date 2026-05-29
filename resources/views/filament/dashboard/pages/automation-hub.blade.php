<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('Rules & Workflows') }}</x-slot>
        <x-slot name="description">{{ __('Automate assignment, reminders, and stage transitions based on events.') }}</x-slot>
        <div class="space-y-2 text-sm">
            <div class="rounded-lg border p-3">If lead source is website and score &gt; 70 -> assign to SDR Team</div>
            <div class="rounded-lg border p-3">If deal is inactive for 3 days -> create follow-up task</div>
            <div class="rounded-lg border p-3">If stage changed to proposal -> notify account owner</div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
