<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <x-filament::section>
            <div class="text-sm text-gray-500">Deliveries Today</div>
            <div class="text-2xl font-bold">{{ $deliveriesToday }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Emails Today</div>
            <div class="text-2xl font-bold">{{ $emailsToday }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">SMS Today</div>
            <div class="text-2xl font-bold">{{ $smsToday }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">In-App Today</div>
            <div class="text-2xl font-bold">{{ $inAppToday }}</div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mt-6">
        <x-filament::section class="lg:col-span-2">
            <h2 class="text-lg font-semibold mb-4">Recent Deliveries</h2>
            @if ($recentDeliveries->isEmpty())
                <p class="text-gray-500">No deliveries yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b">
                                <th class="pb-2">ID</th>
                                <th class="pb-2">Channel</th>
                                <th class="pb-2">Recipient</th>
                                <th class="pb-2">Status</th>
                                <th class="pb-2">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentDeliveries as $delivery)
                                <tr class="border-b">
                                    <td class="py-2">{{ $delivery->id }}</td>
                                    <td class="py-2">{{ $delivery->channel?->label() }}</td>
                                    <td class="py-2">{{ $delivery->recipient }}</td>
                                    <td class="py-2">{{ $delivery->status?->label() }}</td>
                                    <td class="py-2">{{ $delivery->created_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <h2 class="text-lg font-semibold mb-4">Status Overview</h2>
            <dl class="space-y-2">
                @foreach ($statusCounts as $status => $count)
                    <div class="flex justify-between">
                        <dt class="text-gray-600 capitalize">{{ $status }}</dt>
                        <dd class="font-medium">{{ $count }}</dd>
                    </div>
                @endforeach
            </dl>

            <h2 class="text-lg font-semibold mt-6 mb-4">Configuration</h2>
            <dl class="space-y-2">
                <div class="flex justify-between">
                    <dt class="text-gray-600">Active Templates</dt>
                    <dd class="font-medium">{{ $activeTemplates }} / {{ $totalTemplates }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-600">Active Announcements</dt>
                    <dd class="font-medium">{{ $activeAnnouncements }}</dd>
                </div>
            </dl>
        </x-filament::section>
    </div>
</x-filament-panels::page>
