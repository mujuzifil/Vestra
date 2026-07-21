<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\User;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $modelLabel = 'Role';

    protected static ?string $pluralModelLabel = 'Roles';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('permissions')
            ->withCount('users');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn (?Role $record): bool => $record !== null && self::isSystemRole($record)),

                        Forms\Components\Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->options(fn (): array => Permission::orderBy('group')->orderBy('name')->get()
                                ->mapWithKeys(fn (Permission $permission): array => [
                                    $permission->id => $permission->name,
                                ])
                                ->toArray())
                            ->descriptions(fn (): array => Permission::all()->mapWithKeys(fn (Permission $permission): array => [
                                $permission->id => $permission->group ?? 'General',
                            ])->toArray())
                            ->columns(2)
                            ->gridDirection('row')
                            ->bulkToggleable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-medium'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(60)
                    ->toggleable()
                    ->placeholder('No description'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users assigned')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->getStateUsing(fn (Role $record): int => $record->permissions->count())
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_users')
                    ->label('Has users assigned')
                    ->query(fn (Builder $query): Builder => $query->has('users')),

                Tables\Filters\Filter::make('no_users')
                    ->label('No users assigned')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('users')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (Role $record) {
                        AuditService::log(
                            auth()->user(),
                            'role.updated',
                            $record,
                            ['name' => $record->name, 'permissions' => $record->permissions->pluck('name')]
                        );
                    })
                    ->hidden(fn (Role $record): bool => self::isSystemRole($record)),

                Tables\Actions\Action::make('clone')
                    ->label('Clone')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique('roles', 'name')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->rows(2),
                    ])
                    ->action(function (Role $record, array $data): void {
                        $clone = Role::create([
                            'name' => $data['name'],
                            'guard_name' => $record->guard_name,
                            'description' => $data['description'] ?? null,
                        ]);

                        $clone->syncPermissions($record->permissions->pluck('name'));

                        AuditService::log(
                            auth()->user(),
                            'role.cloned',
                            $clone,
                            ['source_role' => $record->name]
                        );

                        Notification::make()
                            ->title('Role cloned')
                            ->body("{$data['name']} was created from {$record->name}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (Role $record) {
                        AuditService::log(
                            auth()->user(),
                            'role.deleted',
                            $record,
                            ['name' => $record->name]
                        );
                    })
                    ->hidden(fn (Role $record): bool => self::isSystemRole($record) || $record->users()->count() > 0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if (self::isSystemRole($record) || $record->users()->count() > 0) {
                                    continue;
                                }

                                AuditService::log(
                                    auth()->user(),
                                    'role.deleted',
                                    $record,
                                    ['name' => $record->name]
                                );
                            }
                        }),
                ]),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected static function isSystemRole(Role $record): bool
    {
        return in_array($record->name, ['Super Administrator', 'Administrator', 'Manager', 'customer'], true);
    }
}
