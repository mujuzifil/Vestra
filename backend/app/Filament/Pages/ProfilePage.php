<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\Admin\ProfileAdminService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;

class ProfilePage extends Page
{
    use WithFileUploads;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Profile';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.profile';

    protected static ?string $slug = 'profile';

    public string $activeTab = 'personal';

    public bool $showEditModal = false;

    public bool $showPasswordModal = false;

    /**
     * @var array<string, mixed>
     */
    public array $editForm = [];

    /**
     * @var array<string, mixed>
     */
    public array $passwordForm = [];

    public $avatar = null;

    public bool $removeAvatar = false;

    public bool $showPassword = false;

    public bool $showPasswordConfirmation = false;

    public function getTitle(): string
    {
        return 'My Profile';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
        $this->resetEditForm();
        $this->resetPasswordForm();
    }

    public function getProfileServiceProperty(): ProfileAdminService
    {
        return app(ProfileAdminService::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfileProperty(): array
    {
        return $this->getProfileServiceProperty()->getProfile($this->currentUser());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSessionsProperty(): array
    {
        return $this->getProfileServiceProperty()->listSessions($this->currentUser());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getActivityProperty(): array
    {
        return $this->getProfileServiceProperty()->activityTimeline($this->currentUser());
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['personal', 'sessions', 'activity'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function openEditModal(): void
    {
        $this->resetEditForm();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->avatar = null;
        $this->removeAvatar = false;
    }

    public function openPasswordModal(): void
    {
        $this->resetPasswordForm();
        $this->showPasswordModal = true;
    }

    public function closePasswordModal(): void
    {
        $this->showPasswordModal = false;
        $this->resetPasswordForm();
    }

    public function saveProfile(): void
    {
        $user = $this->currentUser();

        $validated = $this->validate([
            'editForm.name' => ['required', 'string', 'min:2', 'max:255'],
            'editForm.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'editForm.username' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'editForm.phone' => ['nullable', 'string', 'max:40', 'regex:/^[+0-9()\-\s]*$/'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        $this->getProfileServiceProperty()->updateProfile(
            $user,
            $validated['editForm'],
            $this->avatar,
            $this->removeAvatar
        );

        $this->closeEditModal();
        $this->dispatch('profile-updated');

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();
    }

    public function savePassword(): void
    {
        $validated = $this->validate([
            'passwordForm.current_password' => ['required', 'string'],
            'passwordForm.password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
            'passwordForm.password_confirmation' => ['required', 'string'],
        ]);

        try {
            $this->getProfileServiceProperty()->changePassword(
                $this->currentUser(),
                $validated['passwordForm']['current_password'],
                $validated['passwordForm']['password']
            );
        } catch (ValidationException $e) {
            throw $e;
        }

        $user = $this->currentUser()->fresh();

        if (request()->hasSession()) {
            request()->session()->put([
                'password_hash_'.Filament::getAuthGuard() => $user->getAuthPassword(),
            ]);
        }

        $this->closePasswordModal();

        Notification::make()
            ->title('Password changed')
            ->success()
            ->send();
    }

    public function terminateSession(int $sessionId): void
    {
        $this->getProfileServiceProperty()->terminateSession($this->currentUser(), $sessionId);

        Notification::make()
            ->title('Session terminated')
            ->success()
            ->send();
    }

    public function terminateOtherSessions(): void
    {
        $count = $this->getProfileServiceProperty()->terminateOtherSessions($this->currentUser());

        Notification::make()
            ->title($count > 0 ? "Terminated {$count} other session(s)" : 'No other sessions to terminate')
            ->success()
            ->send();
    }

    private function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    private function resetEditForm(): void
    {
        $user = $this->currentUser();
        $this->editForm = [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone,
        ];
        $this->avatar = null;
        $this->removeAvatar = false;
    }

    private function resetPasswordForm(): void
    {
        $this->passwordForm = [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ];
        $this->showPassword = false;
        $this->showPasswordConfirmation = false;
    }
}
