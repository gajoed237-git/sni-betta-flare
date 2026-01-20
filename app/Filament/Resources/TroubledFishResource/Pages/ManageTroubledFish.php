<?php

namespace App\Filament\Resources\TroubledFishResource\Pages;

use App\Filament\Resources\TroubledFishResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTroubledFish extends ManageRecords
{
    protected static string $resource = TroubledFishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
