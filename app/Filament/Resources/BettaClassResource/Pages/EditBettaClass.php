<?php

namespace App\Filament\Resources\BettaClassResource\Pages;

use App\Filament\Resources\BettaClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBettaClass extends EditRecord
{
    protected static string $resource = BettaClassResource::class;

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
