<?php

namespace App\Filament\Resources\ParticipantSfJuResource\Pages;

use App\Filament\Resources\ParticipantSfJuResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageParticipantSfJus extends ManageRecords
{
    protected static string $resource = ParticipantSfJuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
