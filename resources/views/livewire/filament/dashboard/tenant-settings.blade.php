<div>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="flex gap-4 pt-4">
            <x-filament::button type="submit">
                <x-filament::loading-indicator class="inline h-5 w-5" wire:loading />
                {{ __('Save Changes') }}
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</div>
