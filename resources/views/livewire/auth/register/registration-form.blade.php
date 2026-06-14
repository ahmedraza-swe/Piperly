<div>

    <p class="text-xs mt-2 text-end">{{__('Have an account?')}} <a class="text-primary-500 font-bold" href="{{ route('login') }}">{{__('Login')}}</a></p>

    <form wire:submit="register" class="mt-4 space-y-6">
        <div>
            <x-input.field label="{{ __('Name') }}" type="text"
                   wire:model="name" required
                   value="{{ old('name') }}" required autofocus="true"
                   autocomplete="name" max-width="w-full"/>

            @error('name')
                <span class="text-xs text-red-500" role="alert">
                    {{ $message }}
                </span>
            @enderror


            <x-input.field label="{{ __('Email Address') }}" type="email"
                   wire:model="email" required
                   value="{{ old('email') }}" required
                   autocomplete="email" max-width="w-full"/>

            @error('email')
                <span class="text-xs text-red-500" role="alert">
                    {{ $message }}
                </span>
            @enderror

            <x-input.field label="{{ __('Company / Workspace Name') }}" type="text"
                   wire:model="company_name" required
                   value="{{ old('company_name') }}" max-width="w-full"/>

            @error('company_name')
                <span class="text-xs text-red-500" role="alert">
                    {{ $message }}
                </span>
            @enderror

            <p class="mt-2 text-sm text-gray-600">
                {{ __('We will send you a one-time login code to the email address provided.') }}
            </p>

            @if (config('app.trial_without_payment.enabled'))
                <p class="mt-3 rounded-xl border border-primary-200 bg-primary-50/70 px-4 py-3 text-sm text-primary-900">
                    {{ __('Your 7-day free trial starts when you create your account. No credit card required.') }}
                </p>
            @endif

        </div>

        <div>
            <x-button-link.primary class="inline-block w-full! my-2" elementType="button" type="submit">
                @if (config('app.trial_without_payment.enabled'))
                    {{ __('Create account & start free trial') }}
                @else
                    {{ __('Register') }}
                @endif
            </x-button-link.primary>
        </div>
    </form>
</div>
