<x-filament-widgets::widget>
    <x-filament::section
        :heading="__('Top owners')"
        :description="__('Ranked by won deals, then by total deal value.')"
    >
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="py-2 pr-2">{{ __('Owner') }}</th>
                        <th class="py-2 pr-2">{{ __('Won') }}</th>
                        <th class="py-2 pr-2">{{ __('Deals') }}</th>
                        <th class="py-2">{{ __('Value') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="border-b last:border-0">
                            <td class="py-2 pr-2">{{ $row['owner'] }}</td>
                            <td class="py-2 pr-2 font-medium">{{ $row['won_deals'] }}</td>
                            <td class="py-2 pr-2">{{ $row['total_deals'] }}</td>
                            <td class="py-2">{{ $row['total_value'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('No deal performance data yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
