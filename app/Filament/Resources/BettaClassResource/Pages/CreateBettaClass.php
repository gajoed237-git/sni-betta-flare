<?php

namespace App\Filament\Resources\BettaClassResource\Pages;

use App\Filament\Resources\BettaClassResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBettaClass extends CreateRecord
{
    protected static string $resource = BettaClassResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
