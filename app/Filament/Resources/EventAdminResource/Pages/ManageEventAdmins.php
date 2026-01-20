<?php

namespace App\Filament\Resources\EventAdminResource\Pages;

use App\Filament\Resources\EventAdminResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEventAdmins extends ManageRecords
{
    protected static string $resource = EventAdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['role'] = 'event_admin';
                    return $data;
                }),
        ];
    }
}
