<x-filament-panels::page>
    @php($groups = $this->getSettingGroups())

    @if (count($groups) === 0)
        <x-filament::section>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('You do not have access to workspace settings in this workspace. Contact an administrator.') }}
            </p>
        </x-filament::section>
    @else
        <div class="space-y-8">
            @foreach ($groups as $section)
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                        {{ $section['group'] }}
                    </h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($section['items'] as $item)
                            <a
                                href="{{ $item['url'] }}"
                                class="group flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-primary-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-900 dark:hover:border-primary-500"
                            >
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex rounded-lg bg-primary-50 p-2 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                        <x-filament::icon :icon="$item['icon']" class="h-6 w-6" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-gray-950 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">
                                            {{ $item['title'] }}
                                        </h3>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $item['description'] }}
                                        </p>
                                    </div>
                                    <x-filament::icon
                                        icon="heroicon-m-chevron-right"
                                        class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-primary-500"
                                    />
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
