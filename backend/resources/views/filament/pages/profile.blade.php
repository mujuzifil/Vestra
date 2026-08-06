@php
$profile = $this->profile;
$sessions = $this->sessions;
$activity = $this->activity;
$logoutUrl = filament()->getLogoutUrl();
@endphp

<div class="vestra-workspace vestra-profile" wire:key="profile-{{ $profile['id'] }}">
    <section class="vestra-profile__hero">
        <div>
            <h1 class="vestra-workspace__title">My Profile</h1>
            <p class="vestra-workspace__welcome">View and manage your account information.</p>
        </div>
        <button type="button" class="vestra-button vestra-button--primary" wire:click="openEditModal">
            <x-filament::icon icon="heroicon-o-pencil-square" class="h-4 w-4" />
            <span>Edit Profile</span>
        </button>
    </section>

    <section class="vestra-card vestra-profile__overview">
        <div class="vestra-profile__overview-main">
            <div class="vestra-profile__avatar-wrap">
                @if ($profile['avatar_url'] ?? null)
                    <img src="{{ $profile['avatar_url'] }}" alt="" class="vestra-profile__avatar" />
                @else
                    <span class="vestra-profile__avatar vestra-profile__avatar--initials">{{ $profile['initials'] ?? '?' }}</span>
                @endif
                <span class="vestra-profile__avatar-status @if (($profile['status'] ?? '') === 'active') vestra-profile__avatar-status--active @endif" aria-hidden="true"></span>
            </div>
            <div class="vestra-profile__overview-text">
                <h2 class="vestra-profile__name">{{ $profile['name'] }}</h2>
                @if ($profile['role'] ?? null)
                    <span class="vestra-profile__role-badge">{{ $profile['role'] }}</span>
                @endif
                <div class="vestra-profile__contact">
                    <span>{{ $profile['email'] }}</span>
                    @if ($profile['phone'] ?? null)
                        <span>{{ $profile['phone'] }}</span>
                    @endif
                </div>
            </div>
        </div>

        <dl class="vestra-profile__meta-grid">
            @if ($profile['username'] ?? null)
                <div>
                    <dt>Username</dt>
                    <dd>{{ $profile['username'] }}</dd>
                </div>
            @endif
            @if ($profile['department'] ?? null)
                <div>
                    <dt>Department</dt>
                    <dd>{{ $profile['department'] }}</dd>
                </div>
            @endif
            @if ($profile['employee_id'] ?? null)
                <div>
                    <dt>Employee ID</dt>
                    <dd>{{ $profile['employee_id'] }}</dd>
                </div>
            @endif
            <div>
                <dt>Member Since</dt>
                <dd>{{ ($profile['member_since'] ?? $profile['created_at'])?->format('M j, Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt>Last Login</dt>
                <dd>{{ $profile['last_login_at']?->format('M j, Y g:i A') ?? '—' }}</dd>
            </div>
            <div>
                <dt>Status</dt>
                <dd><span class="vestra-profile__status-dot"></span>{{ $profile['status_label'] }}</dd>
            </div>
        </dl>
    </section>

    <nav class="vestra-profile__tabs" aria-label="Profile sections">
        <button type="button" class="@if ($activeTab === 'personal') is-active @endif" wire:click="setTab('personal')">Personal Information</button>
        <button type="button" class="@if ($activeTab === 'sessions') is-active @endif" wire:click="setTab('sessions')">Sessions</button>
        <button type="button" class="@if ($activeTab === 'activity') is-active @endif" wire:click="setTab('activity')">Activity Log</button>
    </nav>

    @if ($activeTab === 'personal')
        <div class="vestra-profile__two-col">
            <section class="vestra-card vestra-profile__card">
                <h3 class="vestra-profile__card-title">Personal Information</h3>
                <dl class="vestra-profile__detail-list">
                    <div><dt>Full Name</dt><dd>{{ $profile['name'] }}</dd></div>
                    <div><dt>Email Address</dt><dd>{{ $profile['email'] }}</dd></div>
                    <div><dt>Phone Number</dt><dd>{{ $profile['phone'] ?? '—' }}</dd></div>
                    @if ($profile['username'] ?? null)
                        <div><dt>Username</dt><dd>{{ $profile['username'] }}</dd></div>
                    @endif
                    @if ($profile['department'] ?? null)
                        <div><dt>Department</dt><dd>{{ $profile['department'] }}</dd></div>
                    @endif
                    @if ($profile['job_title'] ?? null)
                        <div><dt>Job Title</dt><dd>{{ $profile['job_title'] }}</dd></div>
                    @endif
                    @if ($profile['employee_id'] ?? null)
                        <div><dt>Employee ID</dt><dd>{{ $profile['employee_id'] }}</dd></div>
                    @endif
                </dl>
                @if (($profile['department'] ?? null) || ($profile['job_title'] ?? null) || ($profile['employee_id'] ?? null))
                    <div class="vestra-profile__info-alert" role="note">
                        <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5" />
                        <p>Department, job title, and employee ID are managed in Staff Administration.</p>
                    </div>
                @endif
            </section>

            <section class="vestra-card vestra-profile__card">
                <h3 class="vestra-profile__card-title">Quick Actions</h3>
                <ul class="vestra-profile__actions">
                    <li>
                        <button type="button" wire:click="openPasswordModal">
                            <span class="vestra-profile__action-icon"><x-filament::icon icon="heroicon-o-key" class="h-5 w-5" /></span>
                            <span>
                                <strong>Change Password</strong>
                                <small>Update your account password.</small>
                            </span>
                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                        </button>
                    </li>
                    <li>
                        <button type="button" wire:click="openEditModal">
                            <span class="vestra-profile__action-icon"><x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" /></span>
                            <span>
                                <strong>Edit Profile</strong>
                                <small>Update name, email, phone, username, or photo.</small>
                            </span>
                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                        </button>
                    </li>
                    <li>
                        <button type="button" wire:click="setTab('sessions')">
                            <span class="vestra-profile__action-icon"><x-filament::icon icon="heroicon-o-computer-desktop" class="h-5 w-5" /></span>
                            <span>
                                <strong>Manage Sessions</strong>
                                <small>View and manage active sessions.</small>
                            </span>
                            <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                        </button>
                    </li>
                    <li>
                        <form method="POST" action="{{ $logoutUrl }}">
                            @csrf
                            <button type="submit">
                                <span class="vestra-profile__action-icon"><x-filament::icon icon="heroicon-o-arrow-right-on-rectangle" class="h-5 w-5" /></span>
                                <span>
                                    <strong>Sign Out</strong>
                                    <small>End your current session.</small>
                                </span>
                                <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                            </button>
                        </form>
                    </li>
                </ul>
            </section>
        </div>
    @endif

    @if ($activeTab === 'sessions')
        <section class="vestra-card vestra-profile__card">
            <div class="vestra-profile__card-head">
                <h3 class="vestra-profile__card-title">Active Sessions</h3>
                <button type="button" class="vestra-button vestra-button--secondary" wire:click="terminateOtherSessions" wire:confirm="Sign out of all other sessions?">
                    Sign out other sessions
                </button>
            </div>
            <div class="vestra-profile__table-wrap">
                <table class="vestra-profile__table">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>IP Address</th>
                            <th>Browser</th>
                            <th>Last Active</th>
                            <th>Session</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr wire:key="session-{{ $session['id'] }}">
                                <td>
                                    <strong>{{ $session['device'] }}</strong>
                                    @if ($session['os'] ?? null)
                                        <div class="vestra-profile__muted">{{ $session['os'] }}</div>
                                    @endif
                                </td>
                                <td>{{ $session['ip_address'] ?? '—' }}</td>
                                <td>{{ $session['browser'] ?? '—' }}</td>
                                <td>{{ $session['last_activity_at']?->format('M j, Y g:i A') ?? '—' }}</td>
                                <td>
                                    @if ($session['is_current'])
                                        <span class="vestra-profile__current-badge">Current</span>
                                    @elseif ($session['is_active'])
                                        <span class="vestra-profile__muted">Active</span>
                                    @else
                                        <span class="vestra-profile__muted">Idle</span>
                                    @endif
                                </td>
                                <td>
                                    @unless ($session['is_current'])
                                        <button type="button" class="vestra-profile__link-danger" wire:click="terminateSession({{ $session['id'] }})" wire:confirm="Terminate this session?">
                                            Terminate
                                        </button>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No tracked sessions.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if ($activeTab === 'activity')
        <section class="vestra-card vestra-profile__card">
            <h3 class="vestra-profile__card-title">Activity Log</h3>
            <div class="vestra-profile__activity">
                @forelse ($activity as $entry)
                    <div class="vestra-profile__activity-item">
                        <strong>{{ $entry['label'] }}</strong>
                        <div class="vestra-profile__muted">
                            <span>{{ $entry['timestamp']?->format('M j, Y g:i A') ?? '—' }}</span>
                            <span>{{ $entry['ip'] ?? '—' }}</span>
                            <span>{{ $entry['device'] ?: '—' }}</span>
                        </div>
                    </div>
                @empty
                    <p class="vestra-profile__muted">No recent account activity.</p>
                @endforelse
            </div>
        </section>
    @endif

    @if ($showEditModal)
        <div class="vestra-profile-modal" role="dialog" aria-modal="true" aria-label="Edit Profile">
            <div class="vestra-profile-modal__overlay" wire:click="closeEditModal"></div>
            <div class="vestra-profile-modal__panel">
                <div class="vestra-profile-modal__header">
                    <h2>Edit Profile</h2>
                    <button type="button" wire:click="closeEditModal" aria-label="Close"><x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" /></button>
                </div>
                <form wire:submit.prevent="saveProfile" class="vestra-profile-modal__body">
                    <div class="vestra-profile-form__field">
                        <label for="edit-name">Display Name <span>*</span></label>
                        <input id="edit-name" type="text" wire:model="editForm.name" class="@error('editForm.name') is-error @enderror" />
                        @error('editForm.name')<span class="vestra-profile-form__error">{{ $message }}</span>@enderror
                    </div>
                    <div class="vestra-profile-form__field">
                        <label for="edit-email">Email <span>*</span></label>
                        <input id="edit-email" type="email" wire:model="editForm.email" class="@error('editForm.email') is-error @enderror" />
                        @error('editForm.email')<span class="vestra-profile-form__error">{{ $message }}</span>@enderror
                    </div>
                    <div class="vestra-profile-form__field">
                        <label for="edit-username">Username</label>
                        <input id="edit-username" type="text" wire:model="editForm.username" class="@error('editForm.username') is-error @enderror" />
                        @error('editForm.username')<span class="vestra-profile-form__error">{{ $message }}</span>@enderror
                    </div>
                    <div class="vestra-profile-form__field">
                        <label for="edit-phone">Phone Number</label>
                        <input id="edit-phone" type="tel" wire:model="editForm.phone" class="@error('editForm.phone') is-error @enderror" />
                        @error('editForm.phone')<span class="vestra-profile-form__error">{{ $message }}</span>@enderror
                    </div>
                    <div class="vestra-profile-form__field">
                        <label for="edit-avatar">Profile Photo</label>
                        <input id="edit-avatar" type="file" wire:model="avatar" accept="image/png,image/jpeg,image/webp" />
                        @if ($profile['avatar_url'] ?? null)
                            <button type="button" class="vestra-profile__link-danger" wire:click="$set('removeAvatar', true)">Remove current photo</button>
                        @endif
                        @error('avatar')<span class="vestra-profile-form__error">{{ $message }}</span>@enderror
                    </div>
                    <div class="vestra-profile-modal__footer">
                        <button type="button" class="vestra-button vestra-button--secondary" wire:click="closeEditModal">Cancel</button>
                        <button type="submit" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showPasswordModal)
        <div class="vestra-profile-modal" role="dialog" aria-modal="true" aria-label="Change Password">
            <div class="vestra-profile-modal__overlay" wire:click="closePasswordModal"></div>
            <div class="vestra-profile-modal__panel">
                <div class="vestra-profile-modal__header">
                    <h2>Change Password</h2>
                    <button type="button" wire:click="closePasswordModal" aria-label="Close"><x-filament::icon icon="heroicon-o-x-mark" class="h-5 w-5" /></button>
                </div>
                <form wire:submit.prevent="savePassword" class="vestra-profile-modal__body">
                    <div class="vestra-profile-form__field">
                        <label for="current-password">Current Password <span>*</span></label>
                        <input id="current-password" type="password" wire:model="passwordForm.current_password" autocomplete="current-password" class="@error('passwordForm.current_password') is-error @enderror" />
                        @error('passwordForm.current_password')<span class="vestra-profile-form__error">{{ $message }}</span>@enderror
                    </div>
                    <div class="vestra-profile-form__field">
                        <label for="new-password">New Password <span>*</span></label>
                        <input id="new-password" type="{{ $showPassword ? 'text' : 'password' }}" wire:model="passwordForm.password" autocomplete="new-password" class="@error('passwordForm.password') is-error @enderror" />
                        @error('passwordForm.password')<span class="vestra-profile-form__error">{{ $message }}</span>@enderror
                    </div>
                    <div class="vestra-profile-form__field">
                        <label for="confirm-password">Confirm Password <span>*</span></label>
                        <input id="confirm-password" type="{{ $showPasswordConfirmation ? 'text' : 'password' }}" wire:model="passwordForm.password_confirmation" autocomplete="new-password" />
                    </div>
                    <p class="vestra-profile__muted">Minimum 12 characters with upper, lower, number, and symbol.</p>
                    <div class="vestra-profile-modal__footer">
                        <button type="button" class="vestra-button vestra-button--secondary" wire:click="closePasswordModal">Cancel</button>
                        <button type="submit" class="vestra-button vestra-button--primary" wire:loading.attr="disabled">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
