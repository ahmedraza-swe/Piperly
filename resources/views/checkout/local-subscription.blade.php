<x-layouts.focus-center>

    <x-slot name="title">
        {{ __('Complete Subscription') }}
    </x-slot>

    <div class="text-center my-4 mx-4">
        <x-heading.h6 class="text-primary-500">
            {{ __('7-day free trial — no credit card required.') }}
        </x-heading.h6>
        <x-heading.h2 class="text-primary-900">
            {{ __('Start your trial') }}
        </x-heading.h2>
    </div>

    <livewire:checkout.local-subscription-checkout-form />

</x-layouts.focus-center>
