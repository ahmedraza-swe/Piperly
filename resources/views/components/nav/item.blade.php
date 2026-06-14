@props(['route' => '#'])

@php($onLanding = request()->routeIs('home'))
@php($selected = request()->routeIs($route))
@php($selectedClass = match (true) {
    $onLanding && $selected => 'landing-nav-item is-active',
    $onLanding => 'landing-nav-item',
    $selected => 'text-white',
    default => 'text-primary-50',
})

<li {{ $attributes }}>
    <a
        href="{{ str_starts_with($route, '#') ? (route('home') . $route) : route($route) }}"
        @class([
            'text-sm block py-2 px-3 rounded transition-colors',
            'md:px-4 md:py-2 md:rounded-full' => $onLanding,
            'hover:bg-primary-600 md:hover:bg-transparent md:hover:text-white md:dark:hover:text-primary-500 dark:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700' => ! $onLanding,
            $selectedClass,
        ])
    >
        {{ $slot }}
    </a>
</li>
