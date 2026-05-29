<x-filament-panels::page>
    <div class="mb-4">
        <x-filament::link
            :href="\App\Filament\Dashboard\Pages\SettingsHub::getUrl(panel: 'dashboard')"
            icon="heroicon-m-arrow-left"
            color="gray"
        >
            {{ __('Back to workspace settings') }}
        </x-filament::link>
    </div>

    @livewire('filament.dashboard.pipeline-stages-settings')
</x-filament-panels::page>
