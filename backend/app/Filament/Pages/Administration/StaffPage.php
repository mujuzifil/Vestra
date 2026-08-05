<?php

namespace App\Filament\Pages\Administration;

use App\Models\User;
use App\Services\Admin\StaffAdminService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class StaffPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Staff';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.administration.staff';

    protected static ?string $slug = 'administration/staff';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'role')]
    public array $roleFilter = [];

    #[Url(as: 'sort')]
    public string $sortField = 'created_at';

    #[Url(as: 'direction')]
    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public bool $showDetailDrawer = false;

    public ?int $selectedStaffId = null;

    public function getTitle(): string
    {
        return 'Staff';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public static function canAccess(): bool
    {
        return Gate::allows('viewAny', User::class);
    }

    public function getStaffServiceProperty(): StaffAdminService
    {
        return app(StaffAdminService::class);
    }

    public function getStaffProperty(): mixed
    {
        return $this->getStaffServiceProperty()
            ->paginateStaff($this->buildFilters(), $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getStaffServiceProperty()->getKpiCards();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSelectedStaffProperty(): ?array
    {
        if (empty($this->selectedStaffId)) {
            return null;
        }

        $staff = User::query()->where('is_admin', true)->find($this->selectedStaffId);

        if ($staff === null) {
            return null;
        }

        Gate::authorize('view', $staff);

        return $this->getStaffServiceProperty()->getDetail($staff);
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilterOptionsProperty(): array
    {
        return $this->getStaffServiceProperty()->getFilterOptions();
    }

    public function getCanCreateProperty(): bool
    {
        return Gate::allows('create', User::class);
    }

    public function getCreateUrlProperty(): string
    {
        return StaffFormPage::getUrl();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'role' => $this->roleFilter,
        ];
    }

    public function openDetailDrawer(int $id): void
    {
        $staff = User::query()->where('is_admin', true)->findOrFail($id);
        Gate::authorize('view', $staff);

        $this->selectedStaffId = $id;
        $this->showDetailDrawer = true;
    }

    public function closeDetailDrawer(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedStaffId = null;
    }

    public function disableStaff(int $id): void
    {
        $this->mutateStaff($id, fn (User $staff, User $actor) => $this->getStaffServiceProperty()->setStatus($staff, 'inactive', $actor), 'Account disabled');
    }

    public function enableStaff(int $id): void
    {
        $this->mutateStaff($id, fn (User $staff, User $actor) => $this->getStaffServiceProperty()->setStatus($staff, 'active', $actor), 'Account enabled');
    }

    public function resetStaffPassword(int $id): void
    {
        $this->mutateStaff($id, function (User $staff, User $actor): void {
            $this->getStaffServiceProperty()->resetPassword($staff, $actor);
        }, 'Temporary password emailed');
    }

    public function forceStaffPasswordChange(int $id): void
    {
        $this->mutateStaff($id, fn (User $staff, User $actor) => $this->getStaffServiceProperty()->forcePasswordChange($staff, $actor), 'Password change required on next login');
    }

    public function lockStaff(int $id): void
    {
        $this->mutateStaff($id, fn (User $staff, User $actor) => $this->getStaffServiceProperty()->lock($staff, $actor), 'Account locked');
    }

    public function unlockStaff(int $id): void
    {
        $this->mutateStaff($id, fn (User $staff, User $actor) => $this->getStaffServiceProperty()->unlock($staff, $actor), 'Account unlocked');
    }

    public function resendStaffWelcome(int $id): void
    {
        $this->mutateStaff($id, function (User $staff, User $actor): void {
            $this->getStaffServiceProperty()->resendWelcome($staff, $actor);
        }, 'Welcome email resent');
    }

    public function deleteStaff(int $id): void
    {
        $staff = User::query()->where('is_admin', true)->findOrFail($id);
        Gate::authorize('delete', $staff);

        $this->getStaffServiceProperty()->deleteStaff($staff, auth()->user());
        $this->closeDetailDrawer();

        Notification::make()->title('Staff deleted')->success()->send();
    }

    /**
     * @param  callable(User, User): mixed  $callback
     */
    private function mutateStaff(int $id, callable $callback, string $successMessage): void
    {
        $staff = User::query()->where('is_admin', true)->findOrFail($id);
        Gate::authorize('update', $staff);
        $actor = auth()->user();
        $callback($staff, $actor);

        Notification::make()->title($successMessage)->success()->send();
        $this->selectedStaffId = $id;
        $this->showDetailDrawer = true;
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = [];
        $this->roleFilter = [];
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->roleFilter);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.administration.staff.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'role' => $this->roleFilter ?: null,
        ]);
    }
}
