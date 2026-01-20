<?php

namespace App\Filament\Resources;

use App\Models\AuditTrail;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class AuditTrailResource extends Resource
{
    protected static ?string $model = AuditTrail::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?int $navigationSort = 101;

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.resources.audit_logs');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user && $user->isAdmin()) {
            return $query;
        }

        if (!$user) return $query->whereRaw('1=0');

        return $query->whereIn('event_id', $user->managed_events->pluck('events.id'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event.name')
                    ->label('Event')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),
                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'submitted_score' => 'success',
                        'updated_score' => 'warning',
                        'deleted_score' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('model_type')
                    ->label('Module'),
                TextColumn::make('details')
                    ->label('Details')
                    ->limit(50)
                    ->tooltip(fn($record) => $record->details),
                TextColumn::make('ip_address')
                    ->label('IP'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'submitted_score' => 'Submit Score',
                        'updated_score' => 'Update Score',
                        'deleted_score' => 'Delete Score',
                    ]),
            ]);
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => AuditTrailResource\Pages\ListAuditTrails::route('/'),
        ];
    }
}
