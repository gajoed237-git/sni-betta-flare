<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScoreSnapshotResource\Pages;
use App\Models\ScoreSnapshot;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;

class ScoreSnapshotResource extends Resource
{
    protected static ?string $model = ScoreSnapshot::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereHas('fish', function ($q) use ($eventIds) {
                $q->whereIn('event_id', $eventIds);
            });
        }

        return $query->with(['fish.bettaClass', 'fish.event']);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.competition');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.leaderboard');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rank_in_class')
                    ->label(__('Score Rank (Est)'))
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        1 => 'success',
                        2 => 'warning',
                        3 => 'info',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fish.registration_no')
                    ->label(__('messages.resources.fishes'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fish.bettaClass.name')
                    ->label(__('messages.resources.classes'))
                    ->sortable(),
                TextColumn::make('average_score')
                    ->label(__('messages.fields.total_score')) // Or Avg
                    ->sortable()
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('fish.final_rank')
                    ->label('Juara (Official)')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->icon(fn($record) => $record->fish->winner_type === 'gc' ? 'heroicon-m-sparkles' : null),
                TextColumn::make('fish.winner_type')
                    ->label('Winner Type')
                    ->badge()
                    ->formatStateUsing(fn($state) => strtoupper($state))
                    ->color(fn($state) => match ($state) {
                        'gc' => 'warning',
                        'class' => 'success',
                        default => 'gray'
                    }),
                TextColumn::make('total_judges')
                    ->label(__('messages.fields.judge'))
                    ->sortable(),
            ])
            ->defaultSort('average_score', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label(__('messages.resources.events'))
                    ->relationship('fish.event', 'name', function ($query) {
                        $user = auth()->user();
                        if ($user && $user->isEventAdmin()) {
                            $eventIds = $user->managed_events()->pluck('events.id');
                            $query->whereIn('id', $eventIds);
                        }
                    }),
                Tables\Filters\SelectFilter::make('class')
                    ->label(__('messages.resources.classes'))
                    ->relationship('fish.bettaClass', 'name', function ($query, $get) {
                        if ($get('event')) {
                            $query->where('event_id', $get('event'));
                        }
                    }),
            ])
            ->actions([
                // View only or add GC/BOB toggles
                Tables\Actions\Action::make('set_gc')
                    ->label('Set GC')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->hidden(fn($record) => $record->rank_in_class !== 1)
                    ->action(fn($record) => $record->update(['is_gc' => !$record->is_gc])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScoreSnapshots::route('/'),
        ];
    }
}
