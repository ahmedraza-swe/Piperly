@props([
    'variant' => 'light',
    'height' => 'h-7',
])

@php
    $logo = $variant === 'dark' ? config('app.logo.dark') : config('app.logo.light');
@endphp

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'inline-flex items-center py-1']) }}>
    <img
        src="{{ asset($logo) }}"
        class="{{ $height }} w-auto"
        alt="{{ config('app.name') }}"
    />
</a>
