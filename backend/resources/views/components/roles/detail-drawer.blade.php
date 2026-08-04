@props(['show' => false, 'role' => null])

<div
    class="vestra-roles-detail @if ($show) vestra-roles-detail--open @endif"
    x-data="{ open: @js($show) }"
    x-show="open"
    x-cloak
    @keydown.escape.window="if (open) $wire.closeDetailDrawer()"
    role="dialog"
    aria-modal="true"
    aria-label="Role details"
>
    <div class="vestra-roles-detail__overlay" wire:click="closeDetailDrawer"></div>
    <div class="vestra-roles-detail__panel">
        @if ($role)
            <div class="vestra-roles-detail__header">
                <div class="vestra-roles-detail__header-text">
                    <h2 class="vestra-roles-detail__title">{{ $role['name'] ?? 'Role' }}</h2>
                    <p class="vestra-roles-detail__subtitle">{{ $role['type_label'] ?? '' }}</p>
                </div>
                <button type="button" wire:click="closeDetailDrawer" class="vestra-roles-detail__close" aria-label="Close">
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>
            <div class="vestra-roles-detail__body">
                <div class="vestra-roles-detail__section">
                    <h3 class="vestra-roles-detail__section-title">Details</h3>
                    <dl class="vestra-roles-detail__definition-list">
                        <div class="vestra-roles-detail__definition-row"><dt>Name</dt><dd>{{ $role['name'] ?? '—' }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Description</dt><dd>{{ $role['description'] ?: '—' }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Users</dt><dd>{{ number_format((int) ($role['users_count'] ?? 0)) }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Permissions</dt><dd>{{ number_format((int) ($role['permissions_count'] ?? 0)) }}</dd></div>
                        <div class="vestra-roles-detail__definition-row"><dt>Created</dt><dd>{{ $role['created_at']?->format('M j, Y') ?? '—' }}</dd></div>
                    </dl>
                </div>
                <div class="vestra-roles-detail__section">
                    <h3 class="vestra-roles-detail__section-title">Permissions</h3>
                    @forelse (($role['permissions'] ?? []) as $group)
                        <p><strong>{{ $group['group'] }}</strong></p>
                        <ul>
                            @foreach ($group['items'] as $perm)
                                <li>{{ $perm }}</li>
                            @endforeach
                        </ul>
                    @empty
                        <p>—</p>
                    @endforelse
                </div>
                <div class="vestra-roles-detail__section">
                    <h3 class="vestra-roles-detail__section-title">Assigned users</h3>
                    @forelse (($role['users'] ?? []) as $user)
                        <p>{{ $user['name'] }} <span class="vestra-roles__row-meta">({{ $user['email'] }})</span></p>
                    @empty
                        <p>—</p>
                    @endforelse
                </div>
                @if (! empty($role['edit_url']))
                    <div class="vestra-roles-detail__footer">
                        <a href="{{ $role['edit_url'] }}" class="vestra-button vestra-button--primary">Edit Role</a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
