<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Filament\Resources\ParticipantResource\RelationManagers;
use App\Models\Participant;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Competition';

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.competition');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.participant_data');
    }

    public static function getModelLabel(): string
    {
        return __('messages.resources.participant');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // Event admins can only see participants from their managed events
        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $query->with(['event', 'handler', 'fishes.bettaClass']);
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();

        // Superadmin can always access
        if ($user && $user->isAdmin()) {
            return true;
        }

        // Event admin can access if they manage at least one event
        if ($user && $user->isEventAdmin()) {
            return $user->managed_events()->exists();
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label(__('messages.resources.events'))
                    ->relationship('event', 'name', function ($query) use ($user) {
                        // Event admins can only select their managed events
                        if ($user && $user->isEventAdmin()) {
                            $eventIds = $user->managed_events()->pluck('events.id');
                            $query->whereIn('id', $eventIds);
                        }
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->label(__('messages.fields.participant'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->label(__('messages.fields.phone'))
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Select::make('category')
                    ->label(__('messages.fields.category'))
                    ->options([
                        'team' => 'Team',
                        'single_fighter' => 'Single Fighter',
                    ])
                    ->required()
                    ->default('single_fighter')
                    ->reactive(),
                Forms\Components\TextInput::make('team_name')
                    ->label(__('messages.fields.team'))
                    ->maxLength(255)
                    ->visible(fn($get) => $get('category') === 'team'),
                Forms\Components\Select::make('handler_id')
                    ->label(__('messages.resources.handlers'))
                    ->relationship('handler', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label(__('messages.fields.name'))
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label(__('messages.fields.phone'))
                            ->tel(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email(),
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->label(__('messages.fields.description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.name')
                    ->label(__('messages.resources.events'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('messages.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label(__('messages.fields.phone'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->label(__('messages.fields.category'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'team' => 'success',
                        'single_fighter' => 'info',
                    }),
                Tables\Columns\TextColumn::make('team_name')
                    ->label(__('messages.fields.team'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('handler.name')
                    ->label(__('messages.resources.handlers'))
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fishes_count')
                    ->label(__('messages.fields.fish_count'))
                    ->counts('fishes')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('fish_classes')
                    ->label(__('messages.fields.fish_classes'))
                    ->badge()
                    ->separator(',')
                    ->getStateUsing(function ($record) {
                        return $record->fishes
                            ->pluck('bettaClass.code')
                            ->unique()
                            ->filter()
                            ->values()
                            ->toArray();
                    })
                    ->color('success')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('messages.resources.events'))
                    ->relationship('event', 'name'),
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('messages.fields.category'))
                    ->options([
                        'team' => 'Team',
                        'single_fighter' => 'Single Fighter',
                    ]),
                Tables\Filters\SelectFilter::make('handler')
                    ->label(__('messages.resources.handlers'))
                    ->relationship('handler', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FishesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit' => Pages\EditParticipant::route('/{record}/edit'),
        ];
    }
}
