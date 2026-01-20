<?php

namespace App\Filament\Widgets;

use App\Models\AuditTrail;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;

class LatestActivities extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        $query = AuditTrail::query();

        // Scope by managed events if user is Event Admin (and not Super Admin)
        if ($user && $user->isEventAdmin() && !$user->isAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        return $table
            ->query($query->latest()->limit(5))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->since(),
                TextColumn::make('user.name')
                    ->label('Judge/Admin'),
                TextColumn::make('action')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'submitted_score' => 'success',
                        'updated_score' => 'warning',
                        'deleted_score' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('details')
                    ->limit(50),
            ])
            ->paginated(false);
    }
}
