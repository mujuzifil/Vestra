@props(['show' => false, 'staff' => null])

<div
    class="vestra-staff-detail @if ($show) vestra-staff-detail--open @endif"
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    role="dialog"
    aria-modal="true"
    aria-label="Staff details"
>
    <div class="vestra-staff-detail__overlay" wire:click="closeDetailDrawer"></div>
    <div class="vestra-staff-detail__panel">
        @if ($staff)
            <div class="vestra-staff-detail__header">
                <div class="vestra-staff-detail__header-main">
                    <div class="vestra-staff-detail__header-text">
                        <h2 class="vestra-staff-detail__title">{{ $staff['name'] ?? 'Staff' }}</h2>
                        <p class="vestra-staff-detail__subtitle">{{ $staff['email'] ?? '' }}</p>
                    </div>
                </div>
                <button type="button" wire:click="closeDetailDrawer" class="vestra-staff-detail__close" aria-label="Close">
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>
            <div class="vestra-staff-detail__body">
                <div class="vestra-staff-detail__badges">
                    <x-staff.status-badge :status="$staff['status'] ?? null" />
                </div>
                <div class="vestra-staff-detail__section">
                    <h3 class="vestra-staff-detail__section-title">Profile</h3>
                    <dl class="vestra-staff-detail__definition-list">
                        <div class="vestra-staff-detail__definition-row"><dt>Name</dt><dd>{{ $staff['name'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Email</dt><dd>{{ $staff['email'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Last login</dt><dd>{{ $staff['last_login_at']?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Joined</dt><dd>{{ $staff['created_at']?->format('M j, Y') ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Password reset</dt><dd>{{ ($staff['password_reset_pending'] ?? false) ? 'Pending' : 'No' }}</dd></div>
                    </dl>
                </div>
                <div class="vestra-staff-detail__section">
                    <h3 class="vestra-staff-detail__section-title">Roles</h3>
                    @forelse (($staff['roles'] ?? []) as $role)
                        <span class="vestra-staff__category-pill">{{ $role['name'] }}</span>
                    @empty
                        <p>—</p>
                    @endforelse
                </div>
                @if (! empty($staff['edit_url']))
                    <div class="vestra-staff-detail__footer">
                        <a href="{{ $staff['edit_url'] }}" class="vestra-button vestra-button--primary">Edit Staff</a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
