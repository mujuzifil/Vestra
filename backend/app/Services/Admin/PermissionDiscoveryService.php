<?php

namespace App\Services\Admin;

use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Discovers admin modules from the live Filament panel registry and
 * generates only the actions that exist for each module.
 */
class PermissionDiscoveryService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPermissionTree(?string $search = null): array
    {
        $modules = $this->discoverModules();
        $search = filled($search) ? mb_strtolower(trim($search)) : null;

        $tree = [];

        foreach ($modules as $module) {
            $permissions = [];

            foreach ($module['actions'] as $action) {
                $permission = [
                    'name' => $module['key'].'.'.$action['key'],
                    'label' => $action['label'],
                    'action' => $action['key'],
                    'group' => $module['label'],
                    'description' => $action['description'] ?? null,
                ];

                if ($search !== null) {
                    $haystack = mb_strtolower($permission['name'].' '.$permission['label'].' '.$module['label']);
                    if (! str_contains($haystack, $search)) {
                        continue;
                    }
                }

                $permissions[] = $permission;
            }

            if ($permissions === []) {
                continue;
            }

            $tree[] = [
                'key' => $module['key'],
                'label' => $module['label'],
                'group' => $module['navigation_group'] ?? 'General',
                'description' => $module['description'],
                'permissions' => $permissions,
            ];
        }

        usort($tree, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

        return $tree;
    }

    /**
     * Persist discovered permissions into Spatie permission tables.
     *
     * @return array{created: int, updated: int, total: int}
     */
    public function syncToDatabase(string $guard = 'web'): array
    {
        $created = 0;
        $updated = 0;

        foreach ($this->getPermissionTree() as $module) {
            foreach ($module['permissions'] as $permission) {
                $record = Permission::query()->firstOrNew([
                    'name' => $permission['name'],
                    'guard_name' => $guard,
                ]);

                $wasNew = ! $record->exists;
                $record->group = $module['label'];
                $record->save();

                if ($wasNew) {
                    $created++;
                } else {
                    $updated++;
                }
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'total' => $created + $updated,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function discoverModules(): array
    {
        $panel = Filament::getPanel('admin');
        $modules = [];

        foreach ($panel->getPages() as $pageClass) {
            if (! is_subclass_of($pageClass, Page::class)) {
                continue;
            }

        if ($pageClass === \App\Filament\Pages\ForcePasswordChange::class
            || $pageClass === \App\Filament\Pages\Administration\StaffFormPage::class
            || $pageClass === \App\Filament\Pages\Administration\RoleFormPage::class
            || $pageClass === \App\Filament\Pages\UnauthorizedAccess::class
            || $pageClass === \App\Filament\Pages\ProfilePage::class) {
            continue;
        }

            if (! $pageClass::shouldRegisterNavigation()) {
                continue;
            }

            $label = (string) ($pageClass::getNavigationLabel() ?: class_basename($pageClass));
            $key = Str::slug($label);
            if ($key === '') {
                continue;
            }

            $modules[$key] = [
                'key' => $key,
                'label' => $label,
                'navigation_group' => $pageClass::getNavigationGroup(),
                'description' => 'Manage '.$label.' and related data.',
                'page' => $pageClass,
                'actions' => $this->discoverActionsForPage($pageClass, $label, $key),
            ];
        }

        // Ensure Roles/Staff appear even if page discovery order varies.
        return array_values($modules);
    }

    /**
     * @param  class-string<Page>  $pageClass
     * @return array<int, array{key: string, label: string, description?: string}>
     */
    private function discoverActionsForPage(string $pageClass, string $label, string $key): array
    {
        $actions = [
            ['key' => 'view', 'label' => 'View '.$label, 'description' => 'Access the '.$label.' workspace'],
        ];

        $ref = new \ReflectionClass($pageClass);
        $methods = collect($ref->getMethods(\ReflectionMethod::IS_PUBLIC))
            ->map(fn (\ReflectionMethod $method) => $method->getName())
            ->all();

        $methodMap = [
            'create' => ['openCreateModal', 'create', 'saveDraft', 'createStaff', 'createProduct', 'createPost', 'uploadAsset'],
            'edit' => ['openEditModal', 'update', 'save', 'saveProduct', 'saveMetadata', 'updatePost', 'activatePartner', 'updateCoverage', 'updateProfile'],
            'delete' => ['delete', 'deleteSelected', 'deleteArticle', 'deleteStaff', 'removeImage'],
            'export' => ['getExportUrl'],
            'approve' => ['approve', 'approveSelected'],
            'reject' => ['reject', 'rejectSelected'],
            'publish' => ['publish', 'saveDraft'],
            'archive' => ['archive', 'archiveSelected'],
            'suspend' => ['suspendPartner', 'suspend'],
        ];

        foreach ($methodMap as $action => $candidates) {
            if ($action === 'view') {
                continue;
            }

            $found = false;
            foreach ($candidates as $candidate) {
                if (in_array($candidate, $methods, true)) {
                    $found = true;
                    break;
                }
            }

            // Route-based export discovery
            if ($action === 'export' && ! $found) {
                $found = $this->hasExportRoute($key);
            }

            if ($found) {
                $actions[] = [
                    'key' => $action,
                    'label' => Str::headline($action).' '.$label,
                    'description' => Str::headline($action).' records in '.$label,
                ];
            }
        }

        // Policy-backed modules: if page exposes a model via common property patterns, merge policy abilities.
        $model = $this->guessModelForPage($pageClass);
        if ($model) {
            foreach (['viewAny' => 'view', 'create' => 'create', 'update' => 'edit', 'delete' => 'delete'] as $ability => $actionKey) {
                try {
                    if (Gate::getPolicyFor($model) && method_exists(Gate::getPolicyFor($model), $ability)) {
                        if (! collect($actions)->contains(fn ($a) => $a['key'] === $actionKey)) {
                            $actions[] = [
                                'key' => $actionKey,
                                'label' => Str::headline($actionKey).' '.$label,
                            ];
                        }
                    }
                } catch (\Throwable) {
                    // Ignore policy resolution failures during discovery.
                }
            }
        }

        // Deduplicate by action key
        $unique = [];
        foreach ($actions as $action) {
            $unique[$action['key']] = $action;
        }

        return array_values($unique);
    }

    private function hasExportRoute(string $moduleKey): bool
    {
        $needle = str_replace('-', '.', $moduleKey);
        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();
            if ($name !== '' && str_contains($name, 'export') && str_contains($name, $needle)) {
                return true;
            }
            if ($name !== '' && str_contains($name, 'export') && str_contains($name, $moduleKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  class-string<Page>  $pageClass
     * @return class-string|null
     */
    private function guessModelForPage(string $pageClass): ?string
    {
        $map = [
            'StaffPage' => \App\Models\User::class,
            'RolesPage' => \Spatie\Permission\Models\Role::class,
            'ProductsPage' => \App\Models\Product::class,
            'CategoriesPage' => \App\Models\Category::class,
            'BlogPage' => \App\Models\BlogPost::class,
            'BlogArticlePage' => \App\Models\BlogPost::class,
            'MediaPage' => \App\Models\MediaAsset::class,
            'CompaniesPage' => \App\Models\CompanyProfile::class,
            'SupportPage' => \App\Models\SupportTicket::class,
            'EnquiriesPage' => \App\Models\ContactMessage::class,
            'FeedbackPage' => \App\Models\CustomerFeedback::class,
            'ApplicationsPage' => \App\Models\DistributorRequest::class,
            'ActivePartnersPage' => \App\Models\Distributor::class,
            'TerritoriesPage' => \App\Models\DistributorBranch::class,
            'CreditPage' => \App\Models\CreditAccount::class,
        ];

        $base = class_basename($pageClass);

        return $map[$base] ?? null;
    }
}
