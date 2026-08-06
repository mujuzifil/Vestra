@props(['show' => false, 'role' => null])

@php
    $actions = $role['actions'] ?? [];
@endphp

<div
    class="vestra-roles-detail @if ($show) vestra-roles-detail--open @endif"
    x-data="{ open: @entangle('showDetailDrawer') }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    aria-label="Role details"
    role="dialog"
    aria-modal="true"
>
    <div class="vestra-roles-detail__overlay" wire:click="closeDetailDrawer"></div>

    <div class="vestra-roles-detail__panel">
        @if ($role)
            <div class="vestra-roles-detail__header">
                <div class="vestra-roles-detail__header-main">
                    <span class="vestra-roles-detail__avatar">
                        <x-filament::icon icon="heroicon-o-shield-check" class="h-5 w-5" />
                    </span>
                    <div class="vestra-roles-detail__header-text">
                        <h2 class="vestra-roles-detail__title">{{ $role['name'] ?? 'Role' }}</h2>
                        <p class="vestra-roles-detail__subtitle">{{ $role['description'] ?? 'No description' }}</p>
                    </div>
                </div>

                <button type="button" wire:click="closeDetailDrawer" class="vestra-roles-detail__close" aria-label="Close details">
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-roles-detail__body">
                <div class="vestra-roles-detail__badges">
                    <x-roles.status-badge :is-system="$role['is_system'] ?? false" />
                    <span class="vestra-roles__category-pill">{{ $role['status_label'] ?? 'Active' }}</span>
                </div>

                <div class="vestra-roles-detail__actions">
                    @if ($actions['edit'] ?? false)
                        <a href="{{ $role['edit_url'] }}" class="vestra-roles-detail__action-btn">Edit Role</a>
                    @endif
                    @if ($actions['duplicate'] ?? false)
                        <button type="button" wire:click="duplicateRole({{ $role['id'] }})" class="vestra-roles-detail__action-btn">Duplicate Role</button>
                    @endif
                    @if ($actions['enable'] ?? false)
                        <button type="button" wire:click="enableRole({{ $role['id'] }})" class="vestra-roles-detail__action-btn">Enable Role</button>
                    @endif
                    @if ($actions['disable'] ?? false)
                        <button type="button" wire:click="disableRole({{ $role['id'] }})" class="vestra-roles-detail__action-btn">Disable Role</button>
                    @endif
                    @if ($actions['delete'] ?? false)
                        <button type="button" wire:click="deleteRole({{ $role['id'] }})" wire:confirm="Delete this role permanently?" class="vestra-roles-detail__action-btn vestra-roles-detail__action-btn--danger">Delete Role</button>
                    @endif
                </div>

                <div class="vestra-roles-detail__section">
                    <h3 class="vestra-roles-detail__section-title">Details</h3>
                    <dl class="vestra-roles-detail__definition-list">
                        <div class="vestra-roles-detail__definition-row"><dt>Name</dt><dd>{{ $role['name'] ?? '—' }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Slug</dt><dd>{{ $role['slug'] ?? '—' }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Type</dt><dd>{{ $role['type_label'] ?? '—' }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Status</dt><dd>{{ $role['status_label'] ?? '—' }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Users Assigned</dt><dd>{{ number_format((int) ($role['users_count'] ?? 0)) }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Permissions</dt><dd>{{ number_format((int) ($role['permissions_count'] ?? 0)) }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Modules Access</dt><dd>{{ number_format((int) ($role['modules_count'] ?? 0)) }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Created</dt><dd>{{ $role['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Last Modified</dt><dd>{{ $role['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Created By</dt><dd>{{ $role['created_by'] ?? '—' }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Modified By</dt><dd>{{ $role['updated_by'] ?? '—' }}</dd></div>
                    </dl>
                </div>

                <div class="vestra-roles-detail__section">
                    <h3 class="vestra-roles-detail__section-title">Permission Comparison</h3>
                    @forelse (($role['permission_comparison'] ?? []) as $group)
                        <div class="vestra-roles-detail__perm-group">
                            <strong>{{ $group['label'] }}</strong>
                            <ul>
                                @foreach ($group['permissions'] as $permission)
                                    <li>
                                        <span>{{ $permission['label'] }}</span>
                                        <span>{{ $permission['granted'] ? '✓' : '—' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <p class="vestra-roles-detail__text">No discovered permissions.</p>
                    @endforelse
                </div>

                <div class="vestra-roles-detail__section">
                    <h3 class="vestra-roles-detail__section-title">Assigned Users</h3>
                    @forelse (($role['users'] ?? []) as $user)
                        <div class="vestra-roles-detail__user-row">
                            <div class="vestra-roles-detail__user-main">
                                @if ($user['avatar_url'] ?? null)
                                    <img src="{{ $user['avatar_url'] }}" alt="" class="vestra-roles-detail__user-avatar" />
                                @else
                                    <span class="vestra-roles-detail__user-avatar vestra-roles-detail__user-avatar--initials">{{ $user['initials'] ?? '??' }}</span>
                                @endif
                                <div>
                                    <a href="{{ $user['staff_url'] ?? '#' }}" class="vestra-roles-detail__user-name">{{ $user['name'] }}</a>
                                    <div class="vestra-roles-detail__user-meta">{{ $user['email'] }} · {{ $user['department'] ?? '—' }} · {{ $user['status'] ?? '—' }}</div>
                                    <div class="vestra-roles-detail__user-meta">Last login: {{ $user['last_login_at']?->format('M j, Y g:i A') ?? '—' }}</div>
                                </div>
                            </div>
                            @if ($actions['remove_users'] ?? false)
                                <button type="button" wire:click="removeUserFromRole({{ $role['id'] }}, {{ $user['id'] }})" wire:confirm="Remove this user from the role?" class="vestra-roles-detail__action-btn">Remove</button>
                            @endif
                        </div>
                    @empty
                        <p class="vestra-roles-detail__text">No users assigned.</p>
                    @endforelse
                </div>

                <div class="vestra-roles-detail__section">
                    <h3 class="vestra-roles-detail__section-title">Audit History</h3>
                    <div class="vestra-roles-detail__audit">
                        @forelse (($role['audit'] ?? []) as $entry)
                            <div class="vestra-roles-detail__audit-item">
                                <strong>{{ str_replace(['_', '.'], ' ', $entry['action'] ?? 'event') }}</strong>
                                <div class="vestra-roles-detail__audit-meta">
                                    <span>{{ $entry['timestamp']?->format('M j, Y g:i A') ?? '—' }}</span>
                                    <span>{{ $entry['user'] ?? 'System' }}</span>
                                    <span>{{ $entry['ip'] ?? '—' }}</span>
                                    <span>{{ $entry['device'] ?? '—' }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="vestra-roles-detail__text">No audit events yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
