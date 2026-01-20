<?php

namespace App\Filament\Resources\FishScoreResource\Pages;

use App\Filament\Resources\FishScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFishScores extends ListRecords
{
    protected static string $resource = FishScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
