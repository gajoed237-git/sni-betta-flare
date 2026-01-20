<?php

namespace App\Filament\Widgets;

use App\Models\BettaClass;
use App\Models\Fish;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;

class ClassJudgingProgress extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Class Judging Progress';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $query = BettaClass::query();

        // Scope by managed events if user is Event Admin (and not Super Admin)
        if ($user && $user->isEventAdmin() && !$user->isAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $table
            ->query(
                $query
                    ->withCount('fishes')
                    ->with(['fishes' => function ($q) {
                        $q->whereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('fish_scores')
                                ->whereColumn('fish_scores.fish_id', 'fishes.id');
                        });
                    }])
            )
            ->columns([
                TextColumn::make('code')
                    ->label('Class')
                    ->formatStateUsing(fn($record) => "{$record->code} - {$record->name}")
                    ->searchable(['code', 'name'])
                    ->sortable(),
                TextColumn::make('fishes_count')
                    ->label('Total'),
                TextColumn::make('judged_count')
                    ->label('Judged')
                    ->getStateUsing(function ($record) {
                        return $record->fishes->count();
                    }),
                TextColumn::make('nominated_count')
                    ->label('Nom')
                    ->getStateUsing(fn($record) => $record->fishes->where('is_nominated', true)->count())
                    ->badge()
                    ->color('amber'),
                TextColumn::make('winners_count')
                    ->label('Winners')
                    ->getStateUsing(fn($record) => $record->fishes->whereNotNull('final_rank')->count())
                    ->badge()
                    ->color('success'),
                TextColumn::make('progress')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        $total = $record->fishes_count;
                        if ($total == 0) return '0%';
                        $judged = $record->fishes->count();
                        return round(($judged / $total) * 100) . '%';
                    })
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === '100%' => 'success',
                        $state === '0%' => 'gray',
                        default => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->preload()
                    ->searchable()
            ])
            ->paginated(false);
    }
}
