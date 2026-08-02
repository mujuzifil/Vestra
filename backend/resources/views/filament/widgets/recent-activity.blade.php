@php
$colorMap = [
    'primary' => '--primary-500',
    'success' => '--success-500',
    'danger' => '--danger-500',
    'warning' => '--warning-500',
    'info' => '--info-500',
    'gray' => '--neutral-400',
];
$activities = $this->getActivities();
@endphp

<x-filament-widgets::widget class="fi-wi-recent-activity vestra-card">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-semibold text-[var(--text-heading)]">Recent Activity</h3>
        <a href="{{ url('/admin/audit-logs') }}" class="text-sm font-medium text-[var(--primary-500)] hover:text-[var(--primary-600)]">
            View all
        </a>
    </div>

    <div class="flow-root">
        <ul role="list" class="divide-y divide-[var(--border-default)]">
            @forelse ($activities as $activity)
                @php
                    $cssVar = $colorMap[$activity['color']] ?? '--neutral-400';
                @endphp
                <li class="py-3 first:pt-0 last:pb-0">
                    <a @if($activity['url']) href="{{ $activity['url'] }}" @endif
                       class="group flex items-start gap-3 rounded-md @if($activity['url']) hover:bg-[var(--neutral-50)] @endif transition-colors"
                       @if($activity['url']) tabindex="0" @endif>
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg"
                              style="background-color: var({{ $cssVar }} / 0.12); color: var({{ $cssVar }});">
                            <x-filament::icon :icon="$activity['icon']" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-[var(--text-heading)] truncate">
                                {{ $activity['title'] }}
                            </p>
                            <p class="text-xs text-[var(--text-muted)] truncate">
                                {{ $activity['subtitle'] }}
                            </p>
                        </div>
                        <span class="text-xs text-[var(--text-muted)] whitespace-nowrap">
                            {{ $activity['time'] }}
                        </span>
                    </a>
                </li>
            @empty
                <li class="py-8">
                    <div class="flex flex-col items-center text-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--neutral-100)] text-[var(--neutral-400)]">
                            <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6" />
                        </span>
                        <p class="mt-3 text-sm font-medium text-[var(--text-heading)]">No recent activity</p>
                        <p class="mt-1 text-xs text-[var(--text-muted)]">Actions will appear here as your team works.</p>
                    </div>
                </li>
            @endforelse
        </ul>
    </div>
</x-filament-widgets::widget>
