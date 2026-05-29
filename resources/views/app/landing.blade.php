<x-layouts.app>
    <x-slot name="title">
        {{ __('Welcome') }}
    </x-slot>

    <div class="mx-auto max-w-(--breakpoint-xl) px-4 py-10">
        <div class="mb-8">
            <x-heading.h2 class="text-primary-900">
                {{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}
            </x-heading.h2>
            <p class="mt-2 text-gray-600">
                {{ __('Choose where you want to continue.') }}
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            @if (auth()->user()->isAdmin())
                <div class="rounded-xl border-2 border-amber-200 bg-amber-50/50 p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-800">{{ __('Platform owner') }}</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">{{ __('Platform console') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Manage products, plans, tenants, subscriptions, and payments you receive as the vendor.') }}
                    </p>
                    <a href="{{ route('filament.admin.pages.platform') }}" class="mt-4 inline-block font-semibold text-amber-700 hover:text-amber-900">
                        {{ __('Open platform console') }} →
                    </a>
                </div>
            @endif

            @if (auth()->user()->tenants()->exists())
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">{{ __('Customer workspace') }}</p>
                    <h3 class="mt-1 text-lg font-semibold text-gray-900">{{ __('CRM workspace') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Leads, deals, contacts, and team settings for your organization.') }}
                    </p>
                    <a href="{{ route('dashboard') }}" class="mt-4 inline-block font-semibold text-primary-600 hover:text-primary-800">
                        {{ __('Open CRM workspace') }} →
                    </a>
                </div>
            @elseif (! auth()->user()->isAdmin())
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm md:col-span-2">
                    <h3 class="text-lg font-semibold">{{ __('No workspace yet') }}</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('Subscribe on the home page to create your CRM workspace.') }}
                    </p>
                    <a href="{{ route('home') }}#pricing" class="mt-4 inline-block font-semibold text-primary-600">
                        {{ __('View plans') }} →
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
