<x-filament-panels::page>
    <x-filament::section class="mb-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-amber-700 dark:text-amber-400">
                    {{ __('Platform owner') }}
                </p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Customer teams use the CRM workspace at /dashboard. This console is only for you—the vendor.') }}
                </p>
            </div>
            <x-filament::badge color="warning" size="lg">
                {{ __('Admin') }}
            </x-filament::badge>
        </div>
    </x-filament::section>

    <div class="space-y-8">
        @foreach ($this->getLinkGroups() as $section)
            <div>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                    {{ $section['group'] }}
                </h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($section['items'] as $item)
                        <a
                            href="{{ $item['url'] }}"
                            class="group flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-amber-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-900 dark:hover:border-amber-500"
                        >
                            <div class="flex items-start gap-3">
                                <span class="inline-flex rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                                    <x-filament::icon :icon="$item['icon']" class="h-6 w-6" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-semibold text-gray-950 group-hover:text-amber-600 dark:text-white dark:group-hover:text-amber-400">
                                        {{ $item['title'] }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $item['description'] }}
                                    </p>
                                </div>
                                <x-filament::icon
                                    icon="heroicon-m-chevron-right"
                                    class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-amber-500"
                                />
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
