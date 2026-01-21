<?php

namespace App\Filament\Resources\FishSfJuResource\Pages;

use App\Filament\Resources\FishSfJuResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFishSfJus extends ManageRecords
{
    protected static string $resource = FishSfJuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
