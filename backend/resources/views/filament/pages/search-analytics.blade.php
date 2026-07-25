<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <x-filament::section>
            <div class="text-sm text-gray-500">Total Searches (30 days)</div>
            <div class="text-2xl font-bold">{{ $summary['total_searches'] }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Unique Terms</div>
            <div class="text-2xl font-bold">{{ $summary['unique_terms'] }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Zero-Result Searches</div>
            <div class="text-2xl font-bold">{{ $summary['zero_result_searches'] }}</div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-6">
        <x-filament::section>
            <h2 class="text-lg font-semibold mb-4">Top Search Terms</h2>
            @if ($topTerms->isEmpty())
                <p class="text-gray-500">No search data yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="pb-2">Term</th>
                                <th class="pb-2">Searches</th>
                                <th class="pb-2">Avg Results</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topTerms as $term)
                                <tr class="border-b">
                                    <td class="py-2 font-medium">{{ $term->term }}</td>
                                    <td class="py-2">{{ $term->count }}</td>
                                    <td class="py-2">{{ number_format($term->avg_results, 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <h2 class="text-lg font-semibold mb-4">Search Trend</h2>
            @if ($trend->isEmpty())
                <p class="text-gray-500">No trend data yet.</p>
            @else
                <div class="space-y-2">
                    @foreach ($trend as $day)
                        <div class="flex items-center gap-3">
                            <span class="w-24 text-sm text-gray-600">{{ $day->date }}</span>
                            <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full" style="width: {{ min(100, ($day->count / max(1, $trend->max('count'))) * 100) }}%"></div>
                            </div>
                            <span class="w-8 text-right text-sm font-medium">{{ $day->count }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
