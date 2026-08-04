@props([
    'show' => false,
    'staff' => null,
])

<div
    class="vestra-staff-detail @if ($show) vestra-staff-detail--open @endif"
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-x-4"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-4"
    aria-label="Staff details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-staff-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-staff-detail__panel">
        @if ($staff)
            <div class="vestra-staff-detail__header">
                <div class="vestra-staff-detail__header-main">
                    @if (! empty($staff['avatar_url']))
                        <img src="{{ $staff['avatar_url'] }}" alt="" class="vestra-staff-detail__avatar-image" />
                    @else
                        <span class="vestra-staff-detail__avatar">{{ $staff['initials'] ?? '—' }}</span>
                    @endif
                    <div class="vestra-staff-detail__header-text">
                        <h2 class="vestra-staff-detail__title">{{ $staff['name'] ?? 'Staff Member' }}</h2>
                        <p class="vestra-staff-detail__subtitle">{{ $staff['email'] ?? '' }}</p>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-staff-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-staff-detail__body">
                <div class="vestra-staff-detail__badges">
                    <x-staff.status-badge :status="$staff['status'] ?? null" />
                    @if ($staff['password_reset_pending'] ?? false)
                        <span class="vestra-staff__pending-pill">Password reset pending</span>
                    @endif
                </div>

                <div class="vestra-staff-detail__section">
                    <h3 class="vestra-staff-detail__section-title">Details</h3>
                    <dl class="vestra-staff-detail__definition-list">
                        <div class="vestra-staff-detail__definition-row">
                            <dt>Name</dt>
                            <dd>{{ $staff['name'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-staff-detail__definition-row">
                            <dt>Email</dt>
                            <dd>{{ $staff['email'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-staff-detail__definition-row">
                            <dt>Status</dt>
                            <dd>{{ $staff['status_label'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-staff-detail__definition-row">
                            <dt>Last login</dt>
                            <dd>{{ $staff['last_login_at']?->format('M j, Y g:i A') ?? 'Never' }}</dd>
                        </div>
                        <div class="vestra-staff-detail__definition-row">
                            <dt>Created</dt>
                            <dd>{{ $staff['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-staff-detail__definition-row">
                            <dt>Updated</dt>
                            <dd>{{ $staff['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-staff-detail__section">
                    <h3 class="vestra-staff-detail__section-title">Roles</h3>
                    @if (! empty($staff['roles']))
                        <div class="vestra-staff-detail__badges">
                            @foreach ($staff['roles'] as $role)
                                <span class="vestra-staff__role-pill">{{ $role['name'] }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="vestra-staff-detail__text">No roles assigned.</p>
                    @endif
                </div>

                @if (! empty($staff['edit_url']))
                    <div class="vestra-staff-detail__section">
                        <a href="{{ $staff['edit_url'] }}" class="vestra-button vestra-button--primary">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            <span>Edit Staff Member</span>
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
