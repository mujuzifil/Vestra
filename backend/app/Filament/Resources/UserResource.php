<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Services\AuditService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('roles')
            ->where('is_admin', true);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->default(now()),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required()
                            ->default('active'),

                        Forms\Components\Toggle::make('is_admin')
                            ->default(true)
                            ->required()
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Security')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255)
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),

                        Forms\Components\DateTimePicker::make('force_password_change_at')
                            ->label('Force password change at')
                            ->nullable(),

                        Forms\Components\Placeholder::make('last_login_at')
                            ->label('Last login')
                            ->content(fn (?User $record): string => $record?->last_login_at?->diffForHumans() ?? 'Never'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Roles')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name', fn (Builder $query) => $query->whereIn('name', ['Super Administrator', 'Administrator', 'Manager']))
                            ->preload()
                            ->searchable(),
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

                Tables\Columns\TextColumn::make('initials')
                    ->label('')
                    ->getStateUsing(fn (User $record): string => $record->initials())
                    ->icon('heroicon-o-user')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Last login')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),

                Tables\Columns\IconColumn::make('force_password_change_at')
                    ->label('PW reset pending')
                    ->boolean()
                    ->getStateUsing(fn (User $record): bool => $record->force_password_change_at !== null && $record->force_password_change_at->isFuture()),

                Tables\Columns\IconColumn::make('two_factor_enabled')
                    ->label('2FA')
                    ->boolean()
                    ->getStateUsing(fn (): bool => false)
                    ->tooltip('Two-factor authentication is not enabled.'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->native(false),

                Tables\Filters\TernaryFilter::make('force_password_change')
                    ->label('Password reset pending')
                    ->placeholder('All users')
                    ->trueLabel('Pending')
                    ->falseLabel('Not pending')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('force_password_change_at')->where('force_password_change_at', '>', now()),
                        false: fn (Builder $query) => $query->whereNull('force_password_change_at'),
                    ),

                Tables\Filters\Filter::make('last_login')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Logged in since'),
                        Forms\Components\DatePicker::make('until')->label('Logged in until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('last_login_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('last_login_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (User $record) {
                        AuditService::log(
                            auth()->user(),
                            'administrator.updated',
                            $record,
                            ['email' => $record->email, 'status' => $record->status]
                        );
                    }),

                Tables\Actions\Action::make('resetPassword')
                    ->label('Reset password')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->confirmed(),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required(),
                        Forms\Components\Toggle::make('force_password_change')
                            ->label('Force password change on next login')
                            ->default(false),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update([
                            'password' => Hash::make($data['password']),
                            'force_password_change_at' => $data['force_password_change'] ? now() : null,
                        ]);

                        AuditService::log(
                            auth()->user(),
                            'administrator.password_reset',
                            $record,
                            ['force_change' => $data['force_password_change'] ?? false]
                        );
                    }),

                Tables\Actions\Action::make('toggleStatus')
                    ->label(fn (User $record): string => $record->status === 'active' ? 'Deactivate' : 'Activate')
                    ->icon(fn (User $record): string => $record->status === 'active' ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn (User $record): string => $record->status === 'active' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $previousStatus = $record->status;
                        $newStatus = $previousStatus === 'active' ? 'inactive' : 'active';
                        $record->update(['status' => $newStatus]);

                        AuditService::log(
                            auth()->user(),
                            "administrator.{$newStatus}",
                            $record,
                            ['previous_status' => $previousStatus]
                        );
                    })
                    ->hidden(fn (User $record): bool => $record->id === auth()->id()),

                Tables\Actions\DeleteAction::make()
                    ->before(function (User $record) {
                        AuditService::log(
                            auth()->user(),
                            'administrator.deleted',
                            $record,
                            ['email' => $record->email]
                        );
                    })
                    ->hidden(fn (User $record): bool => $record->id === auth()->id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::bulkUpdateStatus($records, 'active')),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::bulkUpdateStatus($records, 'inactive')),

                    Tables\Actions\BulkAction::make('forcePasswordChange')
                        ->label('Force password change')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                if ($record->id === auth()->id()) {
                                    continue;
                                }

                                $record->update(['force_password_change_at' => now()]);

                                AuditService::log(
                                    auth()->user(),
                                    'administrator.force_password_change',
                                    $record,
                                    ['email' => $record->email]
                                );
                            }
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->id === auth()->id()) {
                                    continue;
                                }

                                AuditService::log(
                                    auth()->user(),
                                    'administrator.deleted',
                                    $record,
                                    ['email' => $record->email]
                                );
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    protected static function bulkUpdateStatus(Collection $records, string $status): void
    {
        foreach ($records as $record) {
            if ($record->id === auth()->id()) {
                continue;
            }

            $previousStatus = $record->status;
            $record->update(['status' => $status]);

            AuditService::log(
                auth()->user(),
                "administrator.{$status}",
                $record,
                ['previous_status' => $previousStatus]
            );
        }
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
