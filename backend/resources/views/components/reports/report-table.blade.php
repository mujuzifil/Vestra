@props([
    'columns' => [],
    'rows' => [],
    'emptyHeading' => 'No data available',
    'emptyDescription' => 'There is no data to display for the selected filters.',
])

<div class="report-table-container overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200">
            <thead class="bg-neutral-50">
                <tr>
                    @foreach ($columns as $column)
                        <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500">
                            {{ $column['label'] ?? $column['name'] ?? '' }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 bg-white">
                @forelse ($rows as $row)
                    <tr class="hover:bg-neutral-50">
                        @foreach ($columns as $column)
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-neutral-700">
                                @php
                                    $key = $column['name'] ?? $column['key'] ?? null;
                                    $value = $key ? data_get($row, $key) : null;
                                    $formatter = $column['format'] ?? null;
                                @endphp

                                @if (is_callable($formatter))
                                    {!! $formatter($row) !!}
                                @elseif ($key && isset($row[$key]))
                                    {{ $row[$key] }}
                                @elseif ($value !== null)
                                    {{ $value }}
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" class="px-4 py-8 text-center">
                            <x-filament::icon icon="heroicon-o-chart-bar" class="mx-auto h-8 w-8 text-neutral-300" />
                            <p class="mt-2 text-sm font-medium text-neutral-900">{{ $emptyHeading }}</p>
                            <p class="text-xs text-neutral-500">{{ $emptyDescription }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if (count($rows) > 0)
        <div class="border-t border-neutral-200 bg-neutral-50 px-4 py-2 text-xs text-neutral-500">
            Showing {{ count($rows) }} rows
        </div>
    @endif
</div>
