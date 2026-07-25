<?php

namespace App\Filament\Resources;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementPriority;
use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 82;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Announcement')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('body')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('target_audience')
                            ->label('Audience')
                            ->required()
                            ->options(AnnouncementAudience::class)
                            ->default(AnnouncementAudience::EVERYONE->value),

                        Forms\Components\Select::make('priority')
                            ->options(AnnouncementPriority::class)
                            ->default(AnnouncementPriority::NORMAL->value),

                        Forms\Components\Toggle::make('pinned')
                            ->label('Pin to top'),

                        Forms\Components\DateTimePicker::make('start_at')
                            ->label('Start At')
                            ->default(now()),

                        Forms\Components\DateTimePicker::make('end_at')
                            ->label('End At'),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Schedule For')
                            ->helperText('Leave blank to publish immediately when saved as sent.'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Publish Now')
                            ->dehydrated(false)
                            ->helperText('Toggle on to broadcast immediately on create/update.'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('target_audience')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (AnnouncementPriority $state): string => $state->color()),

                Tables\Columns\IconColumn::make('pinned')
                    ->boolean(),

                Tables\Columns\IconColumn::make('sent_at')
                    ->label('Published')
                    ->boolean()
                    ->getStateUsing(fn (Announcement $record): bool => $record->sent_at !== null),

                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('target_audience')
                    ->options(AnnouncementAudience::class)
                    ->multiple(),

                Tables\Filters\SelectFilter::make('priority')
                    ->options(AnnouncementPriority::class)
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('broadcast')
                    ->label('Broadcast')
                    ->icon('heroicon-m-paper-airplane')
                    ->requiresConfirmation()
                    ->action(function (Announcement $record) {
                        app(AnnouncementService::class)->broadcast($record);

                        Notification::make()
                            ->title('Announcement broadcasted')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Announcement $record): bool => $record->sent_at === null),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
