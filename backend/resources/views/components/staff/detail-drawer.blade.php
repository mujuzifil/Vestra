@props(['show' => false, 'staff' => null])

@php
    $actions = $staff['actions'] ?? [];
@endphp

<div
    class="vestra-staff-detail @if ($show) vestra-staff-detail--open @endif"
    x-data="{ open: @entangle('showDetailDrawer') }"
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
                    @if ($staff['password_reset_pending'] ?? false)
                        <span class="vestra-staff__featured-pill">Password Reset Pending</span>
                    @endif
                    @if ($staff['is_locked'] ?? false)
                        <span class="vestra-staff__featured-pill">Locked</span>
                    @endif
                </div>

                @if (! empty($actions))
                    <div class="vestra-staff-detail__actions">
                        @if ($actions['edit'] ?? false)
                            <a href="{{ $staff['edit_url'] }}" class="vestra-staff-detail__action-btn">Edit Staff</a>
                        @endif
                        @if ($actions['disable'] ?? false)
                            <button type="button" wire:click="disableStaff({{ $staff['id'] }})" class="vestra-staff-detail__action-btn">Disable Account</button>
                        @endif
                        @if ($actions['enable'] ?? false)
                            <button type="button" wire:click="enableStaff({{ $staff['id'] }})" class="vestra-staff-detail__action-btn">Enable Account</button>
                        @endif
                        @if ($actions['reset_password'] ?? false)
                            <button type="button" wire:click="resetStaffPassword({{ $staff['id'] }})" wire:confirm="Reset this staff member's password and email a temporary password?" class="vestra-staff-detail__action-btn">Reset Password</button>
                        @endif
                        @if ($actions['force_password_change'] ?? false)
                            <button type="button" wire:click="forceStaffPasswordChange({{ $staff['id'] }})" class="vestra-staff-detail__action-btn">Force Password Change</button>
                        @endif
                        @if ($actions['lock'] ?? false)
                            <button type="button" wire:click="lockStaff({{ $staff['id'] }})" class="vestra-staff-detail__action-btn">Lock Account</button>
                        @endif
                        @if ($actions['unlock'] ?? false)
                            <button type="button" wire:click="unlockStaff({{ $staff['id'] }})" class="vestra-staff-detail__action-btn">Unlock Account</button>
                        @endif
                        @if ($actions['resend_welcome'] ?? false)
                            <button type="button" wire:click="resendStaffWelcome({{ $staff['id'] }})" class="vestra-staff-detail__action-btn">Resend Welcome Email</button>
                        @endif
                        @if ($actions['delete'] ?? false)
                            <button type="button" wire:click="deleteStaff({{ $staff['id'] }})" wire:confirm="Permanently delete this staff account?" class="vestra-staff-detail__action-btn vestra-staff-detail__action-btn--danger">Delete</button>
                        @endif
                    </div>
                @endif

                <div class="vestra-staff-detail__section">
                    <h3 class="vestra-staff-detail__section-title">Personal Information</h3>
                    <dl class="vestra-staff-detail__definition-list">
                        <div class="vestra-staff-detail__definition-row"><dt>Name</dt><dd>{{ $staff['name'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Email</dt><dd>{{ $staff['email'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Phone</dt><dd>{{ $staff['phone'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Username</dt><dd>{{ $staff['username'] ?? '—' }}</dd></div>
                    </dl>
                </div>

                <div class="vestra-staff-detail__section">
                    <h3 class="vestra-staff-detail__section-title">Account Information</h3>
                    <dl class="vestra-staff-detail__definition-list">
                        <div class="vestra-staff-detail__definition-row"><dt>Status</dt><dd>{{ $staff['status_label'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Department</dt><dd>{{ $staff['department'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Job Title</dt><dd>{{ $staff['job_title'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Employee ID</dt><dd>{{ $staff['employee_id'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Created</dt><dd>{{ $staff['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Last Login</dt><dd>{{ $staff['last_login_at']?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Last Password Change</dt><dd>{{ $staff['password_changed_at']?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Password Reset Pending</dt><dd>{{ ($staff['password_reset_pending'] ?? false) ? 'Yes' : 'No' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Created By</dt><dd>{{ $staff['created_by'] ?? '—' }}</dd></div>
                        <div class="vestra-staff-detail__definition-row"><dt>Modified By</dt><dd>{{ $staff['updated_by'] ?? '—' }}</dd></div>
                    </dl>
                </div>

                <div class="vestra-staff-detail__section">
                    <h3 class="vestra-staff-detail__section-title">Assigned Roles</h3>
                    @forelse (($staff['roles'] ?? []) as $role)
                        <span class="vestra-staff__category-pill">{{ $role['name'] }}</span>
                    @empty
                        <p>—</p>
                    @endforelse
                </div>

                <div class="vestra-staff-detail__section">
                    <h3 class="vestra-staff-detail__section-title">Permissions</h3>
                    @forelse (($staff['permissions'] ?? []) as $permission)
                        <span class="vestra-staff__category-pill">
                            {{ $permission['name'] }}
                            @if ($permission['override'] ?? false)
                                (override)
                            @endif
                        </span>
                    @empty
                        <p>—</p>
                    @endforelse
                </div>

                @if (! empty($staff['permission_overrides']))
                    <div class="vestra-staff-detail__section">
                        <h3 class="vestra-staff-detail__section-title">Permission Overrides</h3>
                        @foreach ($staff['permission_overrides'] as $override)
                            <span class="vestra-staff__featured-pill">{{ $override }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="vestra-staff-detail__section">
                    <h3 class="vestra-staff-detail__section-title">Audit Timeline</h3>
                    <div class="vestra-staff-detail__audit">
                        @forelse (($staff['audit'] ?? []) as $entry)
                            <div class="vestra-staff-detail__audit-item">
                                <strong>{{ str_replace(['_', '.'], ' ', $entry['action'] ?? 'event') }}</strong>
                                <div class="vestra-staff-detail__audit-meta">
                                    <span>{{ $entry['timestamp']?->format('M j, Y g:i A') ?? '—' }}</span>
                                    <span>{{ $entry['user'] ?? 'System' }}</span>
                                    <span>{{ $entry['ip'] ?? '—' }}</span>
                                    <span>{{ $entry['device'] ?? '—' }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="vestra-staff-form__hint">No audit events yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
