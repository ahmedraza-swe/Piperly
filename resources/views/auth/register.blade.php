<x-layouts.focus>
    <x-slot name="left">
        <div class="flex flex-col py-2 md:p-10 gap-4 justify-center h-full items-center">
            <div class="card w-full md:max-w-xl bg-base-100 shadow-xl p-4 md:p-8">

                @if($isOtpLoginEnabled)
                    <livewire:auth.register.one-time-password-registration />
                @else
                    @include('auth.partials.traditional-registration-form')
                @endif
            </div>
        </div>
    </x-slot>


    <x-slot name="right">
        <div class="py-4 md:px-12 md:pt-36 h-full">
            <x-heading.h1 class="text-3xl! md:text-4xl! font-semibold!">
                {{ __('Register.') }}
            </x-heading.h1>
            <p class="mt-4">
                {{ __('Create your account and workspace to get started.') }}
            </p>
            @if (config('app.trial_without_payment.enabled'))
                <div class="mt-6 rounded-2xl border border-primary-200 bg-primary-50/80 p-5">
                    <p class="text-sm font-semibold text-primary-900">{{ __('Includes a 7-day free trial') }}</p>
                    <p class="mt-2 text-sm text-primary-800">
                        {{ __('No credit card required. Your trial starts as soon as you register.') }}
                    </p>
                </div>
            @endif
        </div>
    </x-slot>

</x-layouts.focus>
