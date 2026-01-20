<?php

namespace App\Filament\Resources\DivisionResource\Widgets;

use App\Filament\Resources\DivisionResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DivisionListWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return DivisionResource::table($table)
            ->query(DivisionResource::getEloquentQuery())
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(fn($record) => DivisionResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([]);
    }
}
