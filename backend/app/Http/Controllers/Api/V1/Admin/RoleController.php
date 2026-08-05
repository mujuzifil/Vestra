<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\Admin\PermissionDiscoveryService;
use App\Services\Admin\RoleAdminService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly RoleAdminService $roles,
        private readonly PermissionDiscoveryService $permissions
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Role::class);

        $paginator = $this->roles->paginateRoles(
            [
                'search' => $request->query('search'),
                'type' => $request->query('type'),
                'status' => $request->query('status'),
            ],
            (string) $request->query('sort', 'name'),
            (string) $request->query('direction', 'asc'),
            (int) $request->query('per_page', 15)
        );

        return $this->successResponse($paginator);
    }

    public function show(Role $role): JsonResponse
    {
        Gate::authorize('view', $role);

        return $this->successResponse($this->roles->getDetail($role));
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Role::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:roles,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'disabled'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role = $this->roles->createRole($data, $data['permissions'] ?? [], $request->user());

        return $this->successResponse($this->roles->getDetail($role), 'Role created.', 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        Gate::authorize('update', $role);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('roles', 'slug')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'disabled'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $updated = $this->roles->updateRole($role, $data, $data['permissions'] ?? [], $request->user());

        return $this->successResponse($this->roles->getDetail($updated), 'Role updated.');
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        Gate::authorize('delete', $role);
        $this->roles->deleteRole($role, $request->user());

        return $this->successResponse(null, 'Role deleted.');
    }

    public function updateStatus(Request $request, Role $role): JsonResponse
    {
        Gate::authorize('update', $role);

        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'disabled'])],
        ]);

        $updated = $this->roles->setStatus($role, $data['status'], $request->user());

        return $this->successResponse($this->roles->getDetail($updated), 'Role status updated.');
    }

    public function permissionTree(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Role::class);

        return $this->successResponse(
            $this->permissions->getPermissionTree($request->query('search'))
        );
    }

    public function assignedUsers(Role $role): JsonResponse
    {
        Gate::authorize('view', $role);

        return $this->successResponse($this->roles->getDetail($role)['users'] ?? []);
    }

    public function audit(Role $role): JsonResponse
    {
        Gate::authorize('view', $role);

        return $this->successResponse($this->roles->getDetail($role)['audit'] ?? []);
    }
}
