@props([
    'show' => false,
    'role' => null,
])

<div
    class="vestra-roles-detail @if ($show) vestra-roles-detail--open @endif"
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

                <button
                    type="button"
                    wire:click="closeDetailDrawer"
                    class="vestra-roles-detail__close"
                    aria-label="Close details"
                >
                    <x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" />
                </button>
            </div>

            <div class="vestra-roles-detail__body">
                <div class="vestra-roles-detail__badges">
                    <x-roles.status-badge :is-system="$role['is_system'] ?? false" />
                </div>

                <div class="vestra-roles-detail__section">
                    <h3 class="vestra-roles-detail__section-title">Details</h3>
                    <dl class="vestra-roles-detail__definition-list">
                        <div class="vestra-roles-detail__definition-row">
                            <dt>Name</dt>
                            <dd>{{ $role['name'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-roles-detail__definition-row">
                            <dt>Description</dt>
                            <dd>{{ $role['description'] ?? '—' }}</dd>
                        </div>
                        <div class="vestra-roles-detail__definition-row">
                            <dt>Users Assigned</dt>
                            <dd>{{ number_format((int) ($role['users_count'] ?? 0)) }}</dd>
                        </div>
                        <div class="vestra-roles-detail__definition-row">
                            <dt>Permissions</dt>
                            <dd>{{ number_format((int) ($role['permissions_count'] ?? 0)) }}</dd>
                        </div>
                        <div class="vestra-roles-detail__definition-row">
                            <dt>Created</dt>
                            <dd>{{ $role['created_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                        <div class="vestra-roles-detail__definition-row">
                            <dt>Updated</dt>
                            <dd>{{ $role['updated_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="vestra-roles-detail__section">
                    <h3 class="vestra-roles-detail__section-title">Permissions</h3>
                    @if (! empty($role['permissions']))
                        <div class="vestra-roles-detail__badges">
                            @foreach ($role['permissions'] as $permission)
                                <span class="vestra-roles__category-pill">{{ $permission['name'] }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="vestra-roles-detail__text">No permissions assigned.</p>
                    @endif
                </div>

                @if (! empty($role['edit_url']))
                    <div class="vestra-roles-detail__section">
                        <a href="{{ $role['edit_url'] }}" class="vestra-button vestra-button--primary">
                            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
                            <span>Edit Role</span>
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
