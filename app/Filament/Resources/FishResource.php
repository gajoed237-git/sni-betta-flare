<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FishResource\Pages;
use App\Models\Fish;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\FishScoreResource;

class FishResource extends Resource
{
    protected static ?string $model = Fish::class;
    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $query->with(['event', 'bettaClass', 'snapshot']);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.competition');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.fishes');
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;

        if ($user->isEventAdmin()) {
            return $user->managed_events->where('is_locked', false)->isNotEmpty();
        }

        return false;
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->isAdmin()) return true;

        if ($user->isEventAdmin()) {
            return $user->managed_events->where('is_locked', false)->isNotEmpty();
        }

        return false;
    }

    public static function form(Form $form): Form
    {
        $user = auth()->user();

        return $form
            ->schema([
                Forms\Components\Select::make('event_id')
                    ->label(__('messages.resources.events'))
                    ->relationship('event', 'name', function ($query) use ($user) {
                        if ($user && $user->isEventAdmin()) {
                            $eventIds = $user->managed_events()->pluck('events.id');
                            $query->whereIn('id', $eventIds);
                        }
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive(),
                Forms\Components\Select::make('class_id')
                    ->label(__('messages.resources.classes'))
                    ->relationship('bettaClass', 'name', function ($query, $get) {
                        $eventId = $get('event_id');
                        if ($eventId) {
                            $query->where('event_id', $eventId);
                        }
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('registration_no')
                    ->label(__('messages.fields.registration_no'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('participant_name')
                    ->label(__('messages.fields.participant'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('team_name')
                    ->label(__('messages.fields.team'))
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->label(__('messages.fields.status'))
                    ->options([
                        'registered' => __('messages.statuses.registered'),
                        'checking' => __('messages.statuses.checking'),
                        'judging' => __('messages.statuses.judging'),
                        'completed' => __('messages.statuses.completed'),
                        'disqualified' => __('messages.statuses.disqualified'),
                        'moved' => __('messages.statuses.moved'),
                    ])
                    ->required()
                    ->default('registered'),
                Forms\Components\Toggle::make('is_nominated')
                    ->label(__('Nominasi'))
                    ->default(false),
                Forms\Components\Select::make('original_class_id')
                    ->relationship('originalClass', 'name')
                    ->label(__('messages.fields.original_class'))
                    ->placeholder('-')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Textarea::make('admin_note')
                    ->label(__('messages.fields.admin_note_dq'))
                    ->columnSpanFull(),

                Forms\Components\Section::make(__('messages.fields.official_results'))
                    ->description(__('messages.fields.official_results_desc'))
                    ->schema([
                        Forms\Components\Select::make('final_rank')
                            ->label(__('messages.fields.official_rank'))
                            ->options([
                                1 => __('messages.fields.rank_1'),
                                2 => __('messages.fields.rank_2'),
                                3 => __('messages.fields.rank_3'),
                            ])
                            ->placeholder('-')
                            ->selectablePlaceholder(),
                        Forms\Components\Select::make('winner_type')
                            ->label(__('messages.fields.winner_type'))
                            ->options([
                                'class' => __('messages.fields.winner_class'),
                                'gc' => __('messages.fields.winner_gc'),
                            ])
                            ->placeholder(__('messages.fields.regular_participation')),
                        Forms\Components\Placeholder::make('score_reference')
                            ->label(__('messages.fields.score_reference'))
                            ->content(fn(?Fish $record): string => $record?->snapshot?->average_score ? number_format($record->snapshot->average_score, 2) . ' pts' : 'No scores yet')
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_no')
                    ->label(__('messages.fields.registration_no'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bettaClass.code')
                    ->label(__('messages.resources.classes'))
                    ->description(fn(Fish $record): ?string => $record->status === 'moved' ? "Prev: " . ($record->originalClass?->code ?? '-') : null)
                    ->sortable(),
                Tables\Columns\TextColumn::make('participant_name')
                    ->label(__('messages.fields.participant'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('team_name')
                    ->label(__('messages.fields.team'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('messages.fields.status'))
                    ->badge()
                    ->formatStateUsing(function (string $state, Fish $record): string {
                        $label = match ($state) {
                            'registered' => __('messages.statuses.registered'),
                            'checking' => __('messages.statuses.checking'),
                            'judging' => __('messages.statuses.judging'),
                            'completed' => __('messages.statuses.completed'),
                            'disqualified' => __('messages.statuses.disqualified'),
                            'moved' => __('messages.statuses.moved'),
                            default => $state,
                        };
                        return $record->is_nominated ? "{$label} (Nom)" : $label;
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'registered' => 'gray',
                        'checking' => 'warning',
                        'completed' => 'success',
                        'disqualified' => 'danger',
                        'judging' => 'info',
                        'moved' => 'primary',
                        default => 'gray',
                    })
                    ->icon(fn(?Fish $record): ?string => $record?->is_nominated ? 'heroicon-m-star' : null),
                Tables\Columns\ToggleColumn::make('is_nominated')
                    ->label(__('messages.fields.nominasi')),
                Tables\Columns\TextColumn::make('snapshot.average_score')
                    ->label(__('messages.fields.avg_score'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                Tables\Columns\TextColumn::make('snapshot.rank_in_class')
                    ->label(__('messages.fields.score_rank_est'))
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('final_rank')
                    ->label(__('messages.fields.official_rank'))
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('winner_type')
                    ->label(__('messages.fields.winner_type'))
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'gc' => 'warning',
                        'class' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => $state ? strtoupper($state) : '-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name', function ($query) {
                        $user = auth()->user();
                        if ($user && $user->isEventAdmin()) {
                            $eventIds = $user->managed_events()->pluck('events.id');
                            $query->whereIn('id', $eventIds);
                        }
                    }),
                Tables\Filters\SelectFilter::make('bettaClass')
                    ->label(__('messages.resources.classes'))
                    ->relationship('bettaClass', 'name'),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'registered' => __('messages.statuses.registered'),
                        'checking' => __('messages.statuses.checking'),
                        'judging' => __('messages.statuses.judging'),
                        'completed' => __('messages.statuses.completed'),
                        'disqualified' => __('messages.statuses.disqualified'),
                        'moved' => __('messages.statuses.moved'),
                    ]),
                Tables\Filters\TernaryFilter::make('is_nominated')
                    ->label(__('messages.fields.nominasi')),
                Tables\Filters\SelectFilter::make('final_rank')
                    ->label(__('messages.fields.rank'))
                    ->options([
                        1 => __('messages.fields.rank_1'),
                        2 => __('messages.fields.rank_2'),
                        3 => __('messages.fields.rank_3'),
                    ]),
                Tables\Filters\SelectFilter::make('winner_type')
                    ->label(__('messages.fields.winner_type'))
                    ->options([
                        'class' => __('messages.fields.winner_class'),
                        'gc' => __('messages.fields.winner_gc'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('print_label')
                    ->label(__('messages.actions.print_label'))
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn(Fish $record): string => route('print.labels', ['ids' => [$record->id]]))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('give_score')
                    ->label(__('messages.actions.give_score'))
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->url(fn(Fish $record): string => FishScoreResource::getUrl('create', ['fish_id' => $record->id]))
                    ->hidden(fn(Fish $record): bool => $record->event->is_locked && !auth()->user()->isAdmin()),
                Tables\Actions\EditAction::make()
                    ->hidden(fn(Fish $record): bool => $record->event->is_locked && !auth()->user()->isAdmin()),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn(Fish $record): bool => $record->event->is_locked && !auth()->user()->isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('print_selected_labels')
                        ->label(__('messages.actions.print_label'))
                        ->icon('heroicon-o-printer')
                        ->action(function (Tables\Actions\BulkAction $action, $records) {
                            $ids = $records->pluck('id')->toArray();
                            return redirect()->route('print.labels', ['ids' => $ids]);
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFish::route('/'),
            'create' => Pages\CreateFish::route('/create'),
            'edit' => Pages\EditFish::route('/{record}/edit'),
        ];
    }
}
