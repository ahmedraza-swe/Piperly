<x-filament-panels::page>
    <div class="flex gap-4 overflow-x-auto pb-2" style="min-height: 28rem;">
        @foreach ($this->columns as $column)
            <div
                class="fi-section rounded-xl bg-gray-50 dark:bg-white/5 ring-1 ring-gray-950/5 dark:ring-white/10 shrink-0 w-72 flex flex-col max-h-[70vh]"
                data-stage-id="{{ $column['id'] ?? '' }}"
                x-on:dragover.prevent="$el.classList.add('ring-primary-500')"
                x-on:dragleave="$el.classList.remove('ring-primary-500')"
                x-on:drop.prevent="
                    $el.classList.remove('ring-primary-500');
                    const id = parseInt($event.dataTransfer.getData('deal-id'), 10);
                    const stageRaw = $el.dataset.stageId;
                    const stageId = stageRaw === '' ? null : parseInt(stageRaw, 10);
                    if (id) { $wire.moveDeal(id, stageId); }
                "
            >
                <div class="px-3 py-2 border-b border-gray-200 dark:border-white/10 font-semibold text-sm">
                    {{ $column['name'] }}
                    <span class="text-gray-500 dark:text-gray-400 font-normal">
                        ({{ count($column['deals']) }})
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto p-2 space-y-2">
                    @foreach ($column['deals'] as $deal)
                        <div
                            draggable="true"
                            x-on:dragstart="$event.dataTransfer.setData('deal-id', String({{ $deal['id'] }})); $event.dataTransfer.effectAllowed = 'move';"
                            class="rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-3 shadow-sm hover:border-primary-400 cursor-grab active:cursor-grabbing"
                        >
                            <div class="font-medium text-sm text-gray-900 dark:text-white">{{ $deal['title'] }}</div>
                            @if (! empty($deal['company']))
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $deal['company'] }}</div>
                            @endif
                            <div class="mt-2 flex items-center justify-between gap-2 text-xs">
                                @if (! empty($deal['value']))
                                    <span class="font-medium text-primary-600 dark:text-primary-400">${{ $deal['value'] }}</span>
                                @else
                                    <span></span>
                                @endif
                                @if (! empty($deal['owner']))
                                    <span class="truncate text-gray-500" title="{{ $deal['owner'] }}">{{ $deal['owner'] }}</span>
                                @endif
                            </div>
                            <a
                                href="{{ \App\Filament\Dashboard\Resources\Deals\DealResource::getUrl('view', ['record' => $deal['id']]) }}"
                                class="mt-2 inline-flex text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400"
                                draggable="false"
                            >
                                {{ __('Open') }} →
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
