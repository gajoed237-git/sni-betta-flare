<?php

namespace App\Filament\Resources\BettaClassResource\Widgets;

use App\Filament\Resources\BettaClassResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BettaClassListWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return BettaClassResource::table($table)
            ->query(BettaClassResource::getEloquentQuery())
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn($record) => BettaClassResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([]);
    }
}
