<?php

namespace App\Filament\Resources\FishResource\Widgets;

use App\Filament\Resources\FishResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class FishListWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return FishResource::table($table)
            ->query(FishResource::getEloquentQuery())
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn($record) => FishResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([]);
    }
}
