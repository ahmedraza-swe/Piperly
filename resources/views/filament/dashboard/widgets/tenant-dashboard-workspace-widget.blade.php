<x-filament-widgets::widget>
    <div class="space-y-6">
        <div class="grid gap-6 lg:grid-cols-3">
            <x-filament::section class="lg:col-span-2">
                <x-slot name="heading">{{ __('Recent deals') }}</x-slot>
                <x-slot name="description">{{ __('Latest updates in your pipeline') }}</x-slot>

                <div class="overflow-x-auto">
                @if ($recentDeals->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No deals yet. Add one from Pipeline → Deals.') }}</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-2">{{ __('Deal') }}</th>
                                <th class="py-2">{{ __('Company') }}</th>
                                <th class="py-2">{{ __('Value') }}</th>
                                <th class="py-2">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentDeals as $deal)
                                <tr class="border-t border-gray-100 dark:border-gray-800">
                                    <td class="py-2">
                                        <a
                                            href="{{ \App\Filament\Dashboard\Resources\Deals\DealResource::getUrl('view', ['record' => $deal->id]) }}"
                                            class="text-primary-600 hover:underline"
                                        >
                                            {{ $deal->title }}
                                        </a>
                                    </td>
                                    <td class="py-2">{{ $deal->company_name ?: '—' }}</td>
                                    <td class="py-2">
                                        @if ($deal->value !== null)
                                            ${{ number_format((float) $deal->value, 0, '.', ',') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        <span @class([
                                            'text-emerald-600' => $deal->status === 'won',
                                            'text-rose-600' => $deal->status === 'lost',
                                            'text-sky-600' => $deal->status === 'open',
                                        ])>{{ __($deal->status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">{{ __('Quick actions') }}</x-slot>
                <div class="space-y-2 text-sm">
                <a href="{{ \App\Filament\Dashboard\Resources\Activities\ActivityResource::getUrl('index') }}" class="block text-primary-600 hover:underline">
                    {{ __('View activities') }}
                </a>
                <a href="{{ \App\Filament\Dashboard\Resources\Contacts\ContactResource::getUrl('index') }}" class="block text-primary-600 hover:underline">
                    {{ __('View contacts') }}
                </a>
                <a href="{{ \App\Filament\Dashboard\Resources\Leads\LeadResource::getUrl('index') }}" class="block text-primary-600 hover:underline">
                    {{ __('View leads') }}
                </a>
                <a href="{{ \App\Filament\Dashboard\Resources\Leads\LeadResource::getUrl('create') }}" class="block text-primary-600 hover:underline">
                    {{ __('Add lead') }}
                </a>
                <a href="{{ \App\Filament\Dashboard\Resources\Deals\DealResource::getUrl('index') }}" class="block text-primary-600 hover:underline">
                    {{ __('View deals') }}
                </a>
                <a href="{{ \App\Filament\Dashboard\Resources\Deals\DealResource::getUrl('kanban') }}" class="block text-primary-600 hover:underline">
                    {{ __('Deal board') }}
                </a>
                <a href="{{ route('filament.dashboard.pages.settings-hub', $tenantRouteParams) }}" class="block text-primary-600 hover:underline">
                    {{ __('Workspace settings') }}
                </a>
                </div>
            </x-filament::section>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">{{ __('Recent leads') }}</x-slot>
                @if ($recentLeads->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('No leads yet.') }}</p>
                @else
                    <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
                        @foreach ($recentLeads as $lead)
                            <li class="flex justify-between gap-2">
                                <a
                                    href="{{ \App\Filament\Dashboard\Resources\Leads\LeadResource::getUrl('view', ['record' => $lead->id]) }}"
                                    class="truncate text-primary-600 hover:underline"
                                >{{ $lead->title }}</a>
                                <span class="shrink-0 text-gray-500">
                                    @if ($lead->converted_at)
                                        {{ __('Converted') }}
                                    @else
                                        {{ __($lead->status) }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">{{ __('Workspace checklist') }}</x-slot>
                <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
                    <li>{{ __('Capture inbound leads with sources and owners') }}</li>
                    <li>{{ __('Convert qualified leads to deals from the lead view') }}</li>
                    <li>{{ __('Move deals on the board as stages change') }}</li>
                    <li>{{ __('Refresh sample CRM rows with: php artisan db:seed --class=CrmDemoDataSeeder') }}</li>
                </ul>
            </x-filament::section>
        </div>
    </div>
</x-filament-widgets::widget>
