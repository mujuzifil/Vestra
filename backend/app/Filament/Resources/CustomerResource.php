<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'E-Commerce';
    protected static ?string $navigationLabel = 'Customers';
    protected static ?string $label = 'Customer';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Information')
                    ->icon('heroicon-o-user')
                    ->description('Basic contact and account information.')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                        Forms\Components\TextInput::make('phone')->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required()
                            ->default('active')
                            ->native(false),
                        Forms\Components\DateTimePicker::make('email_verified_at')->label('Email Verified'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ViewColumn::make('initials')
                    ->label('')
                    ->view('filament.tables.columns.customer-avatar')
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->weight('font-semibold')
                    ->color('primary')
                    ->description(fn (User $record): string => $record->email),

                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('No phone')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M d, Y')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Lifetime Orders')
                    ->badge()
                    ->color('gray')
                    ->alignment('center')
                    ->sortable(),

                Tables\Columns\TextColumn::make('orders_sum_total_amount')
                    ->label('Lifetime Spend')
                    ->money('UGX')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('orders_max_created_at')
                    ->label('Last Order')
                    ->since()
                    ->sortable()
                    ->placeholder('Never ordered'),

                Tables\Columns\BadgeColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('query')
                            ->label('Search')
                            ->placeholder('Name, email, phone...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $term = $data['query'] ?? null;
                        if (! $term) {
                            return $query;
                        }
                        return $query->where(function (Builder $q) use ($term) {
                            $q->where('name', 'like', "%{$term}%")
                              ->orWhere('email', 'like', "%{$term}%")
                              ->orWhere('phone', 'like', "%{$term}%");
                        });
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),

                Tables\Filters\Filter::make('registered_at')
                    ->label('Registration Date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query->registeredBetween($data['from'] ?? null, $data['until'] ?? null)),

                Tables\Filters\Filter::make('has_orders')
                    ->label('Has Orders')
                    ->query(fn (Builder $query): Builder => $query->hasOrders())
                    ->toggle(),

                Tables\Filters\Filter::make('no_orders')
                    ->label('No Orders')
                    ->query(fn (Builder $query): Builder => $query->hasNoOrders())
                    ->toggle(),

                Tables\Filters\Filter::make('high_value')
                    ->label('High Value')
                    ->query(fn (Builder $query): Builder => $query->highValue(200000))
                    ->toggle(),

                Tables\Filters\Filter::make('recently_registered')
                    ->label('Recently Registered')
                    ->query(fn (Builder $query): Builder => $query->recentlyRegistered(7))
                    ->toggle(),

                Tables\Filters\Filter::make('recently_active')
                    ->label('Recently Active')
                    ->query(fn (Builder $query): Builder => $query->recentlyActive(30))
                    ->toggle(),

                Tables\Filters\Filter::make('lifetime_spend')
                    ->label('Lifetime Spend')
                    ->form([
                        Forms\Components\TextInput::make('min')->label('Min')->numeric()->prefix('UGX'),
                        Forms\Components\TextInput::make('max')->label('Max')->numeric()->prefix('UGX'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->lifetimeSpendBetween(
                            isset($data['min']) ? (float) $data['min'] : null,
                            isset($data['max']) ? (float) $data['max'] : null,
                        );
                    }),

                Tables\Filters\Filter::make('lifetime_orders')
                    ->label('Lifetime Orders')
                    ->form([
                        Forms\Components\TextInput::make('min')->label('Min')->numeric()->integer(),
                        Forms\Components\TextInput::make('max')->label('Max')->numeric()->integer(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->lifetimeOrdersBetween(
                            isset($data['min']) ? (int) $data['min'] : null,
                            isset($data['max']) ? (int) $data['max'] : null,
                        );
                    }),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'active']);
                            Notification::make()->title('Customers activated')->success()->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status' => 'inactive']);
                            Notification::make()->title('Customers deactivated')->success()->send();
                        }),

                    Tables\Actions\BulkAction::make('sendEmail')
                        ->label('Send Email')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Send Email')
                        ->modalDescription('Email campaign integration will be available in a future release. This action is a placeholder.')
                        ->action(function (): void {
                            Notification::make()->title('Email integration is planned')->info()->send();
                        }),

                    Tables\Actions\BulkAction::make('assignTags')
                        ->label('Assign Tags')
                        ->icon('heroicon-o-tag')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Assign Tags')
                        ->modalDescription('Customer tagging will be available in a future release. This action is a placeholder.')
                        ->action(function (): void {
                            Notification::make()->title('Tagging integration is planned')->info()->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (User $record): string => static::getUrl('view', ['record' => $record]))
            ->striped()
            ->emptyStateHeading('No customers found')
            ->emptyStateDescription('Try adjusting your filters or add a new customer to get started.')
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->withMax('orders', 'created_at')
            ->where(function (Builder $query): void {
                $query->where('is_admin', false)
                    ->orWhereNull('is_admin');
            });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
