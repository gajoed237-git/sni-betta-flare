<?php

namespace App\Filament\Resources\FishScoreResource\Pages;

use App\Filament\Resources\FishScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFishScore extends EditRecord
{
    protected static string $resource = FishScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
