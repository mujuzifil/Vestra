<?php

namespace App\Filament\Pages\Administration;

use App\Filament\Resources\AdminSessionResource;
use App\Filament\Resources\AuditLogResource;
use App\Filament\Resources\LoginActivityResource;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\UserResource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class AdministrationDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Administration';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'administration';

    protected static string $view = 'filament.pages.administration.administration-dashboard';

    public function getTitle(): string
    {
        return 'Administration Platform';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('term')
                    ->label('Search administration')
                    ->placeholder('Search users, roles, permissions, audit logs...')
                    ->prefixIcon('heroicon-o-magnifying-glass')
                    ->live(debounce: 300)
                    ->extraAlpineAttributes([
                        '@keydown.enter' => '$wire.searchAdministration()',
                    ]),
            ])
            ->statePath('data');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function searchAdministration(): void
    {
        $term = $this->data['term'] ?? '';

        if (blank($term)) {
            return;
        }

        $this->redirect(UserResource::getUrl('index', ['tableSearch' => $term]));
    }

    public function getSearchTargets(string $term): array
    {
        return [
            [
                'label' => 'Users',
                'route' => UserResource::getUrl('index', ['tableSearch' => $term]),
                'icon' => 'heroicon-o-users',
            ],
            [
                'label' => 'Roles',
                'route' => RoleResource::getUrl('index', ['tableSearch' => $term]),
                'icon' => 'heroicon-o-shield-check',
            ],
            [
                'label' => 'Permissions',
                'route' => PermissionResource::getUrl('index', ['tableSearch' => $term]),
                'icon' => 'heroicon-o-key',
            ],
            [
                'label' => 'Audit Logs',
                'route' => AuditLogResource::getUrl('index', ['tableSearch' => $term]),
                'icon' => 'heroicon-o-clipboard-document-list',
            ],
        ];
    }

    public function getAdminLinks(): array
    {
        return [
            [
                'label' => 'Users',
                'description' => 'Manage administrators, roles, and account status.',
                'icon' => 'heroicon-o-users',
                'route' => UserResource::getUrl(),
                'color' => 'primary',
            ],
            [
                'label' => 'Roles',
                'description' => 'Define roles, clone existing roles, and assign permissions.',
                'icon' => 'heroicon-o-shield-check',
                'route' => RoleResource::getUrl(),
                'color' => 'info',
            ],
            [
                'label' => 'Permissions',
                'description' => 'Browse permissions grouped by functional domain.',
                'icon' => 'heroicon-o-key',
                'route' => PermissionResource::getUrl(),
                'color' => 'success',
            ],
            [
                'label' => 'Audit Logs',
                'description' => 'Review administrator actions, changes, and access history.',
                'icon' => 'heroicon-o-clipboard-document-list',
                'route' => AuditLogResource::getUrl(),
                'color' => 'warning',
            ],
            [
                'label' => 'Login Activity',
                'description' => 'Successful and failed administrator login attempts.',
                'icon' => 'heroicon-o-arrow-right-on-rectangle',
                'route' => LoginActivityResource::getUrl(),
                'color' => 'danger',
            ],
            [
                'label' => 'Sessions',
                'description' => 'View and terminate active administrator sessions.',
                'icon' => 'heroicon-o-computer-desktop',
                'route' => AdminSessionResource::getUrl(),
                'color' => 'primary',
            ],
            [
                'label' => 'Security Policies',
                'description' => 'Password policy, session timeout, and login limits.',
                'icon' => 'heroicon-o-lock-closed',
                'route' => SecurityPolicies::getUrl(),
                'color' => 'danger',
            ],
            [
                'label' => 'System Health',
                'description' => 'Database, cache, queue, storage, and mail connectivity.',
                'icon' => 'heroicon-o-heart',
                'route' => SystemHealth::getUrl(),
                'color' => 'success',
            ],
            [
                'label' => 'API Tokens',
                'description' => 'Administrator API token management. (Coming soon)',
                'icon' => 'heroicon-o-code-bracket-square',
                'route' => '#',
                'color' => 'gray',
                'disabled' => true,
            ],
        ];
    }
}
