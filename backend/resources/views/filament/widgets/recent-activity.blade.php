@php
$colorMap = [
    'primary' => ['bg' => 'var(--primary-100)', 'text' => 'var(--primary-600)'],
    'success' => ['bg' => 'var(--success-100)', 'text' => 'var(--success-600)'],
    'danger' => ['bg' => 'var(--danger-100)', 'text' => 'var(--danger-600)'],
    'warning' => ['bg' => 'var(--warning-100)', 'text' => 'var(--warning-600)'],
    'info' => ['bg' => 'var(--info-100)', 'text' => 'var(--info-600)'],
    'gray' => ['bg' => 'var(--neutral-100)', 'text' => 'var(--neutral-500)'],
];
$activities = $this->getActivities();
@endphp

<x-filament-widgets::widget class="fi-wi-recent-activity vestra-card">
    <div class="vestra-card-header">
        <h3 class="vestra-card-title">Recent Activity</h3>
        <a href="{{ url('/admin/audit-logs') }}" class="vestra-card-link">
            View all
        </a>
    </div>

    <div class="flow-root">
        <ul role="list" class="divide-y divide-[var(--border-default)]">
            @forelse ($activities as $activity)
                @php
                    $style = $colorMap[$activity['color']] ?? $colorMap['gray'];
                @endphp
                <li class="py-3.5 first:pt-0 last:pb-0">
                    <a @if($activity['url']) href="{{ $activity['url'] }}" @endif
                       class="group flex items-start gap-3.5 rounded-lg -mx-2 px-2 py-1.5 @if($activity['url']) hover:bg-[var(--neutral-50)] @endif transition-colors"
                       @if($activity['url']) tabindex="0" @endif>
                        <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                              style="background-color: {{ $style['bg'] }}; color: {{ $style['text'] }};">
                            <x-filament::icon :icon="$activity['icon']" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1 pt-0.5">
                            <p class="text-sm font-semibold text-[var(--text-heading)] truncate">
                                {{ $activity['title'] }}
                            </p>
                            <p class="text-xs text-[var(--text-muted)] truncate mt-0.5">
                                {{ $activity['subtitle'] }}
                            </p>
                        </div>
                        <span class="text-xs text-[var(--text-muted)] whitespace-nowrap pt-1.5">
                            {{ $activity['time'] }}
                        </span>
                    </a>
                </li>
            @empty
                <li>
                    <x-admin.empty-state
                        icon="heroicon-o-clock"
                        title="No recent activity"
                        description="Actions will appear here as your team works."
                    />
                </li>
            @endforelse
        </ul>
    </div>
</x-filament-widgets::widget>
