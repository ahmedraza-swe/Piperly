<x-filament-widgets::widget>
    <x-filament::section
        :heading="__('Follow-ups due')"
        :description="__('Open activities due today or overdue, soonest first.')"
    >
        <div class="space-y-2">
            @forelse($items as $row)
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700">
                    <div class="min-w-0 flex-1">
                        <a href="{{ $row['url'] }}" class="font-medium text-primary-600 hover:underline dark:text-primary-400">
                            {{ $row['subject'] }}
                        </a>
                        <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <span>{{ $typeLabels[$row['type']] ?? $row['type'] }}</span>
                            @if($row['due_at'])
                                <span>
                                    {{ __('Due') }}: {{ $row['due_at']->timezone(config('app.timezone'))->format(config('app.datetime_format')) }}
                                </span>
                            @endif
                            @if($row['is_overdue'])
                                <span class="inline-flex items-center rounded-md bg-danger-50 px-2 py-0.5 text-xs font-medium text-danger-700 ring-1 ring-inset ring-danger-600/10 dark:bg-danger-400/10 dark:text-danger-400 dark:ring-danger-400/20">
                                    {{ __('Overdue') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Nothing due right now. Great job staying on top of follow-ups.') }}
                </p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
