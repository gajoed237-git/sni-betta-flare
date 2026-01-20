<?php

namespace App\Filament\Resources\FishScoreResource\Pages;

use App\Filament\Resources\FishScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFishScore extends CreateRecord
{
    protected static string $resource = FishScoreResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
