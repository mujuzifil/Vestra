<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\PermissionDiscoveryService;
use App\Services\Admin\StaffAdminService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly StaffAdminService $staff,
        private readonly PermissionDiscoveryService $permissions
    ) {}

    public function roles(): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $roles = Role::query()
            ->where('name', '!=', 'customer')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return $this->successResponse($roles);
    }

    public function permissions(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        return $this->successResponse(
            $this->permissions->getPermissionTree($request->query('search'))
        );
    }

    public function permissionTree(Request $request): JsonResponse
    {
        return $this->permissions($request);
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $paginator = $this->staff->paginateStaff(
            [
                'search' => $request->query('search'),
                'status' => array_filter((array) $request->query('status', [])),
                'role' => array_filter((array) $request->query('role', [])),
            ],
            (string) $request->query('sort', 'created_at'),
            (string) $request->query('direction', 'desc'),
            (int) $request->query('per_page', 15)
        );

        return $this->successResponse($paginator);
    }

    public function show(User $staff): JsonResponse
    {
        abort_unless($staff->is_admin, 404);
        Gate::authorize('view', $staff);

        return $this->successResponse($this->staff->getDetail($staff));
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:users,username'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'department' => ['nullable', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'employee_id' => ['nullable', 'string', 'max:60', 'unique:users,employee_id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $result = $this->staff->createStaff(
            $data,
            $data['permissions'] ?? [],
            null,
            $request->user()
        );

        return $this->successResponse([
            'user' => $this->staff->getDetail($result['user']),
            // Temporary password is emailed — never returned in API payloads.
        ], 'Staff created.', 201);
    }

    public function update(Request $request, User $staff): JsonResponse
    {
        abort_unless($staff->is_admin, 404);
        Gate::authorize('update', $staff);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($staff->id)],
            'username' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore($staff->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'department' => ['nullable', 'string', 'max:120'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'employee_id' => ['nullable', 'string', 'max:60', Rule::unique('users', 'employee_id')->ignore($staff->id)],
            'notes' => ['nullable', 'string', 'max:500'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $updated = $this->staff->updateStaff(
            $staff,
            $data,
            $data['permissions'] ?? [],
            null,
            false,
            $request->user()
        );

        return $this->successResponse($this->staff->getDetail($updated), 'Staff updated.');
    }

    public function updateStatus(Request $request, User $staff): JsonResponse
    {
        abort_unless($staff->is_admin, 404);
        Gate::authorize('update', $staff);

        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $updated = $this->staff->setStatus($staff, $data['status'], $request->user());

        return $this->successResponse($this->staff->getDetail($updated), 'Staff status updated.');
    }

    public function resetPassword(Request $request, User $staff): JsonResponse
    {
        abort_unless($staff->is_admin, 404);
        Gate::authorize('update', $staff);

        $this->staff->resetPassword($staff, $request->user());

        return $this->successResponse(null, 'Temporary password emailed.');
    }

    public function destroy(Request $request, User $staff): JsonResponse
    {
        abort_unless($staff->is_admin, 404);
        Gate::authorize('delete', $staff);

        $this->staff->deleteStaff($staff, $request->user());

        return $this->successResponse(null, 'Staff deleted.');
    }

    public function audit(User $staff): JsonResponse
    {
        abort_unless($staff->is_admin, 404);
        Gate::authorize('view', $staff);

        $detail = $this->staff->getDetail($staff);

        return $this->successResponse($detail['audit'] ?? []);
    }
}
