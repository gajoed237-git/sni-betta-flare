<?php

namespace App\Filament\Resources\EventResource\Widgets;

use App\Filament\Resources\EventResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class EventListWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return EventResource::table($table)
            ->query(EventResource::getEloquentQuery())
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn($record) => EventResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([]); // Disable bulk actions for cleaner view
    }
}
