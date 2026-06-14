<fieldset class="fieldset">
    <legend class="fieldset-legend font-medium">{{ __('Email Address') }}</legend>
    <input type="email" class="input w-full" name="email" required id="email" wire:model.blur="email" value="{{ old('email') }}" />
</fieldset>

@error('email')
<span class="text-xs text-red-500" role="alert">
    {{ $message }}
</span>
@enderror


@if(!empty($email) && (empty($minimalSignup) || $userExists))
    <fieldset class="fieldset">
        <legend class="fieldset-legend font-medium">{{ __('Password') }}</legend>
        <input type="password" class="input w-full" name="password" required id="password" wire:model="password" />
    </fieldset>

    @error('password')
    <span class="text-xs text-red-500 ms-1" role="alert">
        {{ $message }}
    </span>
    @enderror
@endif

@if (session('sign_in_link_sent'))
    <div class="my-3 rounded-lg border border-primary-200 bg-primary-50 px-3 py-2 text-xs text-primary-800">
        {{ session('sign_in_link_sent') }}
    </div>
@endif

@if ($userExists)
    <div class="my-2 ms-1 text-xs text-neutral-400">
        {{ ! empty($minimalSignup)
            ? __('This email already has an account. Enter your password, or request a sign-in link below.')
            : __('You are already registered, enter your password.') }}
    </div>
@elseif(!empty($email) && empty($minimalSignup))
    <div class="my-2 ms-1 text-xs text-neutral-400">{{ __('Enter a password for your new account.') }}</div>
@endif

@if($userExists)
    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        @if (! empty($minimalSignup))
            <button
                type="button"
                wire:click="requestSignInLink"
                wire:loading.attr="disabled"
                class="text-xs font-semibold text-primary-600 hover:text-primary-800 hover:underline disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="requestSignInLink">{{ __('Email me a sign-in link') }}</span>
                <span wire:loading wire:target="requestSignInLink">{{ __('Sending…') }}</span>
            </button>
        @endif
        @if (Route::has('password.request'))
            <a class="text-primary-500 text-xs hover:underline {{ empty($minimalSignup) ? 'ms-auto' : '' }}" href="{{ route('password.request', ['email' => $email ?? '']) }}">
                {{ __('Forgot Your Password?') }}
            </a>
        @endif
    </div>
@endif


@if(!$userExists || empty($email))

    <fieldset class="fieldset">
        <legend class="fieldset-legend font-medium">{{ __('Your Name') }}</legend>
        <input type="text" class="input w-full" name="name" required id="name" wire:model="name" value="{{ old('name') }}" />
    </fieldset>

    @error('name')
    <span class="text-xs text-red-500" role="alert">
        {{ $message }}
    </span>
    @enderror

    @if(!empty($minimalSignup))
        <div class="my-2 ms-1 text-xs text-neutral-400">{{ __('We will email you a link to set your password later.') }}</div>
    @endif
@endif

@include('livewire.auth.partials.recaptcha')
