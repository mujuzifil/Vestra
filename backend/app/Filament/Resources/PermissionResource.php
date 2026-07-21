<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Permissions';

    protected static ?string $modelLabel = 'Permission';

    protected static ?string $pluralModelLabel = 'Permissions';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('roles');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Select::make('group')
                    ->options([
                        'Administration' => 'Administration',
                        'Customers' => 'Customers',
                        'Products' => 'Products',
                        'Orders' => 'Orders',
                        'Reports' => 'Reports',
                        'Settings' => 'Settings',
                        'Notifications' => 'Notifications',
                    ])
                    ->native(false),

                Forms\Components\TextInput::make('guard_name')
                    ->required()
                    ->default('web')
                    ->maxLength(255)
                    ->disabled(),
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

                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Administration' => 'primary',
                        'Customers' => 'success',
                        'Products' => 'warning',
                        'Orders' => 'info',
                        'Reports' => 'danger',
                        'Settings' => 'gray',
                        'Notifications' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles_count')
                    ->label('Roles using this')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('guard_name')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options([
                        'Administration' => 'Administration',
                        'Customers' => 'Customers',
                        'Products' => 'Products',
                        'Orders' => 'Orders',
                        'Reports' => 'Reports',
                        'Settings' => 'Settings',
                        'Notifications' => 'Notifications',
                    ])
                    ->multiple()
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (Permission $record) {
                        AuditService::log(
                            auth()->user(),
                            'permission.updated',
                            $record,
                            ['name' => $record->name, 'group' => $record->group]
                        );
                    }),

                Tables\Actions\DeleteAction::make()
                    ->before(function (Permission $record) {
                        AuditService::log(
                            auth()->user(),
                            'permission.deleted',
                            $record,
                            ['name' => $record->name]
                        );
                    })
                    ->hidden(fn (Permission $record): bool => $record->roles()->count() > 0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->roles()->count() > 0) {
                                    continue;
                                }

                                AuditService::log(
                                    auth()->user(),
                                    'permission.deleted',
                                    $record,
                                    ['name' => $record->name]
                                );
                            }
                        }),
                ]),
            ])
            ->defaultSort('group')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
