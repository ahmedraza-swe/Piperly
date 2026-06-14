<form method="POST" action="{{ route('register') }}">
    @csrf

    <p class="text-xs mt-2 text-end">{{__('Have an account?')}} <a class="text-primary-500 font-bold" href="{{ route('login') }}">{{__('Login')}}</a></p>

    <x-input.field label="{{ __('Name') }}" type="text" name="name"
                   value="{{ old('name') }}" required autofocus="true"
                   autocomplete="name" max-width="w-full"/>

    @error('name')
        <span class="text-xs text-red-500" role="alert">
            {{ $message }}
        </span>
    @enderror

    <x-input.field label="{{ __('Email Address') }}" type="email" name="email"
                   value="{{ old('email') }}" required
                   autocomplete="email" max-width="w-full"/>
    @error('email')
        <span class="text-xs text-red-500" role="alert">
            {{ $message }}
        </span>
    @enderror

    <x-input.field label="{{ __('Company / Workspace Name') }}" type="text" name="company_name"
                   value="{{ old('company_name') }}" required max-width="w-full"/>
    @error('company_name')
        <span class="text-xs text-red-500" role="alert">
            {{ $message }}
        </span>
    @enderror

    <x-input.field label="{{ __('Password') }}" type="password" name="password" required max-width="w-full"/>

    @error('password')
        <span class="text-xs text-red-500" role="alert">
            {{ $message }}
        </span>
    @enderror

    <x-input.field label="{{ __('Confirm Password') }}" type="password" name="password_confirmation" required  max-width="w-full"/>

    @error('password')
        <span class="text-xs text-red-500" role="alert">
            {{ $message }}
        </span>
    @enderror

    @if (config('app.trial_without_payment.enabled'))
        <p class="mt-4 rounded-xl border border-primary-200 bg-primary-50/70 px-4 py-3 text-sm text-primary-900">
            {{ __('Your 7-day free trial starts when you create your account. No credit card required.') }}
        </p>
    @endif

    <x-button-link.primary class="inline-block w-full! mt-4 mb-2" elementType="button" type="submit">
        @if (config('app.trial_without_payment.enabled'))
            {{ __('Create account & start free trial') }}
        @else
            {{ __('Register') }}
        @endif
    </x-button-link.primary>

    <x-auth.social-login>
        <x-slot name="before">
            <div class="flex flex-col w-full">
                <div class="divider">{{ __('or') }}</div>
            </div>
        </x-slot>
    </x-auth.social-login>

</form>
