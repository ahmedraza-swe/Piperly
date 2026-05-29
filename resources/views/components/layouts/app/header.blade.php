<nav @class([
    'relative text-white border-gray-200',
    'bg-primary-500' => ! request()->routeIs('home'),
    'landing-site-nav bg-transparent' => request()->routeIs('home'),
])>
    <div class="navbar max-w-(--breakpoint-xl) items-center mx-auto">
        <div class="navbar-start">
            <div class="dropdown">
                <div tabindex="0" role="button" class="btn btn-ghost lg:hidden me-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
                </div>
                <ul tabindex="0" class="menu menu-lg dropdown-content mt-3 z-1 p-2 border border-primary-50 shadow-2xl shadow-primary-500/50 bg-primary-500 rounded-box w-52">
                    <x-layouts.app.navigation-links></x-layouts.app.navigation-links>
                </ul>
            </div>
            <a href="/" class="flex justify-center items-center">
                <img src="{{asset(config('app.logo.light') )}}" class="h-6" alt="Logo" />
            </a>
        </div>
        <div class="navbar-center hidden lg:flex">
            <x-nav>
                <x-layouts.app.navigation-links></x-layouts.app.navigation-links>
            </x-nav>
        </div>
        <div class="navbar-end">
            @auth
                <x-layouts.app.user-menu></x-layouts.app.user-menu>
            @else
                <x-link class="hidden lg:block text-primary-50" href="{{ url('/admin/login') }}">{{ __('Platform owner') }}</x-link>
                <x-link class="hidden md:block text-primary-50" href="{{ route('login') }}">{{ __('Customer login') }}</x-link>
                <x-button-link.secondary elementType="a" href="{{ request()->routeIs('home') ? '#pricing' : route('home') . '#pricing' }}" class="hidden sm:inline-flex !rounded-full !font-semibold !shadow-md">{{ __('Start free trial') }}</x-button-link.secondary>
            @endauth
        </div>
    </div>
</nav>
