<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">{{ __('AI Assistant') }}</x-slot>
        <x-slot name="description">{{ __('Generate drafts, summarize calls, and score leads with AI.') }}</x-slot>
        <div class="grid gap-4 md:grid-cols-2 text-sm">
            <div class="rounded-lg border p-4">
                <h4 class="font-semibold mb-1">Email Draft</h4>
                <p>Create a professional follow-up in under 150 words from lead context.</p>
            </div>
            <div class="rounded-lg border p-4">
                <h4 class="font-semibold mb-1">Lead Score</h4>
                <p>AI confidence score using fit, intent, and engagement signals.</p>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
