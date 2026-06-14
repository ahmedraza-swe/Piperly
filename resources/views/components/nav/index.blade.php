@php($onLanding = request()->routeIs('home'))

<div class="items-center text-base font-light justify-between hidden w-full md:flex md:w-auto md:order-1">
    <ul @class([
        'flex flex-col p-4 md:p-0 mt-4 rounded-lg md:flex-row md:mt-0 md:space-x-1 rtl:space-x-reverse',
        'border border-gray-100 bg-primary-500 md:border-0 md:bg-primary-500 md:space-x-8' => ! $onLanding,
        'border-0 bg-transparent md:bg-transparent landing-nav-list' => $onLanding,
    ])>
        {{ $slot }}
    </ul>
</div>
