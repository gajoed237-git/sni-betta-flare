<?php

namespace App\Filament\Resources\HandlerResource\Pages;

use App\Filament\Resources\HandlerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHandler extends EditRecord
{
    protected static string $resource = HandlerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
