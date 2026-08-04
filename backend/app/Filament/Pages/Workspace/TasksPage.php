<?php

namespace App\Filament\Pages\Workspace;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use App\Services\Admin\TaskService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class TasksPage extends Page
{
    use WithPagination;

    protected static string $layout = 'filament.layouts.crm';

    protected static ?string $navigationGroup = 'Workspace';

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.workspace.tasks';

    protected static ?string $slug = 'tasks';

    #[Url(as: 'search')]
    public string $search = '';

    #[Url(as: 'status')]
    public array $statusFilter = [];

    #[Url(as: 'priority')]
    public array $priorityFilter = [];

    #[Url(as: 'assignee')]
    public ?int $assigneeFilter = null;

    #[Url(as: 'due_from')]
    public ?string $dueFrom = null;

    #[Url(as: 'due_until')]
    public ?string $dueUntil = null;

    #[Url(as: 'sort')]
    public string $sortField = 'due_date';

    #[Url(as: 'direction')]
    public string $sortDirection = 'asc';

    public int $perPage = 15;

    public bool $showDrawer = false;

    public ?int $editingTaskId = null;

    /**
     * @var array<string, mixed>
     */
    public array $form = [
        'title' => '',
        'description' => '',
        'status' => '',
        'priority' => '',
        'assignee_id' => null,
        'related_type' => null,
        'related_id' => null,
        'due_date' => null,
        'internal_notes' => '',
    ];

    public function getTitle(): string
    {
        return 'Tasks';
    }

    public function mount(): void
    {
        Gate::authorize('viewAny', Task::class);
    }

    public function getTaskServiceProperty(): TaskService
    {
        return app(TaskService::class);
    }

    public function getTasksProperty(): mixed
    {
        $filters = $this->buildFilters();

        return $this->getTaskServiceProperty()
            ->paginateTasks($filters, $this->sortField, $this->sortDirection, $this->perPage);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getKpiCardsProperty(): array
    {
        return $this->getTaskServiceProperty()->getKpiCards();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAssigneesProperty(): array
    {
        return User::query()
            ->where('is_admin', true)
            ->orWhereHas('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->name, 'initials' => $user->initials()])
            ->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFilters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->statusFilter,
            'priority' => $this->priorityFilter,
            'assignee' => $this->assigneeFilter,
            'due_from' => $this->dueFrom,
            'due_until' => $this->dueUntil,
        ];
    }

    public function openCreateDrawer(): void
    {
        Gate::authorize('create', Task::class);

        $this->resetForm();
        $this->editingTaskId = null;
        $this->showDrawer = true;
    }

    public function openEditDrawer(int $id): void
    {
        $task = Task::query()->findOrFail($id);

        Gate::authorize('update', $task);

        $this->editingTaskId = $task->id;
        $this->form = [
            'title' => $task->title,
            'description' => $task->description ?? '',
            'status' => $task->status->value,
            'priority' => $task->priority->value,
            'assignee_id' => $task->assignee_id,
            'related_type' => $task->related_type,
            'related_id' => $task->related_id,
            'due_date' => $task->due_date?->format('Y-m-d\TH:i'),
            'internal_notes' => $task->internal_notes ?? '',
        ];
        $this->showDrawer = true;
    }

    public function closeDrawer(): void
    {
        $this->showDrawer = false;
        $this->editingTaskId = null;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->form = [
            'title' => '',
            'description' => '',
            'status' => TaskStatus::NEW->value,
            'priority' => TaskPriority::MEDIUM->value,
            'assignee_id' => null,
            'related_type' => null,
            'related_id' => null,
            'due_date' => null,
            'internal_notes' => '',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'form.title' => ['required', 'string', 'max:255'],
            'form.description' => ['nullable', 'string'],
            'form.status' => ['required', 'string', 'in:'.implode(',', array_map(fn ($s) => $s->value, TaskStatus::cases()))],
            'form.priority' => ['required', 'string', 'in:'.implode(',', array_map(fn ($p) => $p->value, TaskPriority::cases()))],
            'form.assignee_id' => ['nullable', 'integer', 'exists:users,id'],
            'form.related_type' => ['nullable', 'string'],
            'form.related_id' => ['nullable', 'integer'],
            'form.due_date' => ['nullable', 'date'],
            'form.internal_notes' => ['nullable', 'string'],
        ];
    }

    public function saveTask(): void
    {
        $this->validate();

        $service = $this->getTaskServiceProperty();
        $data = $this->form;

        if ($this->editingTaskId) {
            $task = Task::query()->findOrFail($this->editingTaskId);
            Gate::authorize('update', $task);
            $service->updateTask($task, $data);
        } else {
            Gate::authorize('create', Task::class);
            $service->createTask(Auth::user(), $data);
        }

        $this->closeDrawer();
        $this->resetPage();
    }

    public function deleteTask(int $id): void
    {
        $task = Task::query()->findOrFail($id);
        Gate::authorize('delete', $task);

        $this->getTaskServiceProperty()->deleteTask($task);
    }

    public function completeTask(int $id): void
    {
        $task = Task::query()->findOrFail($id);
        Gate::authorize('complete', $task);

        $this->getTaskServiceProperty()->completeTask($task);
    }

    public function archiveTask(int $id): void
    {
        $task = Task::query()->findOrFail($id);
        Gate::authorize('archive', $task);

        $this->getTaskServiceProperty()->archiveTask($task);
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
        $this->priorityFilter = [];
        $this->assigneeFilter = null;
        $this->dueFrom = null;
        $this->dueUntil = null;
        $this->sortField = 'due_date';
        $this->sortDirection = 'asc';
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

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssigneeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDueFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDueUntil(): void
    {
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search)
            || filled($this->statusFilter)
            || filled($this->priorityFilter)
            || filled($this->assigneeFilter)
            || filled($this->dueFrom)
            || filled($this->dueUntil);
    }

    public function getExportUrl(string $format): string
    {
        return route('filament.admin.workspace.tasks.export', [
            'format' => $format,
            'search' => $this->search ?: null,
            'status' => $this->statusFilter ?: null,
            'priority' => $this->priorityFilter ?: null,
            'assignee' => $this->assigneeFilter,
            'due_from' => $this->dueFrom,
            'due_until' => $this->dueUntil,
            'sort' => $this->sortField,
            'direction' => $this->sortDirection,
        ]);
    }
}
