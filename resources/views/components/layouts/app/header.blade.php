@php($onLanding = request()->routeIs('home'))

<nav @class([
    'relative z-50 text-white',
    'bg-primary-500 border-gray-200' => ! $onLanding,
    'landing-site-nav' => $onLanding,
])>
    <div class="navbar max-w-(--breakpoint-xl) items-center mx-auto px-4 lg:px-6">
        <div class="navbar-start">
            <div class="dropdown">
                <div tabindex="0" role="button" @class([
                    'btn btn-ghost lg:hidden me-1',
                    'text-white hover:bg-white/10' => $onLanding,
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                </div>
                <ul tabindex="0" @class([
                    'menu menu-lg dropdown-content mt-3 z-1 p-2 shadow-2xl rounded-box w-52',
                    'border border-primary-50 shadow-primary-500/50 bg-primary-500' => ! $onLanding,
                    'landing-nav-mobile-menu' => $onLanding,
                ])>
                    <x-layouts.app.navigation-links></x-layouts.app.navigation-links>
                </ul>
            </div>
            <x-app.brand />
        </div>
        <div class="navbar-center hidden lg:flex">
            <x-nav>
                <x-layouts.app.navigation-links></x-layouts.app.navigation-links>
            </x-nav>
        </div>
        <div class="navbar-end gap-2 sm:gap-3">
            @auth
                <x-layouts.app.user-menu></x-layouts.app.user-menu>
            @else
                <x-link @class([
                    'hidden lg:inline-flex text-sm font-medium',
                    'landing-nav-link' => $onLanding,
                    'text-primary-50' => ! $onLanding,
                ]) href="{{ url('/admin/login') }}">{{ __('Platform owner') }}</x-link>
                <x-link @class([
                    'hidden md:inline-flex text-sm font-medium',
                    'landing-nav-link' => $onLanding,
                    'text-primary-50' => ! $onLanding,
                ]) href="{{ route('login') }}">{{ __('Customer login') }}</x-link>
                <a href="{{ $onLanding ? '#pricing' : route('home') . '#pricing' }}" @class([
                    'hidden sm:inline-flex items-center justify-center text-sm font-semibold transition',
                    'landing-nav-cta' => $onLanding,
                    'btn btn-sm rounded-full bg-secondary-500 text-secondary-900 hover:bg-secondary-600' => ! $onLanding,
                ])>{{ __('Start 7-day trial') }}</a>
            @endauth
        </div>
    </div>
</nav>
